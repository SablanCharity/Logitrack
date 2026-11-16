<?php

namespace Tests\Unit;

use App\Services\CapstoneRecommendationService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class CapstoneRecommendationServiceTest extends TestCase
{
    public function test_it_generates_three_valid_recommendations_from_ollama(): void
    {
        Http::fake([
            'http://127.0.0.1:11434/api/chat' => Http::response([
                'message' => ['content' => json_encode($this->validPayload())],
            ]),
        ]);

        $recommendations = app(CapstoneRecommendationService::class)->recommend(
            'Campus Clinic Appointment System',
            'A study about organizing clinic appointments and patient queues.',
            [[
                'title' => 'Clinic Patient Record System',
                'abstract' => 'A repository study about storing clinic visit records.',
            ]],
        );

        $this->assertCount(3, $recommendations);

        foreach ($recommendations as $recommendation) {
            $this->assertSame(
                ['title', 'idea'],
                array_keys($recommendation)
            );

            foreach ($recommendation as $value) {
                $this->assertNotEmpty($value);
            }
        }

        Http::assertSent(function ($request): bool {
            $prompt = $request['messages'][1]['content'];

            return $request->url() === 'http://127.0.0.1:11434/api/chat'
                && $request['model'] === 'qwen2.5:1.5b'
                && $request['format'] === 'json'
                && str_contains($prompt, 'Campus Clinic Appointment System')
                && str_contains($prompt, 'Clinic Patient Record System')
                && str_contains($request['messages'][0]['content'], 'undergraduate');
        });
    }

    public function test_it_rejects_invalid_json_from_ollama(): void
    {
        Http::fake([
            '*' => Http::response(['message' => ['content' => 'not json']], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('invalid recommendation data');

        app(CapstoneRecommendationService::class)->recommend('Study title', 'Study abstract');
    }

    public function test_it_rejects_recommendations_with_missing_fields(): void
    {
        $payload = $this->validPayload();
        unset($payload['recommendations'][1]['idea']);

        Http::fake([
            '*' => Http::response([
                'message' => ['content' => json_encode($payload)],
            ]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('omitted a required recommendation detail');

        app(CapstoneRecommendationService::class)->recommend('Study title', 'Study abstract');
    }

    public function test_it_reports_ollama_api_failures(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'model unavailable'], 503),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTP 503');

        app(CapstoneRecommendationService::class)->recommend('Study title', 'Study abstract');
    }

    public function test_it_explains_when_the_configured_ollama_model_is_missing(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'model "qwen2.5" not found'], 404),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('configured recommendation model [qwen2.5:1.5b]');

        app(CapstoneRecommendationService::class)->recommend('Study title', 'Study abstract');
    }

    private function validPayload(): array
    {
        return [
            'recommendations' => array_map(fn (int $number): array => [
                'title' => 'Alternative project '.$number,
                'idea' => 'A concise concept seed for an undergraduate project '.$number.'.',
            ], [1, 2, 3]),
        ];
    }
}
