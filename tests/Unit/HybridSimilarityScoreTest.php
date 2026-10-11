<?php

namespace Tests\Unit;

use App\Services\EmbeddingService;
use App\Services\SimilarityService;
use App\Services\TextPreprocessor;
use App\Services\TfIdfService;
use Tests\TestCase;

class HybridSimilarityScoreTest extends TestCase
{
    private function createService(): SimilarityService
    {
        return new SimilarityService(
            new TextPreprocessor(),
            new TfIdfService(),
            new EmbeddingService()
        );
    }

    public function test_compare_returns_hybrid_and_component_scores(): void
    {
        config([
            'similarity.hybrid.method' => 'weighted_mean',
            'similarity.hybrid.lexical_weight' => 0.40,
            'similarity.hybrid.semantic_weight' => 0.60,
        ]);

        $result = $this->createService()->compare(
            'Student attendance monitoring system',
            'Student attendance monitoring system'
        );

        $this->assertArrayHasKey('lexical_similarity', $result);
        $this->assertArrayHasKey('hybrid_similarity', $result);
        $this->assertArrayHasKey('shared_terms', $result);
        $this->assertArrayHasKey('dss', $result);

        $this->assertGreaterThanOrEqual(0, $result['hybrid_similarity']);
        $this->assertLessThanOrEqual(1, $result['hybrid_similarity']);
    }

    public function test_identical_text_has_high_lexical_similarity(): void
    {
        $text = 'Student attendance monitoring system';

        $result = $this->createService()->compare($text, $text);

        $this->assertEqualsWithDelta(
            1.0,
            $result['lexical_similarity'],
            0.000001
        );
    }

    public function test_comparison_returns_shared_terms(): void
    {
        $result = $this->createService()->compare(
            'Student attendance monitoring system',
            'Student attendance reporting system'
        );

        $this->assertIsArray($result['shared_terms']);
        $this->assertNotEmpty($result['shared_terms']);
        $this->assertContains('attendance', $result['shared_terms']);
    }

    public function test_comparison_includes_decision_support_guidance(): void
    {
        $result = $this->createService()->compare(
            'Student attendance monitoring system',
            'Student attendance monitoring system'
        );

        $this->assertIsArray($result['dss']);
        $this->assertArrayHasKey('level', $result['dss']);
        $this->assertArrayHasKey('areas_to_review', $result['dss']);
    }
}