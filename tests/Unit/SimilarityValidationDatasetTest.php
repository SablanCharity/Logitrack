<?php

namespace Tests\Unit;

use App\Services\SimilarityService;
use App\Services\SimilarityValidationService;
use Mockery;
use Tests\TestCase;

class SimilarityValidationDatasetTest extends TestCase
{
    public function test_validation_dataset_contains_fifteen_examples(): void
    {
        $dataset = require base_path(
            'tests/Fixtures/similarity_validation_dataset.php'
        );

        $this->assertCount(15, $dataset);
    }

    public function test_validation_dataset_has_required_fields(): void
    {
        $dataset = require base_path(
            'tests/Fixtures/similarity_validation_dataset.php'
        );

        foreach ($dataset as $example) {
            $this->assertArrayHasKey('name', $example);
            $this->assertArrayHasKey('proposed', $example);
            $this->assertArrayHasKey('existing', $example);
            $this->assertArrayHasKey('human_score', $example);

            $this->assertNotEmpty($example['name']);
            $this->assertNotEmpty($example['proposed']);
            $this->assertNotEmpty($example['existing']);

            $this->assertIsNumeric($example['human_score']);
            $this->assertGreaterThanOrEqual(
                0,
                $example['human_score']
            );
            $this->assertLessThanOrEqual(
                1,
                $example['human_score']
            );
        }
    }

    public function test_validation_dataset_can_be_processed(): void
    {
        $dataset = require base_path(
            'tests/Fixtures/similarity_validation_dataset.php'
        );

        $similarityService = Mockery::mock(
            SimilarityService::class
        );

        $similarityService
            ->shouldReceive('compare')
            ->times(count($dataset))
            ->andReturn([
                'lexical_percentage' => 50.0,
                'semantic_percentage' => 70.0,
                'hybrid_similarity' => 0.60,
                'hybrid_percentage' => 60.0,
                'semantic_available' => true,
            ]);

        $validationService = new SimilarityValidationService(
            $similarityService
        );

        $result = $validationService->validate($dataset);

        $this->assertCount(
            15,
            $result['examples']
        );

        $this->assertSame(
            15,
            $result['metrics']['sample_count']
        );

        $this->assertNotNull(
            $result['metrics']['mean_absolute_error']
        );

        $this->assertNotNull(
            $result['metrics']['root_mean_squared_error']
        );
    }
}