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

        // 1. Get modern approved capstones
        $modernCapstones = CapstoneProject::where('status', 'admin_approved')
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('abstract', 'like', "%{$search}%")
                      ->orWhere('program', 'like', "%{$search}%");
            })
            ->get()
            ->map(function ($item) {
                $item->source_type = 'modern';
                return $item;
            });

        // 2. Get approved old/OCR capstones
        $oldCapstones = OldCapstone::where('status', 'admin_approved')
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('abstract', 'like', "%{$search}%")
                      ->orWhere('program', 'like', "%{$search}%");
            })
            ->get()
            ->map(function ($item) {
                $item->source_type = 'old';
                $item->year_section = $item->publication_date; 
                
                // Safe dummy relationship fallbacks including group_name
                $item->groupRoom = (object)[
                    'id' => null,
                    'group_name' => 'Legacy Record Group',
                    'group_leader' => null,
                    'classroom' => (object)[
                        'name' => 'Legacy Repository',
                        'instructor' => (object)['first_name' => $item->adviser, 'last_name' => '']
                    ],
                    'members' => collect($item->authors ?? [])->map(fn($author) => (object)[
                        'is_leader' => false,
                        'user' => (object)['first_name' => $author, 'last_name' => '']
                    ])
                ];
                $item->instructor = (object)['first_name' => $item->adviser, 'last_name' => ''];
                
                return $item;
            });

        // 3. Merge collections and sort by newest first
        $mergedCollection = $modernCapstones->concat($oldCapstones)->sortByDesc('created_at')->values();

        // 4. Manually paginate the merged collection
        $perPage = 10;
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
            'classroomId' => null,
        ]);
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
