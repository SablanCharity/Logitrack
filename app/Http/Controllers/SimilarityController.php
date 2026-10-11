<?php

namespace App\Http\Controllers;

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
    ) {
    }

    /**
     * Display the Similarity Checker form.
     */
    public function index(): View
    {
        return view('similarity.index');
    }

    /**
     * Analyze the proposed study against approved studies
     * in the repository and generate alternative project ideas.
     */
    public function check(Request $request): View|RedirectResponse
    {
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
                'min:' . config(
                    'similarity.minimum_text_length',
                    20
                ),
            ],
        ]);

        /*
         * Combine the title and abstract for analysis.
         */
        $proposedText = trim(
            $validated['title'] . ' ' . $validated['abstract']
        );

        /*
         * Retrieve existing approved repository studies.
         */
        $studies = CapstoneProject::query()
            ->where(
                'status',
                config(
                    'similarity.repository_status',
                    'admin_approved'
                )
            )
            ->get();

        /*
         * Prepare repository studies for similarity analysis.
         */
        $existingStudies = $studies
            ->map(function (CapstoneProject $study): array {
                return [
                    'id' => $study->id,
                    'title' => $study->title ?? '',
                    'abstract' => $study->abstract ?? '',
                    'study' => $study,
                ];
            })
            ->values()
            ->all();

        /*
         * Rank existing studies by hybrid similarity.
         */
        $results = $this->similarityService->rank(
            $proposedText,
            $existingStudies
        );

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
         * Display the similarity results and recommendations.
         */
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
            ]
        );
    }
}
