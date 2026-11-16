<?php

namespace Tests\Feature;

use App\Http\Controllers\SimilarityController;
use App\Models\User;
use App\Services\CapstoneRecommendationService;
use App\Services\SimilarityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Mockery;
use Tests\TestCase;

class CapstoneRecommendationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_similarity_results_view_receives_three_ai_recommendations(): void
    {
        Http::fake([
            '*' => Http::response([
                'message' => [
                    'content' => json_encode([
                        'recommendations' => array_map(fn (int $number): array => [
                            'title' => 'Alternative project '.$number,
                            'idea' => 'A concise concept seed '.$number.'.',
                        ], [1, 2, 3]),
                    ]),
                ],
            ]),
        ]);

        $response = $this->callController();

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('similarity.results', $response->name());

        $data = $response->getData();
        $this->assertCount(3, $data['recommendations']);
        $this->assertNull($data['recommendationError']);

        foreach ($data['recommendations'] as $recommendation) {
            $this->assertArrayHasKey('idea', $recommendation);
            $this->assertSame(['title', 'idea'], array_keys($recommendation));
        }
    }

    public function test_similarity_results_show_a_clear_error_when_ollama_fails(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'model unavailable'], 503),
        ]);

        $response = $this->callController();

        $this->assertInstanceOf(View::class, $response);
        $data = $response->getData();

        $this->assertSame([], $data['recommendations']);
        $this->assertStringContainsString('HTTP 503', $data['recommendationError']);
    }

    private function callController(): View
    {
        $similarityService = Mockery::mock(SimilarityService::class);
        $similarityService->shouldReceive('rank')
            ->once()
            ->andReturn([]);

        $controller = new SimilarityController(
            $similarityService,
            new CapstoneRecommendationService()
        );

        $request = Request::create('/similarity-checker', 'POST', [
            'title' => 'Campus Clinic Appointment System',
            'abstract' => 'A study about organizing clinic appointments and patient queues.',
        ]);
        $user = new User();
        $user->forceFill(['role' => 'instructor']);
        $request->setUserResolver(fn () => $user);

        return $controller->check($request);
    }
}
