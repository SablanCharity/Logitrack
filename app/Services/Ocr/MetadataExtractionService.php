<?php

namespace App\Services\Ocr;

class MetadataExtractionService
{
    public function extract(string $text): array
    {
        $text = $this->normalizeText($text);

        $metadata = [
            'title' => null,
            'authors' => [],
            'adviser' => null,
            'program' => null,
            'department' => null,
            'institution' => null,
            'publication_date' => null,
            'abstract' => null,
        ];

        if ($text === '') {
            return $metadata;
        }

        $metadata['authors'] = $this->extractAuthors($text);
        $metadata['title'] = $this->extractTitle($text);
        $metadata['adviser'] = $this->extractAdviser($text);
        $metadata['program'] = $this->extractProgram($text);
        $metadata['department'] = $this->extractDepartment($text);
        $metadata['institution'] = $this->extractInstitution($text);
        $metadata['publication_date'] = $this->extractPublicationDate($text);
        $abstract = $this->extractAbstract($text);
        $metadata['abstract'] = $abstract !== null
            ? $this->cleanAbstract($abstract)
            : null;

        return $metadata;
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(
            ["\r\n", "\r", "\f", "\u{00A0}", '—', '–'],
            ["\n", "\n", "\n", ' ', '-', '-'],
            $text
        );

        // Remove OCR pipe artifacts without joining neighboring words.
        $text = str_replace('|', ' ', $text);

        // Normalize horizontal whitespace but preserve paragraph boundaries.
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/ *\n */', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }

    /**
     * Get the metadata header before the degree and adviser information.
     * Supports different hardbound formats.
     */
    private function extractHeader(string $text): ?string
    {
        $text = preg_replace(
            '/^\s*(?:v\s*)?\bABSTRACT\b\s*/i',
            '',
            $text
        ) ?? $text;

        if (preg_match(
            '/\bUndergraduate\s+Thesis\b|\bUndergraduate\s+Capstone\s+Project\b|\bBachelor\s+of\s+Science\b|\bAdviser\s*:/i',
            $text,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            $text = substr($text, 0, $matches[0][1]);
        }

        $text = preg_split('/\n\s*\n/', trim($text), 2)[0] ?? $text;

        return trim($text) !== '' ? trim($text) : null;
    }

    private function extractAuthorAndTitleBoundary(string $text): ?array
    {
        $header = $this->extractHeader($text);

        if ($header === null) {
            return null;
        }

        $header = trim($header);

        if (preg_match(
            '/\b([A-Z][a-z][A-Za-z0-9-]*(?:\s+[A-Z][a-z][A-Za-z0-9-]*)*)\s*:/',
            $header,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            $titleStart = $matches[0][1];

            return [
                'authors_text' => trim(substr($header, 0, $titleStart)),
                'title' => trim(substr($header, $titleStart)),
                'author_format' => 'surname_first',
            ];
        }

        if (preg_match(
            '/^(.+?[A-Z]{2,}[.,]+)\s+(?=[A-Z][a-z])/s',
            $header,
            $matches
        )) {
            $authorsText = trim($matches[1]);
            $title = trim(substr($header, strlen($matches[1])));

            $title = preg_replace('/^[\s.,;:-]+/', '', $title) ?? $title;
            $title = preg_replace('/\s+/', ' ', $title) ?? $title;

            return [
                'authors_text' => $authorsText,
                'title' => rtrim($title, " \t\n\r\0\x0B.,;"),
                'author_format' => 'given_first',
            ];
        }

        $lines = preg_split('/\n+/', $header) ?: [];

        if (count($lines) >= 2) {
            $firstLine = trim($lines[0]);
            $remaining = trim(implode(' ', array_slice($lines, 1)));

            $hasUppercaseAuthorList =
                preg_match('/^[A-Z][A-Z .,\'-]+$/', $firstLine) === 1;

            $titleStartsWithMixedCase =
                preg_match('/^[A-Z][a-z]/', $remaining) === 1;

            if ($hasUppercaseAuthorList && $titleStartsWithMixedCase) {
                return [
                    'authors_text' => $firstLine,
                    'title' => rtrim($remaining, " \t\n\r\0\x0B.,;"),
                    'author_format' => 'surname_first',
                ];
            }
        }

        return null;
    }

    private function extractAuthors(string $text): array
    {
        $boundary = $this->extractAuthorAndTitleBoundary($text);

        if ($boundary === null) {
            return [];
        }

        $authorsText = trim($boundary['authors_text']);
        $authors = [];

        if (($boundary['author_format'] ?? '') === 'surname_first') {
            // Split between authors, preserving initials such as "T." and "D."
            $parts = preg_split(
                '/\.\s*,\s*(?=[A-Z][A-Z\'-]+,\s*[A-Z])/',
                $authorsText
            ) ?: [];

            foreach ($parts as $part) {
                $part = trim($part, " \t\n\r\0\x0B,;");

                if (! preg_match(
                    '/^([A-Z][A-Z\'-]+),\s*(.+)$/s',
                    $part,
                    $matches
                )) {
                    continue;
                }

                $surname = trim($matches[1]);
                $givenNames = preg_replace(
                    '/\s+/',
                    ' ',
                    trim($matches[2])
                ) ?? trim($matches[2]);

                $name = $surname . ', ' . rtrim($givenNames, " \t\n\r\0\x0B,;.");

                if ($name !== ', ' && ! in_array($name, $authors, true)) {
                    $authors[] = $name;
                }
            }

            if ($authors !== []) {
                return $authors;
            }
        }

        $authorsText = preg_replace(
            '/\s+\bAND\s+/i',
            ', ',
            $authorsText
        ) ?? $authorsText;

        $parts = preg_split('/\.\s*,\s*/', $authorsText) ?: [];

        foreach ($parts as $part) {
            $name = preg_replace('/\s+/', ' ', trim($part)) ?? trim($part);
            $name = preg_replace('/^AND\s+/i', '', $name) ?? $name;
            $name = trim($name, " \t\n\r\0\x0B.,;");

            if ($name !== '' && ! in_array($name, $authors, true)) {
                $authors[] = $name;
            }
        }

        return $authors;
    }

    private function extractTitle(string $text): ?string
    {
        $boundary = $this->extractAuthorAndTitleBoundary($text);

        if ($boundary === null || trim($boundary['title']) === '') {
            return null;
        }

        $title = preg_replace(
            '/\s+/',
            ' ',
            trim($boundary['title'])
        ) ?? trim($boundary['title']);

        return rtrim($title, " \t\n\r\0\x0B.,;");
    }


    private function extractAdviser(string $text): ?string
    {
        /*
        * The adviser value ends at the blank line separating
        * the metadata block from the abstract body.
        *
        * This supports names with initials and wrapped lines.
        */
        if (! preg_match(
            '/\bAdviser\s*:?\s*(.*?)(?=\n\s*\n|$)/is',
            $text,
            $matches
        )) {
            return null;
        }

        $adviser = preg_replace(
            '/\s+/',
            ' ',
            trim($matches[1])
        ) ?? trim($matches[1]);

        // Remove trailing punctuation without removing periods in initials.
        $adviser = rtrim($adviser, " \t\n\r\0\x0B,;:");

        // Reject an empty value or a value that is only punctuation.
        if ($adviser === '' || preg_match('/^[[:punct:]]+$/', $adviser)) {
            return null;
        }

        // Remove the final period after the complete name, if present.
        $adviser = preg_replace('/\.$/', '', $adviser) ?? $adviser;

        return trim($adviser) !== '' ? trim($adviser) : null;
    }


    private function extractProgram(string $text): ?string
    {
        $patterns = [
            '/Bachelor\s+of\s+Science\s+in\s+Computer\s+Science/i'
                => 'Bachelor of Science in Computer Science',
            '/Bachelor\s+of\s+Science\s+in\s+Information\s+Technology/i'
                => 'Bachelor of Science in Information Technology',
            '/BS\s*CS\b/i'
                => 'BSCS',
            '/BS\s*IT\b/i'
                => 'BSIT',
        ];

        foreach ($patterns as $pattern => $canonicalName) {
            if (preg_match($pattern, $text)) {
                return $canonicalName;
            }
        }

        return null;
    }

    private function extractDepartment(string $text): ?string
    {
        if (preg_match(
            '/\bComputer\s+Studies\s+Department\b/i',
            $text
        )) {
            return 'Computer Studies Department';
        }

        return null;
    }

    private function extractInstitution(string $text): ?string
    {
        if (preg_match(
            '/\bCavite\s+State\s+University\s*-\s*Imus\s+Campus\b/i',
            $text
        )) {
            return 'Cavite State University - Imus Campus';
        }

        return null;
    }

    private function extractPublicationDate(string $text): ?string
    {
        $months = 'January|February|March|April|May|June|July|August|September|October|November|December';

        if (preg_match(
            '/\b(' . $months . ')\s+((?:19|20)\d{2})\b/i',
            $text,
            $matches
        )) {
            return ucfirst(strtolower($matches[1])) . ' ' . $matches[2];
        }

        // Also support dates written numerically, such as 06/2022.
        if (preg_match(
            '/\b(0?[1-9]|1[0-2])[\/-]((?:19|20)\d{2})\b/',
            $text,
            $matches
        )) {
            $monthNames = [
                1 => 'January', 2 => 'February', 3 => 'March',
                4 => 'April', 5 => 'May', 6 => 'June',
                7 => 'July', 8 => 'August', 9 => 'September',
                10 => 'October', 11 => 'November', 12 => 'December',
            ];

            return $monthNames[(int) $matches[1]] . ' ' . $matches[2];
        }

        return null;
    }

    private function cleanAbstract(string $abstract): string
    {
        $abstract = str_replace(
            ["\r\n", "\r", "\u{00A0}"],
            ["\n", "\n", ' '],
            $abstract
        );

        $abstract = preg_replace('/[ \t]+/', ' ', $abstract) ?? $abstract;

        $rawParagraphs = preg_split('/\n\s*\n+/', trim($abstract)) ?: [];
        $paragraphs = [];

        foreach ($rawParagraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                continue;
            }

            $paragraph = preg_replace(
                '/(?<=\p{L})-\s*\n\s*(?=\p{L})/u',
                '',
                $paragraph
            ) ?? $paragraph;

            $paragraph = preg_replace('/\s*\n\s*/', ' ', $paragraph) ?? $paragraph;
            $paragraph = preg_replace('/\s+/', ' ', $paragraph) ?? $paragraph;

            $paragraphs[] = trim($paragraph);
        }

        return implode("\n\n", $paragraphs);
    }

    private function extractAbstract(string $text): ?string
    {
        /*
         * In this document format, the abstract body starts after the
         * adviser metadata line and its following blank line.
         */
        if (! preg_match(
            '/\bAdviser\s*:.*?(?:\n\s*\n)/is',
            $text,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            return null;
        }

        $start = $matches[0][1] + strlen($matches[0][0]);
        $abstract = substr($text, $start);

        // Remove interface text accidentally included in OCR output.
        $abstract = preg_split(
            '/\bApprove\s*(?:&|and)\s*Push\s+to\s+Repository\b/i',
            $abstract
        )[0] ?? $abstract;

        $abstract = trim($abstract);

        return $abstract !== '' ? $abstract : null;
    }
}