<?php

namespace Tests\Feature;

use App\Services\SimilarityValidationService;
use Tests\TestCase;

class SimilarityValidationRealTest extends TestCase
{
    public function test_real_similarity_algorithm_can_validate_the_full_dataset(): void
    {
        $dataset = require base_path(
            'tests/Fixtures/similarity_validation_dataset.php'
        );

        $validationService = app(
            SimilarityValidationService::class
        );

        $validation = $validationService->validate($dataset);

        $this->assertCount(
            15,
            $validation['examples']
        );

        $this->assertSame(
            15,
            $validation['metrics']['sample_count']
        );

        $this->assertNotNull(
            $validation['metrics']['mean_absolute_error']
        );

        $this->assertNotNull(
            $validation['metrics']['root_mean_squared_error']
        );

        $this->assertNotNull(
            $validation['metrics']['pearson_correlation']
        );

        $this->assertNotNull(
            $validation['metrics']['spearman_correlation']
        );

        foreach ($validation['examples'] as $example) {
            $this->assertGreaterThanOrEqual(
                0,
                $example['system_score']
            );

            $this->assertLessThanOrEqual(
                1,
                $example['system_score']
            );
        }

        fwrite(
            STDOUT,
            PHP_EOL .
            '=== Similarity Validation Results ===' .
            PHP_EOL .
            'Samples: ' .
            $validation['metrics']['sample_count'] .
            PHP_EOL .
            'MAE: ' .
            $validation['metrics']['mean_absolute_error_percentage'] .
            '%' .
            PHP_EOL .
            'RMSE: ' .
            $validation['metrics']['root_mean_squared_error_percentage'] .
            '%' .
            PHP_EOL .
            'Pearson: ' .
            $validation['metrics']['pearson_correlation'] .
            PHP_EOL .
            'Spearman: ' .
            $validation['metrics']['spearman_correlation'] .
            PHP_EOL .
            '=======================================' .
            PHP_EOL
        );
    }
}