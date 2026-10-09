<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Similarity Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the Similarity Checker.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Embedding Driver
    |--------------------------------------------------------------------------
    |
    | Available:
    |
    | - ollama
    | - openai
    |
    */

    'embedding_driver' => env(
        'SIMILARITY_EMBEDDING_DRIVER',
        'ollama'
    ),

    /*
    |--------------------------------------------------------------------------
    | Embedding API Base URL
    |--------------------------------------------------------------------------
    |
    | Ollama:
    | http://127.0.0.1:11434
    |
    | OpenAI:
    | https://api.openai.com/v1
    |
    */

    'embedding_base_url' => env(
        'SIMILARITY_EMBEDDING_BASE_URL',
        'http://127.0.0.1:11434'
    ),

    /*
    |--------------------------------------------------------------------------
    | Embedding Model
    |--------------------------------------------------------------------------
    |
    | Default Ollama model:
    | nomic-embed-text
    |
    */

    'embedding_model' => env(
        'SIMILARITY_EMBEDDING_MODEL',
        'nomic-embed-text'
    ),

    /*
    |--------------------------------------------------------------------------
    | Embedding API Key
    |--------------------------------------------------------------------------
    |
    | Only required when using an OpenAI-compatible provider
    | that requires authentication.
    |
    */

    'embedding_api_key' => env(
        'SIMILARITY_EMBEDDING_API_KEY'
    ),

    /*
    |--------------------------------------------------------------------------
    | Embedding Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum number of seconds allowed for an embedding request.
    |
    */

    'embedding_timeout' => (int) env(
        'SIMILARITY_EMBEDDING_TIMEOUT',
        20
    ),

    /*
    |--------------------------------------------------------------------------
    | Hybrid Similarity
    |--------------------------------------------------------------------------
    |
    | The system should eventually determine the best combination
    | of lexical and semantic similarity using human-labelled
    | validation data.
    |
    | We therefore do NOT claim that an arbitrary fixed weighting
    | such as 40/60 is scientifically validated.
    |
    */

    'hybrid' => [

        /*
         * Current baseline method.
         *
         * Harmonic mean gives both components importance and
         * avoids allowing one very high score to completely
         * compensate for a very low score.
         */
        'method' => env(
            'SIMILARITY_HYBRID_METHOD',
            'harmonic_mean'
        ),

        /*
         * Optional weights.
         *
         * These are intentionally null by default because the
         * appropriate weights should eventually be determined
         * through validation against human-labelled examples.
         */
        'lexical_weight' => env(
            'SIMILARITY_LEXICAL_WEIGHT'
        ),

        'semantic_weight' => env(
            'SIMILARITY_SEMANTIC_WEIGHT'
        ),

    ],

    /*
    |--------------------------------------------------------------------------
    | Repository Study Status
    |--------------------------------------------------------------------------
    |
    | Only studies with this status should be considered by the
    | Similarity Checker.
    |
    */

    'repository_status' => env(
        'SIMILARITY_REPOSITORY_STATUS',
        'admin_approved'
    ),

    /*
    |--------------------------------------------------------------------------
    | Shared Terms
    |--------------------------------------------------------------------------
    |
    | Maximum number of shared terms displayed on the results page.
    |
    */

    'shared_terms_limit' => (int) env(
        'SIMILARITY_SHARED_TERMS_LIMIT',
        10
    ),

    /*
    |--------------------------------------------------------------------------
    | Results Limit
    |--------------------------------------------------------------------------
    |
    | Maximum number of similar studies displayed to the user.
    |
    */

    'results_limit' => (int) env(
        'SIMILARITY_RESULTS_LIMIT',
        10
    ),

    /*
    |--------------------------------------------------------------------------
    | Minimum Text Length
    |--------------------------------------------------------------------------
    |
    | Minimum amount of text required before attempting similarity
    | analysis.
    |
    */

    'minimum_text_length' => (int) env(
        'SIMILARITY_MINIMUM_TEXT_LENGTH',
        20
    ),

];
