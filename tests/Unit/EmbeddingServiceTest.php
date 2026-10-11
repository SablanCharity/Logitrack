<?php

namespace Tests\Unit;

use App\Services\EmbeddingService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmbeddingServiceTest extends TestCase
{
    public function test_cosine_similarity_of_identical_vectors_is_one(): void
    {
        $service = app(EmbeddingService::class);

        $result = $service->cosineSimilarity(
            [1.0, 0.0, 0.0],
            [1.0, 0.0, 0.0]
        );

        $this->assertEqualsWithDelta(1.0, $result, 0.000001);
    }

    public function test_cosine_similarity_of_orthogonal_vectors_is_zero(): void
    {
        $service = app(EmbeddingService::class);

        $result = $service->cosineSimilarity(
            [1.0, 0.0],
            [0.0, 1.0]
        );

        $this->assertEqualsWithDelta(0.0, $result, 0.000001);
    }

    public function test_cosine_similarity_handles_zero_vectors(): void
    {
        $service = app(EmbeddingService::class);

        $result = $service->cosineSimilarity(
            [0.0, 0.0],
            [1.0, 2.0]
        );

        $this->assertSame(0.0, $result);
    }

    public function test_embed_returns_vector_when_ollama_responds_successfully(): void
    {
        config([
            'similarity.embedding_driver' => 'ollama',
            'similarity.embedding_base_url' => 'http://127.0.0.1:11434',
            'similarity.embedding_model' => 'nomic-embed-text',
            'similarity.embedding_timeout' => 5,
        ]);

        Http::fake([
            '127.0.0.1:11434/api/embed' => Http::response([
                'embeddings' => [[0.1, 0.2, 0.3]],
            ], 200),
        ]);

        $service = app(EmbeddingService::class);

        $result = $service->embed('student attendance system');

        $this->assertSame([0.1, 0.2, 0.3], $result);
    }

    public function test_embed_handles_unavailable_ollama_gracefully(): void
    {
        config([
            'similarity.embedding_driver' => 'ollama',
            'similarity.embedding_base_url' => 'http://127.0.0.1:11434',
            'similarity.embedding_model' => 'nomic-embed-text',
            'similarity.embedding_timeout' => 5,
        ]);

        Http::fake([
            '127.0.0.1:11434/*' => Http::response([], 500),
        ]);

        $service = app(EmbeddingService::class);

        $result = $service->embed('student attendance system');

        $this->assertNull($result);
    }
}