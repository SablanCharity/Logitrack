<?php

namespace App\Http\Controllers;

use App\Models\CapstoneProject;
use App\Services\SimilarityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SimilarityController extends Controller
{
    public function __construct(
        protected SimilarityService $similarityService
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
     * in the repository.
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
         * Combine the title and abstract.
         *
         * The title is included because important research
         * terms are often present there.
         */
        $proposedText = trim(
            $validated['title'] .
            ' ' .
            $validated['abstract']
        );

        /*
         * Retrieve existing approved studies.
         *
         * The Similarity Checker should compare the proposed
         * study against studies that are already approved
         * for the repository.
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
         * Prepare repository studies for the similarity engine.
         */
        $existingStudies = [];

        foreach ($studies as $study) {
            /*
             * Build the existing study's searchable text.
             *
             * We intentionally check multiple possible field
             * names so this controller can work with the current
             * project structure without changing the database.
             */

            $title = $study->title
                ?? $study->project_title
                ?? $study->name
                ?? '';

            $abstract = $study->abstract
                ?? $study->description
                ?? '';

            $existingStudies[] = [
                'id' => $study->id,

                'title' => $title,

                'abstract' => $abstract,

                'study' => $study,
            ];
        }

        /*
         * Rank the existing studies from highest similarity
         * to lowest similarity.
         */
        $results = $this->similarityService->rank(
            $proposedText,
            $existingStudies
        );

        /*
         * Display the results page.
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
            ]
        );
    }
}