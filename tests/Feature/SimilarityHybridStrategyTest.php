<?php

namespace Tests\Feature;

use App\Services\SimilarityService;
use App\Services\SimilarityValidationService;
use Tests\TestCase;

class SimilarityHybridStrategyTest extends TestCase
{
    public function test_hybrid_strategies_against_validation_dataset(): void
    {
        $dataset = require base_path(
            'tests/Fixtures/similarity_validation_dataset.php'
        );

        $similarityService = app(
            SimilarityService::class
        );

        $validationService = app(
            SimilarityValidationService::class
        );

        /*
         * First obtain the real lexical and semantic scores
         * from the existing locked SimilarityService.
         */
        $comparisons = [];

        foreach ($dataset as $example) {
            $comparison = $similarityService->compare(
                $example['proposed'],
                $example['existing']
            );

            $comparisons[] = [
                'name' => $example['name'],
                'human_score' => (float) $example['human_score'],
                'lexical' => (float) (
                    $comparison['lexical_similarity'] ?? 0
                ),
                'semantic' => $comparison['semantic_similarity'] !== null
                    ? (float) $comparison['semantic_similarity']
                    : null,
            ];
        }

        $strategies = [
            'Harmonic Mean' => function (
                float $lexical,
                float $semantic
            ): float {
                if ($semantic === null) {
                    return $lexical;
                }

                if ($lexical + $semantic <= 0) {
                    return 0.0;
                }

                return (
                    2 * $lexical * $semantic
                ) / (
                    $lexical + $semantic
                );
            },

            'Arithmetic Mean' => function (
                float $lexical,
                float $semantic
            ): float {
                if ($semantic === null) {
                    return $lexical;
                }

                return (
                    $lexical + $semantic
                ) / 2;
            },

            'Weighted 30/70' => function (
                float $lexical,
                float $semantic
            ): float {
                if ($semantic === null) {
                    return $lexical;
                }

                return (
                    ($lexical * 0.30) +
                    ($semantic * 0.70)
                );
            },

            'Weighted 40/60' => function (
                float $lexical,
                float $semantic
            ): float {
                if ($semantic === null) {
                    return $lexical;
                }

                return (
                    ($lexical * 0.40) +
                    ($semantic * 0.60)
                );
            },

            'Weighted 50/50' => function (
                float $lexical,
                float $semantic
            ): float {
                if ($semantic === null) {
                    return $lexical;
                }

                return (
                    ($lexical * 0.50) +
                    ($semantic * 0.50)
                );
            },

            'Weighted 20/80' => function (
                float $lexical,
                float $semantic
            ): float {
                if ($semantic === null) {
                    return $lexical;
                }

                return (
                    ($lexical * 0.20) +
                    ($semantic * 0.80)
                );
            },
        ];

        $results = [];

        foreach ($strategies as $strategyName => $strategy) {
            $humanScores = [];
            $systemScores = [];
            $absoluteErrors = [];
            $squaredErrors = [];

            foreach ($comparisons as $comparison) {
                $human = $comparison['human_score'];

                $lexical = $comparison['lexical'];
                $semantic = $comparison['semantic'];

                $system = $strategy(
                    $lexical,
                    $semantic
                );

                $system = max(
                    0.0,
                    min(1.0, $system)
                );

                $error = abs(
                    $system - $human
                );

                $humanScores[] = $human;
                $systemScores[] = $system;
                $absoluteErrors[] = $error;
                $squaredErrors[] = $error ** 2;
            }

            $count = count($humanScores);

            $mae = array_sum(
                $absoluteErrors
            ) / $count;

            $rmse = sqrt(
                array_sum($squaredErrors) / $count
            );

            $pearson = $this->pearsonCorrelation(
                $humanScores,
                $systemScores
            );

            $spearman = $this->spearmanCorrelation(
                $humanScores,
                $systemScores
            );

            $results[$strategyName] = [
                'mae' => $mae,
                'rmse' => $rmse,
                'pearson' => $pearson,
                'spearman' => $spearman,
            ];
        }

        fwrite(
            STDOUT,
            PHP_EOL .
            '============================================================' .
            PHP_EOL .
            '              HYBRID STRATEGY COMPARISON' .
            PHP_EOL .
            '============================================================' .
            PHP_EOL
        );

        foreach ($results as $strategyName => $metrics) {
            fwrite(
                STDOUT,
                PHP_EOL .
                $strategyName .
                PHP_EOL .
                '  MAE:      ' .
                number_format(
                    $metrics['mae'] * 100,
                    2
                ) .
                '%' .
                PHP_EOL .
                '  RMSE:     ' .
                number_format(
                    $metrics['rmse'] * 100,
                    2
                ) .
                '%' .
                PHP_EOL .
                '  Pearson:  ' .
                number_format(
                    $metrics['pearson'],
                    4
                ) .
                PHP_EOL .
                '  Spearman: ' .
                number_format(
                    $metrics['spearman'],
                    4
                ) .
                PHP_EOL .
                '------------------------------------------------------------' .
                PHP_EOL
            );
        }

        /*
         * Verify that the original validation service is still
         * functional. This also keeps this test connected to the
         * existing validation architecture.
         */
        $validation = $validationService->validate(
            $dataset
        );

        $this->assertCount(
            15,
            $validation['examples']
        );

        $this->assertArrayHasKey(
            'Harmonic Mean',
            $results
        );

        $this->assertArrayHasKey(
            'Arithmetic Mean',
            $results
        );

        $this->assertArrayHasKey(
            'Weighted 30/70',
            $results
        );

        $this->assertArrayHasKey(
            'Weighted 40/60',
            $results
        );

        $this->assertArrayHasKey(
            'Weighted 50/50',
            $results
        );

        $this->assertArrayHasKey(
            'Weighted 20/80',
            $results
        );
    }

    protected function pearsonCorrelation(
        array $x,
        array $y
    ): ?float {
        $count = count($x);

        if ($count < 2 || $count !== count($y)) {
            return null;
        }

        $meanX = array_sum($x) / $count;
        $meanY = array_sum($y) / $count;

        $numerator = 0.0;
        $sumSquaredX = 0.0;
        $sumSquaredY = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $differenceX = $x[$i] - $meanX;
            $differenceY = $y[$i] - $meanY;

            $numerator += (
                $differenceX *
                $differenceY
            );

            $sumSquaredX += (
                $differenceX ** 2
            );

            $sumSquaredY += (
                $differenceY ** 2
            );
        }

        $denominator = sqrt(
            $sumSquaredX *
            $sumSquaredY
        );

        if ($denominator == 0.0) {
            return null;
        }

        return round(
            $numerator / $denominator,
            4
        );
    }

    protected function spearmanCorrelation(
        array $x,
        array $y
    ): ?float {
        if (count($x) < 2 || count($x) !== count($y)) {
            return null;
        }

        $rankedX = $this->rankValues($x);
        $rankedY = $this->rankValues($y);

        return $this->pearsonCorrelation(
            $rankedX,
            $rankedY
        );
    }

    protected function rankValues(
        array $values
    ): array {
        $indexed = [];

        foreach ($values as $index => $value) {
            $indexed[$index] = $value;
        }

        asort(
            $indexed,
            SORT_NUMERIC
        );

        $ranks = [];
        $position = 1;

        foreach ($indexed as $index => $value) {
            $ranks[$index] = $position;
            $position++;
        }

        ksort($ranks);

        return array_values($ranks);
    }
}