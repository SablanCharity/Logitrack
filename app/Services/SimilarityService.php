<?php

namespace App\Services;

class SimilarityService
{
    public function __construct(
        protected TextPreprocessor $preprocessor,
        protected TfIdfService $tfIdf,
        protected EmbeddingService $embedding,
    ) {
    }

    /**
     * Compare two pieces of study text.
     *
     * The comparison produces:
     *
     * - lexical similarity
     * - semantic similarity
     * - hybrid similarity
     * - shared terms
     * - DSS guidance
     *
     * @return array<string, mixed>
     */
    public function compare(
        string $proposedText,
        string $existingText
    ): array {
        /*
         * ---------------------------------------------------------
         * 1. PREPROCESSING
         * ---------------------------------------------------------
         */

        $proposedTokens = $this->preprocessor->process(
            $proposedText
        );

        $existingTokens = $this->preprocessor->process(
            $existingText
        );

        /*
         * ---------------------------------------------------------
         * 2. LEXICAL SIMILARITY
         * ---------------------------------------------------------
         */

        $lexicalSimilarity = $this->tfIdf->compare(
            $proposedTokens,
            $existingTokens
        );

        /*
         * ---------------------------------------------------------
         * 3. SEMANTIC SIMILARITY
         * ---------------------------------------------------------
         *
         * Embeddings are optional.
         *
         * If Ollama/API is unavailable, semantic similarity
         * becomes null instead of breaking the checker.
         */

        $semanticSimilarity = null;

        $proposedEmbedding = $this->embedding->embed(
            $proposedText
        );

        $existingEmbedding = $this->embedding->embed(
            $existingText
        );

        if (
            $proposedEmbedding !== null &&
            $existingEmbedding !== null
        ) {
            $semanticSimilarity =
                $this->embedding->cosineSimilarity(
                    $proposedEmbedding,
                    $existingEmbedding
                );
        }

        /*
         * ---------------------------------------------------------
         * 4. HYBRID SIMILARITY
         * ---------------------------------------------------------
         */

        $hybridSimilarity = $this->calculateHybridSimilarity(
            $lexicalSimilarity,
            $semanticSimilarity
        );

        /*
         * ---------------------------------------------------------
         * 5. SHARED TERMS
         * ---------------------------------------------------------
         */

        $sharedTerms = $this->tfIdf->sharedTerms(
            $proposedTokens,
            $existingTokens,
            (int) config(
                'similarity.shared_terms_limit',
                10
            )
        );

        /*
         * ---------------------------------------------------------
         * 6. DSS GUIDANCE
         * ---------------------------------------------------------
         */

        $dss = $this->generateDssGuidance(
            $hybridSimilarity,
            $lexicalSimilarity,
            $semanticSimilarity
        );

        return [
            'lexical_similarity' => $lexicalSimilarity,
            'lexical_percentage' =>
                $this->tfIdf->toPercentage(
                    $lexicalSimilarity
                ),

            'semantic_similarity' => $semanticSimilarity,
            'semantic_percentage' =>
                $semanticSimilarity !== null
                    ? round($semanticSimilarity * 100, 2)
                    : null,

            'hybrid_similarity' => $hybridSimilarity,
            'hybrid_percentage' =>
                round($hybridSimilarity * 100, 2),

            'shared_terms' => $sharedTerms,

            'dss' => $dss,

            'semantic_available' =>
                $semanticSimilarity !== null,
        ];
    }

    /**
     * Calculate the hybrid similarity score.
     *
     * When both lexical and semantic scores exist,
     * the configured hybrid method is used.
     *
     * The default is harmonic mean.
     *
     * If semantic similarity is unavailable, the system
     * safely falls back to lexical similarity.
     */
    protected function calculateHybridSimilarity(
        float $lexicalSimilarity,
        ?float $semanticSimilarity
    ): float {
        /*
         * Semantic embeddings are unavailable.
         *
         * Use TF-IDF rather than inventing a semantic score.
         */
        if ($semanticSimilarity === null) {
            return $lexicalSimilarity;
        }

        $method = config(
            'similarity.hybrid.method',
            'harmonic_mean'
        );

        /*
         * ---------------------------------------------------------
         * Harmonic Mean
         * ---------------------------------------------------------
         *
         * H = 2ab / (a + b)
         *
         * This prevents one score from completely compensating
         * for a very low score in the other component.
         */
        if ($method === 'harmonic_mean') {
            $sum =
                $lexicalSimilarity +
                $semanticSimilarity;

            if ($sum <= 0) {
                return 0.0;
            }

            return $this->clamp(
                (2 * $lexicalSimilarity * $semanticSimilarity)
                / $sum
            );
        }

        /*
         * ---------------------------------------------------------
         * Arithmetic Mean
         * ---------------------------------------------------------
         *
         * This option is available for future experiments.
         */
        if ($method === 'arithmetic_mean') {
            return $this->clamp(
                (
                    $lexicalSimilarity +
                    $semanticSimilarity
                ) / 2
            );
        }

        /*
         * ---------------------------------------------------------
         * Weighted Mean
         * ---------------------------------------------------------
         *
         * We only use this if BOTH weights have explicitly
         * been configured.
         *
         * This prevents the system from pretending that a
         * 40/60 or 50/50 weighting has scientific validation.
         */
        if ($method === 'weighted_mean') {
            $lexicalWeight = config(
                'similarity.hybrid.lexical_weight'
            );

            $semanticWeight = config(
                'similarity.hybrid.semantic_weight'
            );

            if (
                is_numeric($lexicalWeight) &&
                is_numeric($semanticWeight)
            ) {
                $lexicalWeight = (float) $lexicalWeight;
                $semanticWeight = (float) $semanticWeight;

                $totalWeight =
                    $lexicalWeight +
                    $semanticWeight;

                if ($totalWeight > 0) {
                    return $this->clamp(
                        (
                            ($lexicalSimilarity * $lexicalWeight) +
                            ($semanticSimilarity * $semanticWeight)
                        ) / $totalWeight
                    );
                }
            }
        }

        /*
         * Unknown configuration:
         *
         * Safely fall back to harmonic mean.
         */
        $sum =
            $lexicalSimilarity +
            $semanticSimilarity;

        if ($sum <= 0) {
            return 0.0;
        }

        return $this->clamp(
            (2 * $lexicalSimilarity * $semanticSimilarity)
            / $sum
        );
    }

    /**
     * Generate Decision Support System guidance.
     *
     * The DSS does NOT automatically reject a proposed study.
     *
     * Instead, it identifies areas the student should review.
     *
     * @return array<string, mixed>
     */
    protected function generateDssGuidance(
        float $hybridSimilarity,
        float $lexicalSimilarity,
        ?float $semanticSimilarity
    ): array {
        $percentage = $hybridSimilarity * 100;

        /*
         * ---------------------------------------------------------
         * HIGH SIMILARITY
         * ---------------------------------------------------------
         */

        if ($percentage >= 70) {
            return [
                'level' => 'high',
                'title' => 'High similarity detected.',
                'message' =>
                    'The proposed study contains substantial '
                    . 'similarity with the existing study. '
                    . 'Review the differences carefully before '
                    . 'finalizing the proposed project.',
                'areas_to_review' => [
                    'Research objectives',
                    'Target users',
                    'System scope',
                    'Major features',
                    'Problem statement',
                ],
                'recommendation' =>
                    'Determine whether the proposed study '
                    . 'provides a sufficiently different purpose, '
                    . 'scope, target context, or set of features.',
            ];
        }

        /*
         * ---------------------------------------------------------
         * MODERATE SIMILARITY
         * ---------------------------------------------------------
         */

        if ($percentage >= 40) {
            return [
                'level' => 'moderate',
                'title' => 'Moderate similarity detected.',
                'message' =>
                    'The existing study shares related concepts '
                    . 'with the proposed study, but differences '
                    . 'may exist in the target users, scope, '
                    . 'objectives, or implementation.',
                'areas_to_review' => [
                    'Research objectives',
                    'Target users',
                    'System scope',
                    'Major features',
                ],
                'recommendation' =>
                    'Review the identified similarities and '
                    . 'clearly establish how the proposed study '
                    . 'differs from the existing work.',
            ];
        }

        /*
         * ---------------------------------------------------------
         * LOW SIMILARITY
         * ---------------------------------------------------------
         */

        return [
            'level' => 'low',
            'title' => 'Low similarity detected.',
            'message' =>
                'The proposed study has relatively low '
                . 'similarity with the existing study.',
            'areas_to_review' => [],
            'recommendation' =>
                'The proposed study appears to have a '
                . 'different focus, but normal research review '
                . 'should still be performed.',
        ];
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

    /**
     * Compare a proposed study against multiple existing studies.
     *
     * Each existing study should contain:
     *
     * [
     *     'id' => ...,
     *     'title' => ...,
     *     'abstract' => ...
     * ]
     *
     * @param array<int, array<string, mixed>> $existingStudies
     * @return array<int, array<string, mixed>>
     */
    public function rank(
        string $proposedText,
        array $existingStudies
    ): array {
        $results = [];

        foreach ($existingStudies as $study) {
            $existingText = trim(
                ($study['title'] ?? '') .
                ' ' .
                ($study['abstract'] ?? '')
            );

            if ($existingText === '') {
                continue;
            }

            $comparison = $this->compare(
                $proposedText,
                $existingText
            );

            $results[] = array_merge(
                $study,
                [
                    'similarity' => $comparison,
                ]
            );
        }

        /*
         * Sort from highest overall similarity
         * to lowest similarity.
         */
        usort(
            $results,
            function (array $a, array $b): int {
                return
                    ($b['similarity']['hybrid_similarity'] ?? 0)
                    <=>
                    ($a['similarity']['hybrid_similarity'] ?? 0);
            }
        );

        /*
         * Limit the number of results displayed.
         */
        $limit = (int) config(
            'similarity.results_limit',
            10
        );

        return array_slice(
            $results,
            0,
            $limit
        );
    }
}
