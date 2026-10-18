<?php

namespace App\Http\Controllers;

use App\Models\CapstoneProject;
use App\Models\OldCapstone;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class RepositoryController extends Controller
{
    public function dashboard(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->role === 'instructor') {
            return redirect()->route('instructor.repository.index');
        }

        if ($user->role === 'student') {
            if (! $user->is_approved) {
                return redirect()->route('student.pending');
            }

            return $this->index($request, true);
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.capstones');
        }

        abort(403, 'Unauthorized access.');
    }

    /**
     * Display approved modern and legacy capstones.
     */
    public function index(Request $request, bool $isDashboard = false): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'regex:/^\d{4}$/'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = $validated['search'] ?? null;
        $year = $validated['year'] ?? null;

        $modernCapstones = CapstoneProject::query()
            ->where('status', 'admin_approved')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('abstract', 'like', "%{$search}%")
                        ->orWhere('program', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function ($item) {
                $item->source_type = 'modern';

                return $item;
            });

        $oldCapstones = OldCapstone::query()
            ->where('status', 'admin_approved')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('abstract', 'like', "%{$search}%")
                        ->orWhere('program', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function ($item) {
                $item->source_type = 'old';
                $item->year_section = $item->publication_date;

                $item->groupRoom = (object) [
                    'id' => null,
                    'group_name' => 'Legacy Record Group',
                    'group_leader' => null,
                    'classroom' => (object) [
                        'name' => 'Legacy Repository',
                        'instructor' => (object) [
                            'first_name' => $item->adviser,
                            'last_name' => '',
                        ],
                    ],
                    'members' => collect($item->authors ?? [])
                        ->map(fn ($author) => (object) [
                            'is_leader' => false,
                            'user' => (object) [
                                'first_name' => $author,
                                'last_name' => '',
                            ],
                        ]),
                ];

                $item->instructor = (object) [
                    'first_name' => $item->adviser,
                    'last_name' => '',
                ];

                return $item;
            });

        $mergedCollection = $modernCapstones
            ->concat($oldCapstones)
            ->sortByDesc(fn ($capstone) => $capstone->created_at)
            ->values();

        $getStudyYear = function ($capstone) {
            $date = $capstone->publication_date ?? $capstone->created_at;

            if (empty($date)) {
                return null;
            }

            try {
                return Carbon::parse($date)->format('Y');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $years = $mergedCollection
            ->map(fn ($capstone) => $getStudyYear($capstone))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        if ($year !== null && $year !== '') {
            $mergedCollection = $mergedCollection
                ->filter(fn ($capstone) => $getStudyYear($capstone) === (string) $year)
                ->values();
        }

        $totalCapstoneStudies = $mergedCollection->count();
        $dashboardStudies = [
            'best' => $mergedCollection->first(fn ($study) => (bool) $study->is_best),
            'most_viewed' => $mergedCollection->sortByDesc('views_count')->first(),
        ];

        $perPage = $isDashboard ? 5 : 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $mergedCollection
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $capstones = new LengthAwarePaginator(
            $currentPageItems,
            $mergedCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        return view('repository.index', [
            'capstones' => $capstones,
            'search' => $search,
            'year' => $year,
            'years' => $years,
            'classroomId' => null,
            'isDashboard' => $isDashboard,
            'showDashboardOverview' => $isDashboard
                && empty($search)
                && empty($year)
                && $currentPage === 1,
            'dashboardStudies' => $dashboardStudies,
            'totalCapstoneStudies' => $totalCapstoneStudies,
        ]);
    }

    /**
     * Display an approved repository capstone.
     */
    public function show(Request $request, int $capstoneId): View
    {
        $returnParameters = $request->validate([
            'type' => ['nullable', 'in:old,modern'],
            'return_to' => ['nullable', 'in:dashboard,repository,instructor_repository'],
            'search' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'digits:4', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $returnRoute = match ($returnParameters['return_to'] ?? 'repository') {
            'dashboard' => 'dashboard',
            'instructor_repository' => 'instructor.repository.index',
            default => 'repository.index',
        };

        $backUrl = route($returnRoute, array_filter([
            'search' => $returnParameters['search'] ?? null,
            'year' => $returnParameters['year'] ?? null,
            'page' => $returnParameters['page'] ?? null,
        ], fn ($value) => $value !== null && $value !== ''));

        $type = $returnParameters['type']
            ?? (OldCapstone::whereKey($capstoneId)->exists() ? 'old' : 'modern');

        if (! in_array($type, ['old', 'modern'], true)) {
            abort(400, 'Please specify the capstone source type.');
        }

        $user = $request->user();
        $classroomId = $user && $user->role === 'student'
            ? $user->classroomMemberships()->where('status', 'approved')->value('classroom_id')
            : null;

        if ($type === 'old') {
            $capstone = OldCapstone::query()
                ->where('status', 'admin_approved')
                ->findOrFail($capstoneId);
            $capstone = $this->prepareLegacyCapstone($capstone);
        } else {
            $capstone = CapstoneProject::with([
                'groupRoom.classroom.instructor',
                'groupRoom.members.user',
            ])
                ->where('status', 'admin_approved')
                ->findOrFail($capstoneId);
            $capstone->source_type = 'modern';
            $capstone->increment('views_count');
        }

        return view('repository.show', compact('capstone', 'classroomId', 'backUrl'));
    }

    private function prepareLegacyCapstone(OldCapstone $capstone): OldCapstone
    {
        $capstone->source_type = 'old';
        $capstone->year_section = $capstone->publication_date;
        $capstone->groupRoom = (object) [
            'id' => null,
            'group_name' => 'Legacy Record Group',
            'group_leader' => null,
            'classroom' => (object) [
                'name' => 'Legacy Repository',
                'instructor' => (object) [
                    'first_name' => $capstone->adviser,
                    'last_name' => '',
                ],
            ],
            'members' => collect($capstone->authors ?? [])
                ->map(fn ($author) => (object) [
                    'is_leader' => false,
                    'user' => (object) [
                        'first_name' => $author,
                        'last_name' => '',
                    ],
                ]),
        ];

        $capstone->instructor = (object) [
            'first_name' => $capstone->adviser,
            'last_name' => '',
        ];

        return $capstone;
    }
}
