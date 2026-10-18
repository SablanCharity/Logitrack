<?php

namespace App\Http\Controllers;

use App\Models\CapstoneProject;
use App\Models\OldCapstone;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class RepositoryController extends Controller
{
    /**
     * Display approved modern and legacy capstones.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'regex:/^\d{4}$/'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = $validated['search'] ?? null;
        $year = $validated['year'] ?? null;

        // 1. Retrieve approved modern capstones only.
        $modernCapstones = CapstoneProject::where(
            'status',
            'admin_approved'
        )
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

        // 2. Retrieve approved legacy/OCR capstones only.
        $oldCapstones = OldCapstone::where(
            'status',
            'admin_approved'
        )
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

        // 3. Merge approved records.
        $mergedCollection = $modernCapstones
            ->concat($oldCapstones)
            ->sortByDesc(function ($capstone) {
                return $capstone->created_at;
            })
            ->values();

        // Get the study year from publication date or creation date.
        $getStudyYear = function ($capstone) {
            $date = $capstone->publication_date
                ?? $capstone->created_at;

            if (empty($date)) {
                return null;
            }

            try {
                return Carbon::parse($date)->format('Y');
            } catch (\Throwable $e) {
                return null;
            }
        };

        // 4. Build year options from approved records.
        $years = $mergedCollection
            ->map(fn ($capstone) => $getStudyYear($capstone))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        // 5. Filter by selected year.
        if ($year !== null && $year !== '') {
            $mergedCollection = $mergedCollection
                ->filter(function ($capstone) use ($getStudyYear, $year) {
                    return $getStudyYear($capstone) === (string) $year;
                })
                ->values();
        }

        // 6. Paginate the merged collection.
        $perPage = 10;
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

        // 7. Pass the variables expected by the repository view.
        return view('repository.index', [
            'capstones' => $capstones,
            'search' => $search,
            'year' => $year,
            'years' => $years,
            'classroomId' => null,
        ]);
    }

    /**
     * Display an approved repository capstone.
     */
    public function show($id, Request $request)
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:modern,old'],
        ]);

        $type = $validated['type'] ?? null;

        if (! in_array($type, ['modern', 'old'], true)) {
            // Only auto-detect when the ID belongs to exactly one source.
            $existsInOld = OldCapstone::where('id', $id)->exists();
            $existsInModern = CapstoneProject::where('id', $id)->exists();

            if ($existsInOld && $existsInModern) {
                abort(
                    400,
                    'Please specify the capstone source type.'
                );
            }

            if (! $existsInOld && ! $existsInModern) {
                abort(404);
            }

            $type = $existsInOld ? 'old' : 'modern';
        }

        if ($type === 'old') {
            $capstone = OldCapstone::where('status', 'admin_approved')
                ->findOrFail($id);

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
        } else {
            $capstone = CapstoneProject::where(
                'status',
                'admin_approved'
            )->findOrFail($id);

            $capstone->source_type = 'modern';
        }

        return view('repository.show', compact('capstone', 'type'));
    }
}
