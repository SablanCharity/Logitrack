<?php

namespace Tests\Feature;

use App\Http\Controllers\SimilarityController;
use App\Models\CapstoneProject;
use App\Services\CapstoneRecommendationService;
use App\Services\SimilarityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Tests\TestCase;

class CapstoneRecommendationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_similarity_results_view_receives_three_recommendations(): void
    {
        $similarityService = Mockery::mock(SimilarityService::class);

        $similarityService
            ->shouldReceive('rank')
            ->once()
            ->with(
                Mockery::type('string'),
                []
            )
            ->andReturn([]);

        $recommendationService = new CapstoneRecommendationService();

        $controller = new SimilarityController(
            $similarityService,
            $recommendationService
        );

        $request = Request::create(
            '/similarity-checker',
            'POST',
            [
                'title' => 'Student Attendance Monitoring System',
                'abstract' => 'A system for monitoring student attendance, '
                    . 'tracking absences, and generating classroom reports.',
            ]
        );

        $response = $controller->check($request);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('similarity.results', $response->name());

        $data = $response->getData();

        $this->assertArrayHasKey('recommendations', $data);
        $this->assertCount(3, $data['recommendations']);

        foreach ($data['recommendations'] as $recommendation) {
            $this->assertNotEmpty($recommendation['title']);
            $this->assertNotEmpty($recommendation['abstract']);
            $this->assertArrayHasKey('reason', $recommendation);
            $this->assertArrayHasKey('design_note', $recommendation);
        }

        $this->assertSame(
            'Student Attendance Monitoring System',
            $data['title']
        );
    }
}