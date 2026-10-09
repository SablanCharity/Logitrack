<?php

namespace Tests\Feature;

use App\Services\SimilarityService;
use Tests\TestCase;

class SimilarityRepositoryRankingTest extends TestCase
{
    public function test_repository_results_are_ranked_by_hybrid_similarity(): void
    {
        $similarityService = app(SimilarityService::class);

        $proposed = 'Student Attendance Monitoring System';

        $studies = [
            [
                'id' => 1,
                'title' => 'Digital Classroom Attendance Tracking System',
                'abstract' => 'A system for recording and monitoring student attendance in classrooms.',
            ],
            [
                'id' => 2,
                'title' => 'Student Information Management System',
                'abstract' => 'A system for managing student information and academic records.',
            ],
            [
                'id' => 3,
                'title' => 'Inventory Management System',
                'abstract' => 'A system for managing product inventory and stock levels for retail businesses.',
            ],
        ];

        $results = $similarityService->rank(
            $proposed,
            $studies
        );

        $this->assertCount(3, $results);

        $scores = array_map(
            fn (array $result): float =>
                (float) $result['similarity']['hybrid_similarity'],
            $results
        );

        $sortedScores = $scores;
        rsort($sortedScores);

        $this->assertSame(
            $sortedScores,
            $scores
        );

        $this->assertSame(
            1,
            $results[0]['id']
        );
    }

    public function test_repository_ranking_preserves_study_information(): void
    {
        $similarityService = app(SimilarityService::class);

        $proposed = 'Student Attendance Monitoring System';

        $studies = [
            [
                'id' => 101,
                'title' => 'Digital Classroom Attendance Tracking System',
                'abstract' => 'A system for recording and monitoring student attendance in classrooms.',
            ],
            [
                'id' => 102,
                'title' => 'Inventory Management System',
                'abstract' => 'A system for managing product inventory and stock levels.',
            ],
        ];

        $results = $similarityService->rank(
            $proposed,
            $studies
        );

        $this->assertNotEmpty($results);

        foreach ($results as $result) {
            $this->assertArrayHasKey('id', $result);
            $this->assertArrayHasKey('title', $result);
            $this->assertArrayHasKey('abstract', $result);
            $this->assertArrayHasKey('similarity', $result);

            $comparison = $result['similarity'];

            $this->assertArrayHasKey(
                'hybrid_similarity',
                $comparison
            );

            $this->assertArrayHasKey(
                'hybrid_percentage',
                $comparison
            );

            $this->assertArrayHasKey(
                'lexical_percentage',
                $comparison
            );

            $this->assertArrayHasKey(
                'semantic_percentage',
                $comparison
            );

            $this->assertArrayHasKey(
                'shared_terms',
                $comparison
            );

            $this->assertArrayHasKey(
                'dss',
                $comparison
            );
        }
    }

    public function test_ranked_results_are_limited_by_configured_result_limit(): void
    {
        $similarityService = app(SimilarityService::class);

        $studies = [];

        for ($index = 1; $index <= 15; $index++) {
            $studies[] = [
                'id' => $index,
                'title' => "Student System {$index}",
                'abstract' => 'A software system for students and educational institutions.',
            ];
        }

        $results = $similarityService->rank(
            'Student Attendance Monitoring System',
            $studies
        );

        $limit = (int) config(
            'similarity.results_limit',
            10
        );

        $this->assertLessThanOrEqual(
            $limit,
            count($results)
        );

        $this->assertCount(
            min($limit, count($studies)),
            $results
        );
    }
}