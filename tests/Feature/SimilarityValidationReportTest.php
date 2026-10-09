<?php

namespace Tests\Feature;

use App\Services\SimilarityValidationService;
use Tests\TestCase;

class SimilarityValidationReportTest extends TestCase
{
    public function test_real_similarity_validation_report(): void
    {
        $dataset = require base_path(
            'tests/Fixtures/similarity_validation_dataset.php'
        );

        $validationService = app(
            SimilarityValidationService::class
        );

        $validation = $validationService->validate($dataset);

        fwrite(
            STDOUT,
            PHP_EOL .
            '============================================================' .
            PHP_EOL .
            '             SIMILARITY VALIDATION REPORT' .
            PHP_EOL .
            '============================================================' .
            PHP_EOL
        );

        foreach ($validation['examples'] as $example) {
            fwrite(
                STDOUT,
                PHP_EOL .
                $example['name'] .
                PHP_EOL .
                '  Human:    ' .
                number_format($example['human_percentage'], 2) .
                '%' .
                PHP_EOL .
                '  System:   ' .
                number_format($example['system_percentage'], 2) .
                '%' .
                PHP_EOL .
                '  Error:    ' .
                number_format(
                    $example['absolute_error_percentage'],
                    2
                ) .
                '%' .
                PHP_EOL .
                '  Lexical:  ' .
                number_format(
                    (float) ($example['lexical_percentage'] ?? 0),
                    2
                ) .
                '%' .
                PHP_EOL .
                '  Semantic: ' .
                (
                    $example['semantic_percentage'] !== null
                        ? number_format(
                            (float) $example['semantic_percentage'],
                            2
                        ) . '%'
                        : 'N/A'
                ) .
                PHP_EOL .
                '  Hybrid:   ' .
                (
                    $example['hybrid_percentage'] !== null
                        ? number_format(
                            (float) $example['hybrid_percentage'],
                            2
                        ) . '%'
                        : 'N/A'
                ) .
                PHP_EOL .
                '------------------------------------------------------------' .
                PHP_EOL
            );
        }

        $metrics = $validation['metrics'];

        fwrite(
            STDOUT,
            PHP_EOL .
            '============================================================' .
            PHP_EOL .
            '                     SUMMARY' .
            PHP_EOL .
            '============================================================' .
            PHP_EOL .
            'Samples:  ' .
            $metrics['sample_count'] .
            PHP_EOL .
            'MAE:      ' .
            number_format(
                (float) $metrics['mean_absolute_error_percentage'],
                2
            ) .
            '%' .
            PHP_EOL .
            'RMSE:     ' .
            number_format(
                (float) $metrics['root_mean_squared_error_percentage'],
                2
            ) .
            '%' .
            PHP_EOL .
            'Pearson:  ' .
            number_format(
                (float) $metrics['pearson_correlation'],
                4
            ) .
            PHP_EOL .
            'Spearman: ' .
            number_format(
                (float) $metrics['spearman_correlation'],
                4
            ) .
            PHP_EOL .
            '============================================================' .
            PHP_EOL
        );

        $this->assertCount(
            15,
            $validation['examples']
        );
    }
}