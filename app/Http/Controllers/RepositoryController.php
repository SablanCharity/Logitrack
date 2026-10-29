<?php

namespace App\Http\Controllers;

use App\Models\ArchivedCapstone;
use App\Models\CapstoneProject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RepositoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $year = $request->input('year');
        $isDashboard = $request->routeIs('dashboard');

        // 1. Get modern approved capstones
        $modernCapstones = CapstoneProject::where('status', 'admin_approved')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('abstract', 'like', "%{$search}%")
                        ->orWhere('program', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function ($item) {
                $item->source_type = 'modern';
                $item->study_year = $item->publication_date?->format('Y');

                return $item;
            });

        // 2. Get approved old/OCR capstones
        $archivedCapstones = ArchivedCapstone::where('status', 'approved')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('abstract', 'like', "%{$search}%")
                        ->orWhere('program', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function ($item) {
                $item->source_type = 'old';
                $item->year_section = $item->publication_date;
                $item->study_year = $this->publicationYear($item->publication_date);

                // Safe dummy relationship fallbacks including group_name
                $item->groupRoom = (object) [
                    'id' => null,
                    'group_name' => 'Legacy Record Group',
                    'group_leader' => null,
                    'classroom' => (object) [
                        'name' => 'Legacy Repository',
                        'instructor' => (object) ['first_name' => $item->adviser, 'last_name' => ''],
                    ],
                    'members' => collect($item->authors ?? [])->map(fn ($author) => (object) [
                        'is_leader' => false,
                        'user' => (object) ['first_name' => $author, 'last_name' => ''],
                    ]),
                ];
                $item->instructor = (object) ['first_name' => $item->adviser, 'last_name' => ''];

                return $item;
            });

        // 3. Merge collections and sort by newest first
        $allCapstones = $modernCapstones->concat($archivedCapstones);
        $mergedCollection = $allCapstones
            ->when($year, fn ($items) => $items->where('study_year', (int) $year))
            ->sortByDesc('created_at')
            ->values();

        $years = $allCapstones
            ->pluck('study_year')
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $dashboardStudies = [
            'best' => $modernCapstones->firstWhere('is_best', true),
            'most_viewed' => $modernCapstones->sortByDesc('views_count')->first(),
        ];
        $totalCapstoneStudies = $allCapstones->count();
        $showDashboardOverview = $isDashboard && ! $search && ! $year;

        // 4. Manually paginate the merged collection
        $perPage = 5;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $mergedCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();

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
            'isDashboard' => $isDashboard,
            'showDashboardOverview' => $showDashboardOverview,
            'dashboardStudies' => $dashboardStudies,
            'totalCapstoneStudies' => $totalCapstoneStudies,
            'classroomId' => null,
        ]);
    }

    public function show(Request $request, int $capstoneId): View
    {
        $returnParameters = $request->validate([
            'type' => ['nullable', 'in:old,modern'],
            'return_to' => ['nullable', 'in:dashboard,repository,instructor_repository'],
            'search' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'digits:4', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $returnRoute = match ($returnParameters['return_to'] ?? null) {
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
            ?? (ArchivedCapstone::whereKey($capstoneId)->exists() ? 'old' : 'modern');

        $user = $request->user();
        $classroomId = $user->role === 'student'
            ? $user->classroomMemberships()->where('status', 'approved')->value('classroom_id')
            : null;

        if ($type === 'old') {
            $capstone = ArchivedCapstone::query()
                ->where('status', 'approved')
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

    public function edit(Request $request, int $capstoneId): View
    {
        $type = $request->validate([
            'type' => ['required', 'in:old,modern'],
        ])['type'];

        $capstone = $type === 'old'
            ? ArchivedCapstone::query()->where('status', 'approved')->findOrFail($capstoneId)
            : CapstoneProject::query()->where('status', 'admin_approved')->findOrFail($capstoneId);

        return view('repository.edit', compact('capstone', 'type'));
    }

    public function update(Request $request, int $capstoneId): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:old,modern'],
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['nullable', 'string'],
            'program' => ['nullable', 'string', 'max:255'],
            'publication_date' => ['nullable', 'string', 'max:255'],
            'adviser' => ['nullable', 'string', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
            'authors' => ['nullable', 'string'],
        ]);

        if ($validated['type'] === 'old') {
            $capstone = ArchivedCapstone::query()
                ->where('status', 'approved')
                ->findOrFail($capstoneId);
            $capstone->update([
                'title' => $validated['title'],
                'abstract' => $validated['abstract'] ?? null,
                'program' => $validated['program'] ?? null,
                'publication_date' => $validated['publication_date'] ?? null,
                'adviser' => $validated['adviser'] ?? null,
                'institution' => $validated['institution'] ?? null,
                'authors' => isset($validated['authors'])
                    ? array_map('trim', explode(',', $validated['authors']))
                    : null,
            ]);
        } else {
            $capstone = CapstoneProject::query()
                ->where('status', 'admin_approved')
                ->findOrFail($capstoneId);
            $capstone->update([
                'title' => $validated['title'],
                'abstract' => $validated['abstract'] ?? null,
                'program' => $validated['program'] ?? null,
                'publication_date' => $validated['publication_date'] ?? null,
            ]);
        }

        return redirect()
            ->route('repository.show', ['capstoneId' => $capstone->id, 'type' => $validated['type']])
            ->with('success', 'Capstone metadata updated successfully.');
    }

    private function prepareLegacyCapstone(ArchivedCapstone $capstone): ArchivedCapstone
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
