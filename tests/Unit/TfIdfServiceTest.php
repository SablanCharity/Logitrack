<?php

namespace Tests\Unit;

use App\Services\TextPreprocessor;
use App\Services\TfIdfService;
use Tests\TestCase;

class TfIdfServiceTest extends TestCase
{
    private TfIdfService $tfIdfService;

    private TextPreprocessor $preprocessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tfIdfService = new TfIdfService();
        $this->preprocessor = new TextPreprocessor();
    }

    public function test_identical_text_has_maximum_similarity(): void
    {
        $text = 'student attendance monitoring system';

        $tokens = $this->preprocessor->process($text);

        $result = $this->tfIdfService->compare(
            $tokens,
            $tokens
        );

        $this->assertIsFloat($result);
        $this->assertEqualsWithDelta(1.0, $result, 0.000001);
    }

    public function test_similarity_is_between_zero_and_one(): void
    {
        $tokensA = $this->preprocessor->process(
            'student attendance monitoring system'
        );

        $tokensB = $this->preprocessor->process(
            'classroom inventory management application'
        );

        $result = $this->tfIdfService->compare(
            $tokensA,
            $tokensB
        );

        $this->assertGreaterThanOrEqual(0.0, $result);
        $this->assertLessThanOrEqual(1.0, $result);
    }

    public function test_related_text_has_shared_terms(): void
    {
        $tokensA = $this->preprocessor->process(
            'student attendance monitoring system'
        );

        $tokensB = $this->preprocessor->process(
            'student attendance reporting system'
        );

        $sharedTerms = $this->tfIdfService->sharedTerms(
            $tokensA,
            $tokensB
        );

        $this->assertIsArray($sharedTerms);
        $this->assertNotEmpty($sharedTerms);
        $this->assertContains('attendance', $sharedTerms);
    }

    public function test_comparison_returns_a_numeric_similarity(): void
    {
        $tokensA = $this->preprocessor->process(
            'digital attendance tracking'
        );

        $tokensB = $this->preprocessor->process(
            'electronic attendance recording'
        );

        $result = $this->tfIdfService->compare(
            $tokensA,
            $tokensB
        );

        $this->assertIsNumeric($result);
    }
}