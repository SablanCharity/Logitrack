<?php

namespace Tests\Unit;

use App\Services\CapstoneRecommendationService;
use Tests\TestCase;

class CapstoneRecommendationServiceTest extends TestCase
{
    protected CapstoneRecommendationService $recommendationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recommendationService = new CapstoneRecommendationService();
    }

    public function test_it_returns_three_recommendations_for_attendance_studies(): void
    {
        $recommendations = $this->recommendationService->recommend(
            'Student Attendance Monitoring System',
            'A system for tracking classroom attendance, monitoring absences, '
                . 'and generating student attendance reports.'
        );

        $this->assertCount(3, $recommendations);

        foreach ($recommendations as $recommendation) {
            $this->assertArrayHasKey('title', $recommendation);
            $this->assertArrayHasKey('abstract', $recommendation);
            $this->assertArrayHasKey('reason', $recommendation);
            $this->assertArrayHasKey('design_note', $recommendation);

            $this->assertNotEmpty($recommendation['title']);
            $this->assertNotEmpty($recommendation['abstract']);
        }

        $this->assertStringContainsString(
            'Absence Alert',
            $recommendations[0]['title']
        );
    }

    public function test_it_returns_inventory_related_recommendations(): void
    {
        $recommendations = $this->recommendationService->recommend(
            'Inventory Management System',
            'A system for tracking product stock and inventory levels.'
        );

        $this->assertCount(3, $recommendations);

        $this->assertStringContainsString(
            'Inventory',
            $recommendations[0]['title']
        );

        $this->assertStringContainsString(
            'Expiration',
            $recommendations[1]['title']
        );
    }

    public function test_it_returns_restaurant_related_recommendations(): void
    {
        $recommendations = $this->recommendationService->recommend(
            'Restaurant Ordering System',
            'A system for restaurant food ordering and reservations.'
        );

        $this->assertCount(3, $recommendations);

        $this->assertStringContainsString(
            'Restaurant',
            $recommendations[0]['title']
        );
    }

    public function test_it_returns_general_recommendations_for_other_topics(): void
    {
        $recommendations = $this->recommendationService->recommend(
            'Community Library Management System',
            'A platform for managing library records and book borrowing.'
        );

        $this->assertCount(3, $recommendations);

        $this->assertStringContainsString(
            'Research Workflow',
            $recommendations[0]['title']
        );
    }

    public function test_it_includes_design_guidance_when_review_areas_are_provided(): void
    {
        $recommendations = $this->recommendationService->recommend(
            'Student Attendance Monitoring System',
            'A system for tracking classroom attendance.',
            [],
            [
                'Research objectives',
                'Target users',
                'System scope',
            ]
        );

        $this->assertCount(3, $recommendations);

        foreach ($recommendations as $recommendation) {
            $this->assertStringContainsString(
                'Research objectives',
                $recommendation['design_note']
            );

            $this->assertStringContainsString(
                'Target users',
                $recommendation['design_note']
            );
        }
    }

    public function test_it_provides_default_design_guidance_without_review_areas(): void
    {
        $recommendations = $this->recommendationService->recommend(
            'Student Attendance Monitoring System',
            'A system for tracking classroom attendance.'
        );

        $this->assertCount(3, $recommendations);

        $this->assertStringContainsString(
            'verify its objectives',
            $recommendations[0]['design_note']
        );
    }

    public function test_each_recommendation_has_non_empty_design_guidance(): void
    {
        $recommendations = $this->recommendationService->recommend(
            'Student Attendance Monitoring System',
            'A system for tracking student attendance and identifying frequent absences.'
        );

        $this->assertCount(3, $recommendations);

        foreach ($recommendations as $recommendation) {
            $this->assertArrayHasKey('design_note', $recommendation);
            $this->assertNotEmpty($recommendation['design_note']);
        }
    }
}