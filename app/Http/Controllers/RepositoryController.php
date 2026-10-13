<?php

namespace App\Http\Controllers;

use App\Models\CapstoneProject;
use App\Models\OldCapstone;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RepositoryController extends Controller
{
    public function index(Request $request)
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

        return view('repository.index', compact('capstones', 'search'));
    }

    public function show($id, Request $request)
    {
        $type = $request->input('type');

        // Auto-detect if type is not provided in query string
        if (!$type) {
            $existsInOld = OldCapstone::where('id', $id)->exists();
            $type = $existsInOld ? 'old' : 'modern';
        }

        if ($type === 'old') {
            $capstone = OldCapstone::findOrFail($id);
            $capstone->source_type = 'old';
            $capstone->year_section = $capstone->publication_date;

            // Safe dummy relationship fallbacks including group_name
            $capstone->groupRoom = (object)[
                'id' => null,
                'group_name' => 'Legacy Record Group',
                'group_leader' => null,
                'classroom' => (object)[
                    'name' => 'Legacy Repository',
                    'instructor' => (object)['first_name' => $capstone->adviser, 'last_name' => '']
                ],
                'members' => collect($capstone->authors ?? [])->map(fn($author) => (object)[
                    'is_leader' => false,
                    'user' => (object)['first_name' => $author, 'last_name' => '']
                ])
            ];
            $capstone->instructor = (object)['first_name' => $capstone->adviser, 'last_name' => ''];
        } else {
            $capstone = CapstoneProject::findOrFail($id);
            $capstone->source_type = 'modern';
        }

        return view('repository.show', compact('capstone', 'type'));
    }
}