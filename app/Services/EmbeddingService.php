<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class EmbeddingService
{
    /**
     * Generate an embedding for the given text.
     *
     * Supported drivers:
     *
     * - ollama
     * - openai
     *
     * If the embedding service is unavailable, this method
     * returns null instead of crashing the Similarity Checker.
     */
    public function embed(string $text): ?array
    {
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        $driver = config('similarity.embedding_driver', 'ollama');

        try {
            return match ($driver) {
                'ollama' => $this->embedWithOllama($text),
                'openai' => $this->embedWithOpenAi($text),
                default => null,
            };
        } catch (Throwable) {
            /*
             * Semantic similarity should not prevent the
             * TF-IDF portion of the system from working.
             */
            return null;
        }
    }

    /**
     * Generate embeddings for multiple texts.
     *
     * @param array<int, string> $texts
     * @return array<int, array<float>|null>
     */
    public function embedMany(array $texts): array
    {
        $embeddings = [];

        foreach ($texts as $text) {
            $embeddings[] = $this->embed($text);
        }

        return $embeddings;
    }

    /**
     * Generate an embedding using Ollama.
     */
    protected function embedWithOllama(string $text): ?array
    {
        $baseUrl = rtrim(
            config(
                'similarity.embedding_base_url',
                'http://127.0.0.1:11434'
            ),
            '/'
        );

        $model = config(
            'similarity.embedding_model',
            'nomic-embed-text'
        );

        $timeout = (int) config(
            'similarity.embedding_timeout',
            20
        );

        /*
         * Ollama's embedding endpoint.
         */
        $response = Http::timeout($timeout)
            ->acceptJson()
            ->post($baseUrl . '/api/embed', [
                'model' => $model,
                'input' => $text,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        /*
         * Ollama returns:
         *
         * {
         *     "embeddings": [
         *         [0.123, 0.456, ...]
         *     ]
         * }
         */
        $embedding = $data['embeddings'][0] ?? null;

        return $this->validateEmbedding($embedding);
    }

    /**
     * Generate an embedding using an OpenAI-compatible API.
     */
    protected function embedWithOpenAi(string $text): ?array
    {
        $baseUrl = rtrim(
            config(
                'similarity.embedding_base_url',
                'https://api.openai.com/v1'
            ),
            '/'
        );

        $model = config(
            'similarity.embedding_model',
            'text-embedding-3-small'
        );

        $apiKey = config(
            'similarity.embedding_api_key'
        );

        $timeout = (int) config(
            'similarity.embedding_timeout',
            20
        );

        if (empty($apiKey)) {
            return null;
        }

        $response = Http::timeout($timeout)
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl . '/embeddings', [
                'model' => $model,
                'input' => $text,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        /*
         * OpenAI-compatible response:
         *
         * {
         *     "data": [
         *         {
         *             "embedding": [...]
         *         }
         *     ]
         * }
         */
        $embedding = $data['data'][0]['embedding'] ?? null;

        return $this->validateEmbedding($embedding);
    }

    /**
     * Validate and normalize an embedding array.
     *
     * @param mixed $embedding
     * @return array<float>|null
     */
    protected function validateEmbedding(mixed $embedding): ?array
    {
        if (! is_array($embedding) || empty($embedding)) {
            return null;
        }

        $result = [];

        foreach ($embedding as $value) {
            if (! is_numeric($value)) {
                return null;
            }

            $result[] = (float) $value;
        }

        return empty($result) ? null : $result;
    }

    /**
     * Calculate cosine similarity between two embeddings.
     *
     * Returns a value between 0 and 1.
     *
     * @param array<float> $vectorA
     * @param array<float> $vectorB
     */
    public function cosineSimilarity(
        array $vectorA,
        array $vectorB
    ): float {
        if (empty($vectorA) || empty($vectorB)) {
            return 0.0;
        }

        /*
         * Embeddings must have the same dimensions.
         */
        if (count($vectorA) !== count($vectorB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        foreach ($vectorA as $index => $valueA) {
            $valueB = $vectorB[$index];

            $dotProduct += $valueA * $valueB;

            $magnitudeA += $valueA ** 2;
            $magnitudeB += $valueB ** 2;
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0.0 || $magnitudeB == 0.0) {
            return 0.0;
        }

        $similarity = $dotProduct /
            ($magnitudeA * $magnitudeB);

        /*
         * Cosine similarity can theoretically range from -1 to 1.
         *
         * For our similarity score, normalize negative values
         * to zero and keep the final value within 0-1.
         */
        return max(
            0.0,
            min(1.0, $similarity)
        );
    }

    /**
     * Check whether an embedding provider is configured
     * and available.
     */
    public function isAvailable(): bool
    {
        $testText = 'similarity checker test';

        return $this->embed($testText) !== null;
    }
}
