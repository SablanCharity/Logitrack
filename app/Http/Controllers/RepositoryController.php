<?php

namespace App\Http\Controllers;

use App\Models\CapstoneProject;
use App\Models\OldCapstone;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RepositoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $year = $request->validate([
            'year' => ['nullable', 'digits:4', 'integer', 'min:1900', 'max:' . (now()->year + 1)],
        ])['year'] ?? null;
        $classroomId = $request->user()->role === 'student'
            ? $request->user()->classroomMemberships()->where('status', 'approved')->value('classroom_id')
            : null;
        $isDashboard = $request->routeIs('dashboard');

        $years = CapstoneProject::query()
            ->where('status', 'admin_approved')
            ->whereNotNull('publication_date')
            ->pluck('publication_date')
            ->concat(OldCapstone::query()
                ->where('status', 'admin_approved')
                ->pluck('publication_date'))
            ->map(fn ($publicationDate) => $this->publicationYear($publicationDate))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $dashboardStudies = null;
        $totalCapstoneStudies = null;
        $showDashboardOverview = $isDashboard && ! filled($search) && $year === null;

        if ($showDashboardOverview) {
            $approvedStudies = CapstoneProject::query()
                ->where('status', 'admin_approved');
            $studyRelations = [
                'groupRoom.classroom.instructor',
                'groupRoom.members.user',
            ];

            $totalCapstoneStudies = (clone $approvedStudies)->count()
                + OldCapstone::query()->where('status', 'admin_approved')->count();
            $dashboardStudies = [
                'best' => (clone $approvedStudies)
                    ->with($studyRelations)
                    ->where('is_best', true)
                    ->first(),
                'most_viewed' => (clone $approvedStudies)
                    ->with($studyRelations)
                    ->orderByDesc('views_count')
                    ->latest('approved_at')
                    ->first(),
            ];
        }

        if ($isDashboard && $showDashboardOverview) {
            $capstones = collect();
        } else {
            $modernCapstones = CapstoneProject::with([
                'groupRoom.classroom.instructor',
                'groupRoom.members.user',
            ])
                ->where('status', 'admin_approved')
                ->when($search, function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%')
                            ->orWhere('abstract', 'like', '%' . $search . '%')
                            ->orWhere('program', 'like', '%' . $search . '%')
                            ->orWhere('year_section', 'like', '%' . $search . '%');
                    });
                })
                ->when($year, fn ($query, $year) => $query->whereYear('publication_date', $year))
                ->get()
                ->map(function ($capstone) {
                    $capstone->source_type = 'modern';

                    return $capstone;
                });

            $legacyCapstones = OldCapstone::query()
                ->where('status', 'admin_approved')
                ->when($search, function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%')
                            ->orWhere('abstract', 'like', '%' . $search . '%')
                            ->orWhere('program', 'like', '%' . $search . '%')
                            ->orWhere('adviser', 'like', '%' . $search . '%');
                    });
                })
                ->get()
                ->map(fn (OldCapstone $capstone) => $this->prepareLegacyCapstone($capstone))
                ->when($year, fn ($capstones) => $capstones->filter(
                    fn (OldCapstone $capstone) => $this->publicationYear($capstone->publication_date) === (int) $year
                ));

            $mergedCapstones = $modernCapstones
                ->concat($legacyCapstones)
                ->sortByDesc(fn ($capstone) => $capstone->approved_at ?? $capstone->created_at)
                ->values();
            $perPage = $isDashboard ? 5 : 10;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $capstones = new LengthAwarePaginator(
                $mergedCapstones->slice(($currentPage - 1) * $perPage, $perPage)->values(),
                $mergedCapstones->count(),
                $perPage,
                $currentPage,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );
        }

        return view($isDashboard ? 'dashboard' : 'repository.index', compact(
            'capstones',
            'search',
            'year',
            'years',
            'classroomId',
            'isDashboard',
            'showDashboardOverview',
            'dashboardStudies',
            'totalCapstoneStudies'
        ));
    }

    public function show(Request $request, int $capstoneId): View
    {
        $returnParameters = $request->validate([
            'type' => ['nullable', 'in:old,modern'],
            'return_to' => ['nullable', 'in:dashboard,repository'],
            'search' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'digits:4', 'integer', 'min:1900', 'max:' . (now()->year + 1)],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $returnRoute = ($returnParameters['return_to'] ?? null) === 'dashboard'
            ? 'dashboard'
            : 'repository.index';
        $backUrl = route($returnRoute, array_filter([
            'search' => $returnParameters['search'] ?? null,
            'year' => $returnParameters['year'] ?? null,
            'page' => $returnParameters['page'] ?? null,
        ], fn ($value) => $value !== null && $value !== ''));
        $type = $returnParameters['type']
            ?? (OldCapstone::whereKey($capstoneId)->exists() ? 'old' : 'modern');

        $user = $request->user();
        $classroomId = $user->role === 'student'
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
            'members' => collect($capstone->authors ?? [])->map(fn ($author) => (object) [
                'is_leader' => false,
                'user' => (object) [
                    'first_name' => $author,
                    'last_name' => '',
                ],
            ]),
        ];

        return $capstone;
    }

    private function publicationYear(mixed $publicationDate): ?int
    {
        if (preg_match('/\b((?:19|20)\d{2})\b/', (string) $publicationDate, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }
}
