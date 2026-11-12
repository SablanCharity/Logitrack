<?php

namespace App\Http\Controllers;

use App\Models\ArchivedCapstone;
use App\Models\CapstoneProject;
use App\Models\StudentCapstoneSubmission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RepositoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $year = $request->input('year');
        $program = $request->input('program');
        $adviser = trim((string) $request->input('adviser', ''));
        $sort = $request->input('sort', 'newest');
        $perPage = (int) $request->input('per_page', 5);

        if (! in_array($perPage, [5, 10, 20, 50], true)) {
            $perPage = 5;
        }

        if (! in_array($sort, ['newest', 'oldest', 'title_asc', 'title_desc', 'most_viewed'], true)) {
            $sort = 'newest';
        }

        $isDashboard = $request->routeIs('dashboard');

        /*
        |--------------------------------------------------------------------------
        | LOAD MODERN APPROVED CAPSTONES
        |--------------------------------------------------------------------------
        */

        $modernCapstones = CapstoneProject::with([
            'groupRoom.classroom.instructor',
            'groupRoom.members.user',
        ])
            ->where('status', 'admin_approved')
            ->get()
            ->map(function ($item) {
                $item->source_type = 'modern';

                $item->study_year = $item->publication_date
                    ? $this->publicationYear($item->publication_date)
                    : null;

                return $item;
            });

        /*
        |--------------------------------------------------------------------------
        | LOAD APPROVED ARCHIVED / OCR CAPSTONES
        |--------------------------------------------------------------------------
        */

        $archivedCapstones = ArchivedCapstone::where('status', 'approved')
            ->get()
            ->map(function ($item) {
                return $this->prepareLegacyCapstone($item);
            });

        $studentCapstones = StudentCapstoneSubmission::with([
            'submitter',
            'adviserUser',
            'authors.masterlistRecord',
        ])
            ->where('status', 'approved')
            ->get()
            ->map(function (StudentCapstoneSubmission $item): StudentCapstoneSubmission {
                $item->source_type = 'student';
                $item->program = $item->submitter?->program;
                $item->publication_date = $item->submitted_date;
                $item->study_year = $this->publicationYear($item->submitted_date);

                return $item;
            });

        /*
        |--------------------------------------------------------------------------
        | COMBINE MODERN AND ARCHIVED RECORDS
        |--------------------------------------------------------------------------
        */

        $allCapstones = $modernCapstones
            ->concat($archivedCapstones)
            ->concat($studentCapstones)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD TOTAL COUNTS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | These counts use the complete approved collection, not the
        | filtered or paginated results.
        |
        */

        // Combined total of all approved modern and archived studies.
        $totalCapstoneStudies = $allCapstones->count();

        // Total approved BSIT / Information Technology capstones.
        $totalBsitCapstones = $allCapstones->filter(function ($item) {
            $programName = strtoupper(trim((string) ($item->program ?? '')));

            return str_contains($programName, 'BSIT')
                || str_contains($programName, 'INFORMATION TECHNOLOGY')
                || preg_match('/\bIT\b/', $programName) === 1;
        })->count();

        // Total approved CS / Computer Science theses.
        $totalCsTheses = $allCapstones->filter(function ($item) {
            $programName = strtoupper(trim((string) ($item->program ?? '')));

            return str_contains($programName, 'BSCS')
                || str_contains($programName, 'COMPUTER SCIENCE')
                || preg_match('/\bCS\b/', $programName) === 1;
        })->count();

        /*
        |--------------------------------------------------------------------------
        | AVAILABLE PUBLICATION YEAR FILTERS
        |--------------------------------------------------------------------------
        */

        $years = $allCapstones
            ->pluck('study_year')
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | AVAILABLE PROGRAM FILTERS
        |--------------------------------------------------------------------------
        */

        $programs = $allCapstones
            ->pluck('program')
            ->filter()
            ->map(fn ($value) => trim((string) $value))
            ->unique(fn ($value) => mb_strtolower($value))
            ->sort()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | AVAILABLE ADVISER FILTERS
        |--------------------------------------------------------------------------
        */

        $advisers = $allCapstones
            ->map(fn ($item) => $this->getAdviserName($item))
            ->filter()
            ->unique(fn ($value) => mb_strtolower($value))
            ->sort()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | KEYWORD SEARCH
        |--------------------------------------------------------------------------
        */

        $filteredCapstones = $allCapstones;

        if ($search !== '') {
            $keywords = collect(
                preg_split('/\s+/u', mb_strtolower($search), -1, PREG_SPLIT_NO_EMPTY)
            )->unique()->values();

            $filteredCapstones = $filteredCapstones->filter(function ($item) use ($keywords) {

                $searchableText = mb_strtolower(implode(' ', array_filter([
                    $item->title ?? '',
                    $item->abstract ?? '',
                    $item->program ?? '',
                    $this->getAdviserName($item),
                    $this->getAuthorsText($item),
                ])));

                return $keywords->contains(
                    fn ($word) => mb_stripos($searchableText, $word) !== false
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | PUBLICATION YEAR FILTER
        |--------------------------------------------------------------------------
        */

        if ($year !== null && $year !== '') {
            $filteredCapstones = $filteredCapstones->filter(
                fn ($item) => (string) $item->study_year === (string) $year
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PROGRAM FILTER
        |--------------------------------------------------------------------------
        */

        if ($program !== null && $program !== '') {
            $filteredCapstones = $filteredCapstones->filter(
                fn ($item) => mb_strtolower(trim((string) ($item->program ?? '')))
                    === mb_strtolower(trim($program))
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ADVISER FILTER
        |--------------------------------------------------------------------------
        */

        if ($adviser !== '') {
            $filteredCapstones = $filteredCapstones->filter(
                fn ($item) => mb_strtolower($this->getAdviserName($item))
                    === mb_strtolower($adviser)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SORTING USING PUBLICATION DATE
        |--------------------------------------------------------------------------
        |
        | Newest: Latest publication date first.
        | Oldest: Earliest publication date first.
        | Missing dates are placed at the bottom.
        |
        | Both modern and archived capstones use publication_date.
        |
        */

        $filteredCapstones = match ($sort) {

            // Oldest publication date first.
            'oldest' => $filteredCapstones->sortBy(
                fn ($item) => $this->publicationTimestamp(
                    $item->publication_date ?? null
                ) ?? PHP_INT_MAX
            ),

            // Alphabetical order.
            'title_asc' => $filteredCapstones->sortBy(
                fn ($item) => mb_strtolower($item->title ?? '')
            ),

            // Reverse alphabetical order.
            'title_desc' => $filteredCapstones->sortByDesc(
                fn ($item) => mb_strtolower($item->title ?? '')
            ),

            // Highest views first.
            'most_viewed' => $filteredCapstones->sortByDesc(
                fn ($item) => (int) ($item->views_count ?? 0)
            ),

            // Newest publication date first (default).
            default => $filteredCapstones->sortByDesc(
                fn ($item) => $this->publicationTimestamp(
                    $item->publication_date ?? null
                ) ?? PHP_INT_MIN
            ),
        };

        $filteredCapstones = $filteredCapstones->values();

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD FEATURED STUDIES
        |--------------------------------------------------------------------------
        */

        $dashboardStudies = [
            'best' => $allCapstones->firstWhere('is_best', true),

            'most_viewed' => $allCapstones
                ->sortByDesc('views_count')
                ->first(),
        ];

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD OVERVIEW VISIBILITY
        |--------------------------------------------------------------------------
        */

        $showDashboardOverview = $isDashboard
            && $search === ''
            && empty($year)
            && empty($program)
            && $adviser === ''
            && $sort === 'newest';

        /*
        |--------------------------------------------------------------------------
        | MANUAL PAGINATION AFTER SORTING AND FILTERING
        |--------------------------------------------------------------------------
        */

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $currentPageItems = $filteredCapstones
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $capstones = new LengthAwarePaginator(
            $currentPageItems,
            $filteredCapstones->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | RETURN REPOSITORY VIEW
        |--------------------------------------------------------------------------
        */

        return view('repository.index', [
            'capstones' => $capstones,

            // Search and filters.
            'search' => $search,
            'year' => $year,
            'years' => $years,
            'program' => $program,
            'programs' => $programs,
            'adviser' => $adviser,
            'advisers' => $advisers,
            'sort' => $sort,
            'perPage' => $perPage,

            // Dashboard settings.
            'isDashboard' => $isDashboard,
            'showDashboardOverview' => $showDashboardOverview,
            'dashboardStudies' => $dashboardStudies,

            // Dashboard Number 02 metrics.
            'totalCapstoneStudies' => $totalCapstoneStudies,
            'totalBsitCapstones' => $totalBsitCapstones,
            'totalCsTheses' => $totalCsTheses,

            'classroomId' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW CAPSTONE DETAILS
    |--------------------------------------------------------------------------
    */

    public function show(Request $request, int $capstoneId): View
    {
        $returnParameters = $request->validate([
            'type' => ['nullable', 'in:old,modern,student'],
            'return_to' => ['nullable', 'in:dashboard,repository,instructor_repository'],
            'search' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'digits:4', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'program' => ['nullable', 'string', 'max:255'],
            'adviser' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:newest,oldest,title_asc,title_desc,most_viewed'],
            'per_page' => ['nullable', 'integer', 'in:5,10,20,50'],
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
            'program' => $returnParameters['program'] ?? null,
            'adviser' => $returnParameters['adviser'] ?? null,
            'sort' => $returnParameters['sort'] ?? null,
            'per_page' => $returnParameters['per_page'] ?? null,
            'page' => $returnParameters['page'] ?? null,
        ], fn ($value) => $value !== null && $value !== ''));

        $type = $returnParameters['type']
            ?? (ArchivedCapstone::whereKey($capstoneId)->exists()
                ? 'old'
                : (StudentCapstoneSubmission::whereKey($capstoneId)->exists() ? 'student' : 'modern'));

        $user = $request->user();

        $classroomId = $user && $user->role === 'student'
            ? $user->classroomMemberships()
                ->where('status', 'approved')
                ->value('classroom_id')
            : null;

        if ($type === 'old') {

            $capstone = ArchivedCapstone::query()
                ->where('status', 'approved')
                ->findOrFail($capstoneId);

            $capstone = $this->prepareLegacyCapstone($capstone);

            $capstone->increment('views_count');

        } elseif ($type === 'student') {
            $capstone = StudentCapstoneSubmission::with([
                'submitter',
                'adviserUser',
                'authors.masterlistRecord',
            ])
                ->where('status', 'approved')
                ->findOrFail($capstoneId);

            $capstone->source_type = 'student';
            $capstone->program = $capstone->submitter?->program;
            $capstone->publication_date = $capstone->submitted_date;
            $capstone->increment('views_count');
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

    /*
    |--------------------------------------------------------------------------
    | EDIT CAPSTONE
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | UPDATE CAPSTONE
    |--------------------------------------------------------------------------
    */

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
            ->route('repository.show', [
                'capstoneId' => $capstone->id,
                'type' => $validated['type'],
            ])
            ->with('success', 'Capstone metadata updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLICATION DATE TIMESTAMP
    |--------------------------------------------------------------------------
    |
    | Converts modern Carbon dates and archived date strings into timestamps.
    | Returns null when the date is empty or invalid.
    |
    */

    private function publicationTimestamp(mixed $publicationDate): ?int
    {
        if ($publicationDate === null || trim((string) $publicationDate) === '') {
            return null;
        }

        $timestamp = strtotime((string) $publicationDate);

        return $timestamp === false ? null : $timestamp;
    }

    /*
    |--------------------------------------------------------------------------
    | LEGACY CAPSTONE PREPARATION
    |--------------------------------------------------------------------------
    */

    private function prepareLegacyCapstone(ArchivedCapstone $capstone): ArchivedCapstone
    {
        $capstone->source_type = 'old';

        $capstone->year_section = $capstone->publication_date;

        $capstone->study_year = $this->publicationYear(
            $capstone->publication_date
        );

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

    /*
    |--------------------------------------------------------------------------
    | GET ADVISER NAME
    |--------------------------------------------------------------------------
    */

    private function getAdviserName($capstone): string
    {
        if (($capstone->source_type ?? null) === 'old') {
            return trim((string) ($capstone->adviser ?? ''));
        }

        if (($capstone->source_type ?? null) === 'student') {
            return trim(implode(' ', array_filter([
                $capstone->adviserUser?->first_name,
                $capstone->adviserUser?->last_name,
            ])));
        }

        $instructor = $capstone->groupRoom?->classroom?->instructor;

        return trim(implode(' ', array_filter([
            $instructor?->first_name,
            $instructor?->last_name,
        ])));
    }

    /*
    |--------------------------------------------------------------------------
    | GET AUTHORS TEXT
    |--------------------------------------------------------------------------
    */

    private function getAuthorsText($capstone): string
    {
        $authors = $capstone->authors ?? null;

        if (is_array($authors)) {
            return implode(' ', $authors);
        }

        if (is_string($authors)) {
            return $authors;
        }

        if (($capstone->source_type ?? null) === 'student') {
            return $capstone->authors
                ->map(fn ($author): string => $author->masterlistRecord?->name
                    ?: trim(implode(' ', array_filter([
                        $author->masterlistRecord?->first_name,
                        $author->masterlistRecord?->last_name,
                    ]))))
                ->filter()
                ->implode(' ');
        }

        return collect($capstone->groupRoom?->members ?? [])
            ->map(fn ($member) => trim(implode(' ', array_filter([
                $member->user?->first_name ?? null,
                $member->user?->last_name ?? null,
            ]))))
            ->implode(' ');
    }

    /*
    |--------------------------------------------------------------------------
    | EXTRACT PUBLICATION YEAR
    |--------------------------------------------------------------------------
    */

    private function publicationYear(mixed $publicationDate): ?int
    {
        if (preg_match('/\b((?:19|20)\d{2})\b/', (string) $publicationDate, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }
}
