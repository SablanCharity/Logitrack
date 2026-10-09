<?php

namespace App\Services;

class TextPreprocessor
{
    /**
     * Common English stop words.
     *
     * These words are removed because they usually carry
     * little value when comparing the content of studies.
     */
    protected array $stopWords = [
        'a',
        'an',
        'and',
        'are',
        'as',
        'at',
        'be',
        'by',
        'for',
        'from',
        'has',
        'have',
        'he',
        'her',
        'his',
        'i',
        'if',
        'in',
        'into',
        'is',
        'it',
        'its',
        'me',
        'my',
        'of',
        'on',
        'or',
        'our',
        'ours',
        'she',
        'that',
        'the',
        'their',
        'theirs',
        'them',
        'then',
        'there',
        'these',
        'they',
        'this',
        'those',
        'to',
        'was',
        'we',
        'were',
        'what',
        'when',
        'where',
        'which',
        'who',
        'will',
        'with',
        'you',
        'your',
        'yours',
    ];

    /**
     * Normalize text.
     *
     * - Converts text to lowercase
     * - Removes HTML
     * - Removes punctuation
     * - Normalizes whitespace
     */
    public function normalize(string $text): string
    {
        // Remove HTML tags.
        $text = strip_tags($text);

        // Convert to lowercase.
        $text = mb_strtolower($text, 'UTF-8');

        // Replace non-letter/non-number characters with spaces.
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text) ?? '';

        // Normalize multiple spaces.
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }

    /**
     * Tokenize text into individual words.
     */
    public function tokenize(string $text): array
    {
        $normalized = $this->normalize($text);

        if ($normalized === '') {
            return [];
        }

        return preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * Remove common stop words.
     */
    public function removeStopWords(array $tokens): array
    {
        return array_values(
            array_filter(
                $tokens,
                fn (string $token): bool => !in_array($token, $this->stopWords, true)
            )
        );
    }

    /**
     * Complete preprocessing pipeline.
     *
     * Text
     * ↓
     * Normalize
     * ↓
     * Tokenize
     * ↓
     * Remove stop words
     */
    public function process(string $text): array
    {
        $tokens = $this->tokenize($text);

        return $this->removeStopWords($tokens);
    }

    /**
     * Return the processed text as a single string.
     *
     * This is useful for services that need the cleaned
     * text instead of an array of tokens.
     */
    public function clean(string $text): string
    {
        return implode(' ', $this->process($text));
    }
}
