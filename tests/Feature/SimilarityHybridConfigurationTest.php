<?php

namespace Tests\Feature;

use App\Services\SimilarityService;
use Tests\TestCase;

class SimilarityHybridConfigurationTest extends TestCase
{
    public function test_similarity_service_uses_weighted_mean_configuration(): void
    {
        $this->assertSame(
            'weighted_mean',
            config('similarity.hybrid.method')
        );

        $this->assertSame(
            '0.40',
            (string) config('similarity.hybrid.lexical_weight')
        );

        $this->assertSame(
            '0.60',
            (string) config('similarity.hybrid.semantic_weight')
        );
    }

    public function test_similarity_service_produces_expected_weighted_hybrid_score(): void
    {
        $comparison = app(SimilarityService::class)->compare(
            'Student Attendance Monitoring System',
            'Digital Classroom Attendance Tracking and Reporting System'
        );

        $lexical = (float) $comparison['lexical_similarity'];
        $semantic = (float) $comparison['semantic_similarity'];
        $hybrid = (float) $comparison['hybrid_similarity'];

        $expected = ($lexical * 0.40) + ($semantic * 0.60);

        $this->assertEqualsWithDelta(
            $expected,
            $hybrid,
            0.000001
        );

        $this->assertEqualsWithDelta(
            60.22,
            (float) $comparison['hybrid_percentage'],
            0.01
        );
    }

    public function test_semantic_similarity_is_available_for_validation_example(): void
    {
        $comparison = app(SimilarityService::class)->compare(
            'Student Attendance Monitoring System',
            'Digital Classroom Attendance Tracking and Reporting System'
        );

        $this->assertTrue(
            $comparison['semantic_available']
        );

        $this->assertEqualsWithDelta(
            26.06,
            (float) $comparison['lexical_percentage'],
            0.01
        );

        $this->assertEqualsWithDelta(
            83.00,
            (float) $comparison['semantic_percentage'],
            0.01
        );
    }
}