<?php

namespace App\Services;

class SimilarityValidationService
{
    public function __construct(
        protected SimilarityService $similarityService
    ) {
    }

    /**
     * Validate similarity scores against human-labeled examples.
     *
     * Each dataset item should contain:
     *
     * [
     *     'name' => 'Example A',
     *     'proposed' => '...',
     *     'existing' => '...',
     *     'human_score' => 0.0 - 1.0,
     * ]
     */
    public function validate(array $dataset): array
    {
        $results = [];

        foreach ($dataset as $index => $example) {
            $proposed = trim((string) ($example['proposed'] ?? ''));
            $existing = trim((string) ($example['existing'] ?? ''));

            if ($proposed === '' || $existing === '') {
                continue;
            }

            $humanScore = $this->clamp(
                (float) ($example['human_score'] ?? 0)
            );

            $comparison = $this->similarityService->compare(
                $proposed,
                $existing
            );

            $systemScore = $this->clamp(
                (float) ($comparison['hybrid_similarity'] ?? 0)
            );

            $difference = abs($systemScore - $humanScore);

            $results[] = [
                'index' => $index,
                'name' => $example['name'] ?? 'Example ' . ($index + 1),

                'human_score' => round($humanScore, 4),
                'human_percentage' => round($humanScore * 100, 2),

                'system_score' => round($systemScore, 4),
                'system_percentage' => round($systemScore * 100, 2),

                'absolute_error' => round($difference, 4),
                'absolute_error_percentage' => round($difference * 100, 2),

                'lexical_percentage' =>
                    $comparison['lexical_percentage'] ?? null,

                'semantic_percentage' =>
                    $comparison['semantic_percentage'] ?? null,

                'hybrid_percentage' =>
                    $comparison['hybrid_percentage'] ?? null,

                'semantic_available' =>
                    $comparison['semantic_available'] ?? false,
            ];
        }

        return [
            'examples' => $results,
            'metrics' => $this->calculateMetrics($results),
        ];
    }

    /**
     * Calculate validation metrics.
     */
    protected function calculateMetrics(array $results): array
    {
        $count = count($results);

        if ($count === 0) {
            return [
                'sample_count' => 0,
                'mean_absolute_error' => null,
                'mean_absolute_error_percentage' => null,
                'root_mean_squared_error' => null,
                'root_mean_squared_error_percentage' => null,
                'pearson_correlation' => null,
                'spearman_correlation' => null,
            ];
        }

        $humanScores = [];
        $systemScores = [];
        $absoluteErrors = [];
        $squaredErrors = [];

        foreach ($results as $result) {
            $human = (float) $result['human_score'];
            $system = (float) $result['system_score'];

            $error = abs($system - $human);
            $squaredError = ($system - $human) ** 2;

            $humanScores[] = $human;
            $systemScores[] = $system;
            $absoluteErrors[] = $error;
            $squaredErrors[] = $squaredError;
        }

        $mae = array_sum($absoluteErrors) / $count;

        $mse = array_sum($squaredErrors) / $count;
        $rmse = sqrt($mse);

        return [
            'sample_count' => $count,

            'mean_absolute_error' => round($mae, 4),
            'mean_absolute_error_percentage' => round($mae * 100, 2),

            'root_mean_squared_error' => round($rmse, 4),
            'root_mean_squared_error_percentage' => round($rmse * 100, 2),

            'pearson_correlation' => $this->pearsonCorrelation(
                $humanScores,
                $systemScores
            ),

            'spearman_correlation' => $this->spearmanCorrelation(
                $humanScores,
                $systemScores
            ),
        ];
    }

    /**
     * Calculate Pearson correlation.
     */
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

        foreach ($x as $index => $valueX) {
            $valueY = $y[$index];

            $differenceX = $valueX - $meanX;
            $differenceY = $valueY - $meanY;

            $numerator += $differenceX * $differenceY;
            $sumSquaredX += $differenceX ** 2;
            $sumSquaredY += $differenceY ** 2;
        }

        $denominator = sqrt(
            $sumSquaredX * $sumSquaredY
        );

        if ($denominator == 0.0) {
            return null;
        }

        return round(
            max(
                -1.0,
                min(
                    1.0,
                    $numerator / $denominator
                )
            ),
            4
        );
    }

    /**
     * Calculate Spearman rank correlation.
     */
    protected function spearmanCorrelation(
        array $x,
        array $y
    ): ?float {
        $count = count($x);

        if ($count < 2 || $count !== count($y)) {
            return null;
        }

        $rankedX = $this->rankValues($x);
        $rankedY = $this->rankValues($y);

        return $this->pearsonCorrelation(
            $rankedX,
            $rankedY
        );
    }

    /**
     * Rank values using average ranks for ties.
     */
    protected function rankValues(array $values): array
    {
        $indexed = [];

        foreach ($values as $index => $value) {
            $indexed[] = [
                'index' => $index,
                'value' => (float) $value,
            ];
        }

        usort(
            $indexed,
            fn (array $a, array $b): int =>
                $a['value'] <=> $b['value']
        );

        $ranks = array_fill(
            0,
            count($values),
            0.0
        );

        $position = 0;
        $total = count($indexed);

        while ($position < $total) {
            $start = $position;
            $currentValue = $indexed[$position]['value'];

            while (
                $position < $total &&
                $indexed[$position]['value'] == $currentValue
            ) {
                $position++;
            }

            $end = $position - 1;

            $averageRank = (
                ($start + 1) +
                ($end + 1)
            ) / 2;

            for ($i = $start; $i <= $end; $i++) {
                $originalIndex = $indexed[$i]['index'];

                $ranks[$originalIndex] = $averageRank;
            }
        }

        return $ranks;
    }

    /**
     * Clamp a value between 0 and 1.
     */
    protected function clamp(float $value): float
    {
        return max(
            0.0,
            min(1.0, $value)
        );
    }
}