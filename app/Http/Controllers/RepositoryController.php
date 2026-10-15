<?php

namespace App\Http\Controllers;

use App\Models\CapstoneProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
            ->map(fn ($publicationDate) => \Illuminate\Support\Carbon::parse($publicationDate)->year)
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

            $totalCapstoneStudies = (clone $approvedStudies)->count();
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

        $showSearchResults = ! $isDashboard || ! $showDashboardOverview;
        $capstones = $showSearchResults
            ? CapstoneProject::with([
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
                ->latest('approved_at')
                ->paginate($isDashboard ? 5 : 10)
            : collect();

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

        $user = $request->user();
        $classroomId = $user->role === 'student'
            ? $user->classroomMemberships()->where('status', 'approved')->value('classroom_id')
            : null;

        $capstone = CapstoneProject::with([
            'groupRoom.classroom.instructor',
            'groupRoom.members.user',
        ])
            ->where('status', 'admin_approved')
            ->findOrFail($capstoneId);

        $capstone->increment('views_count');

        return view('repository.show', compact('capstone', 'classroomId', 'backUrl'));
    }

}