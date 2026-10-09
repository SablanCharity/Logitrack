<?php

namespace App\Services;

class TfIdfService
{
    /**
     * Calculate TF-IDF vectors for a collection of documents.
     *
     * @param array<int, array<int, string>> $documents
     * @return array<int, array<string, float>>
     */
    public function calculateVectors(array $documents): array
    {
        if (empty($documents)) {
            return [];
        }

        $documentCount = count($documents);

        /*
         * Build document frequency.
         *
         * DF = number of documents containing the term.
         */
        $documentFrequency = [];

        foreach ($documents as $tokens) {
            $uniqueTokens = array_unique($tokens);

            foreach ($uniqueTokens as $token) {
                if ($token === '') {
                    continue;
                }

                $documentFrequency[$token] =
                    ($documentFrequency[$token] ?? 0) + 1;
            }
        }

        $vectors = [];

        foreach ($documents as $tokens) {
            $termFrequency = $this->calculateTermFrequency($tokens);

            $vector = [];

            foreach ($documentFrequency as $term => $df) {
                $tf = $termFrequency[$term] ?? 0;

                if ($tf === 0) {
                    $vector[$term] = 0.0;
                    continue;
                }

                /*
                 * Smoothed IDF:
                 *
                 * IDF = log((N + 1) / (DF + 1)) + 1
                 *
                 * Smoothing prevents division/log problems and
                 * keeps the values stable for small datasets.
                 */
                $idf = log(
                    ($documentCount + 1) / ($df + 1)
                ) + 1;

                $vector[$term] = $tf * $idf;
            }

            $vectors[] = $vector;
        }

        return $vectors;
    }

    /**
     * Calculate term frequency for one document.
     *
     * TF = occurrences of term / total number of terms.
     *
     * @param array<int, string> $tokens
     * @return array<string, float>
     */
    protected function calculateTermFrequency(array $tokens): array
    {
        if (empty($tokens)) {
            return [];
        }

        $counts = array_count_values($tokens);
        $totalTerms = count($tokens);

        $termFrequency = [];

        foreach ($counts as $term => $count) {
            $termFrequency[$term] = $count / $totalTerms;
        }

        return $termFrequency;
    }

    /**
     * Calculate cosine similarity between two TF-IDF vectors.
     *
     * Returns a value between 0 and 1.
     *
     * 0 = completely different
     * 1 = identical direction
     *
     * @param array<string, float> $vectorA
     * @param array<string, float> $vectorB
     */
    public function cosineSimilarity(
        array $vectorA,
        array $vectorB
    ): float {
        if (empty($vectorA) || empty($vectorB)) {
            return 0.0;
        }

        /*
         * Dot product.
         */
        $dotProduct = 0.0;

        foreach ($vectorA as $term => $valueA) {
            $valueB = $vectorB[$term] ?? 0.0;

            $dotProduct += $valueA * $valueB;
        }

        /*
         * Magnitude of vector A.
         */
        $magnitudeA = 0.0;

        foreach ($vectorA as $value) {
            $magnitudeA += $value ** 2;
        }

        $magnitudeA = sqrt($magnitudeA);

        /*
         * Magnitude of vector B.
         */
        $magnitudeB = 0.0;

        foreach ($vectorB as $value) {
            $magnitudeB += $value ** 2;
        }

        $magnitudeB = sqrt($magnitudeB);

        /*
         * Avoid division by zero.
         */
        if ($magnitudeA == 0.0 || $magnitudeB == 0.0) {
            return 0.0;
        }

        $similarity = $dotProduct / ($magnitudeA * $magnitudeB);

        /*
         * Floating-point calculations can occasionally produce
         * a value slightly outside the expected range.
         */
        return max(0.0, min(1.0, $similarity));
    }

    /**
     * Compare two tokenized documents.
     *
     * Returns the cosine similarity as a value from 0 to 1.
     *
     * @param array<int, string> $tokensA
     * @param array<int, string> $tokensB
     */
    public function compare(
        array $tokensA,
        array $tokensB
    ): float {
        $vectors = $this->calculateVectors([
            $tokensA,
            $tokensB,
        ]);

        if (count($vectors) < 2) {
            return 0.0;
        }

        return $this->cosineSimilarity(
            $vectors[0],
            $vectors[1]
        );
    }

    /**
     * Convert a similarity value from 0-1 into a percentage.
     */
    public function toPercentage(float $similarity): float
    {
        return round(
            max(0.0, min(1.0, $similarity)) * 100,
            2
        );
    }

    /**
     * Find terms shared between two documents.
     *
     * @param array<int, string> $tokensA
     * @param array<int, string> $tokensB
     * @return array<int, string>
     */
    public function sharedTerms(
        array $tokensA,
        array $tokensB,
        int $limit = 10
    ): array {
        $uniqueA = array_unique($tokensA);
        $uniqueB = array_unique($tokensB);

        $shared = array_values(
            array_intersect($uniqueA, $uniqueB)
        );

        /*
         * Sort alphabetically for predictable output.
         */
        sort($shared);

        return array_slice($shared, 0, $limit);
    }
}
