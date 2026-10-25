<?php

namespace App\Http\Controllers;

use App\Models\ArchivedCapstone;
use App\Models\CapstoneProject;
use App\Services\CapstoneRecommendationService;
use App\Services\SimilarityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SimilarityController extends Controller
{
    public function __construct(
        protected SimilarityService $similarityService,
        protected CapstoneRecommendationService $recommendationService
    ) {}

    /**
     * Display the Similarity Checker form.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if($user->role === 'admin', 403, 'Similarity checking is not available to administrators.');
        $classroomId = $user->role === 'student'
            ? $user->classroomMemberships()->where('status', 'approved')->value('classroom_id')
            : null;

        return view('similarity.index', compact('classroomId'));
    }

    /**
     * Analyze the proposed study against approved studies
     * in the repository and generate alternative project ideas.
     */
    public function check(Request $request): View|RedirectResponse
    {
        abort_if($request->user()->role === 'admin', 403, 'Similarity checking is not available to administrators.');
        /*
         * Validate the proposed study.
         */
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'abstract' => [
                'required',
                'string',
                'min:'.config(
                    'similarity.minimum_text_length',
                    20
                ),
            ],
        ]);

        /*
         * Combine the title and abstract for analysis.
         */
        $proposedText = trim(
            $validated['title'].' '.$validated['abstract']
        );

        /*
         * Retrieve approved modern repository studies.
         */
        $modernStudies = CapstoneProject::query()
            ->where(
                'status',
                config(
                    'similarity.repository_status',
                    'admin_approved'
                )
            )
            ->get()
            ->map(function (CapstoneProject $study): array {
                return [
                    'id' => $study->id,
                    'title' => $study->title ?? '',
                    'abstract' => $study->abstract ?? '',
                    'type' => 'modern',
                    'study' => $study,
                ];
            });

        /*
         * Retrieve approved OCR/old repository studies.
         */
        $oldStudies = ArchivedCapstone::query()
            ->where('status', 'approved')
            ->get()
            ->map(function (ArchivedCapstone $study): array {
                return [
                    'id' => $study->id,
                    'title' => $study->title ?? '',
                    'abstract' => $study->abstract ?? '',
                    'type' => 'old',
                    'study' => $study,
                ];
            });

        /*
         * Merge both modern and old studies into a single array.
         */
        $existingStudies = $modernStudies->concat($oldStudies)->values()->all();

        /*
         * Compare the proposed study against all approved studies.
         * The service ranks the results by hybrid similarity.
         */
        $results = $this->similarityService->rank(
            $proposedText,
            $existingStudies
        );

        /*
         * Keep the highest-ranked result for display.
         * The full ranked results remain available for internal processing.
         */
        $displayResults = $results;

        /*
         * Extract DSS review areas from the highest-ranked result.
         */
        $topResult = $results[0]['similarity'] ?? [];

        $reviewAreas = $topResult['dss']['areas_to_review'] ?? [];

        /*
         * Generate three alternative capstone recommendations.
         */
        $recommendations = $this->recommendationService->recommend(
            $validated['title'],
            $validated['abstract'],
            $existingStudies,
            $reviewAreas
        );

        /*
         * Display only the most similar capstone project.
         * The study count still reflects ALL approved studies compared.
         */
        $user = $request->user();
        $classroomId = $user->role === 'student'
            ? $user->classroomMemberships()->where('status', 'approved')->value('classroom_id')
            : null;

        return view(
            'similarity.results',
            [
                'title' => $validated['title'],
                'abstract' => $validated['abstract'],

                'proposedTitle' => $validated['title'],
                'proposedAbstract' => $validated['abstract'],

                'results' => $results,
                'studyCount' => count($existingStudies),

                'recommendations' => $recommendations,
                'classroomId' => $classroomId,
            ]
        );
    }
}
