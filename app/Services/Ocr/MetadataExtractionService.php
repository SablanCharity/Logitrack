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

        $metadata['title'] = $this->extractTitle($text);
        $metadata['authors'] = $this->extractAuthors($text);
        $metadata['adviser'] = $this->extractAdviser($text);
        $metadata['program'] = $this->extractProgram($text);
        $metadata['department'] = $this->extractDepartment($text);
        $metadata['institution'] = $this->extractInstitution($text);
        $metadata['publication_date'] = $this->extractPublicationDate($text);
        $metadata['abstract'] = $this->extractAbstract($text);

        return $metadata;
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text) ?? $text;

        $lines = explode("\n", $text);
        $lines = array_map(function (string $line): string {
            $line = preg_replace('/[ \t]+/', ' ', trim($line)) ?? trim($line);
            if (preg_match('/^(?:page\s*)?\d{1,3}$/i', $line)) {
                return '';
            }
            return $line;
        }, $lines);

        return trim(implode("\n", $lines));
    }

    private function extractTitle(string $text): ?string
    {
        $lines = array_values(array_filter(
            array_map(fn ($line) => trim($line), preg_split('/\R/u', $text) ?: []),
            fn ($line) => $line !== ''
        ));

        foreach ($lines as $index => $line) {
            if (preg_match('/^(?:capstone\s+project\s+title|project\s+title|title)\s*[:\-]\s*(.+)$/iu', $line, $matches)) {
                return $this->cleanField($matches[1]);
            }
        }

        $flatText = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
        $flatText = str_replace('|', '', $flatText);

        if (preg_match('/(?:[A-ZÑ\s\.,]+\.,\s*)+(?:AND\s+[A-ZÑ\s\.,]+\.,\s*)?(.+?)(?=\.\s*Undergraduate\b)/iu', $flatText, $matches)) {
            $candidate = $this->cleanField($matches[1]);
            if ($candidate !== null && mb_strlen($candidate) >= 5) {
                return $candidate;
            }
        }

        foreach (array_slice($lines, 0, 15) as $line) {
            if (
                !$this->isMetadataLabel($line)
                && !$this->isInstitutionLine($line)
                && !$this->isProgramLine($line)
                && !$this->isDateLine($line)
                && !$this->isAuthorLabel($line)
                && !preg_match('/^[A-ZÑ\s.,\'-]+$/u', $line)
                && $this->isPlausibleTitle($line)
            ) {
                return $this->cleanField($line);
            }
        }

        return null;
    }

    private function extractAuthors(string $text): array
    {
        $labels = '(?:authors?|researchers?|proponents?|prepared\s+by|submitted\s+by)';

        if (preg_match(
            '/(?:^|\n)\s*' . $labels . '\s*[:\-]\s*(.+?)(?=\n\s*(?:adviser|advisor|abstract|program|course|department|institution|school|university|college|date|publication\s+date|title)\b|\n\s*$|\z)/is',
            $text,
            $matches
        )) {
            $authors = $this->splitAuthors($matches[1]);
            if ($authors !== []) {
                return $authors;
            }
        }

        $flatText = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
        $flatText = str_replace('|', '', $flatText);
        $flatText = preg_replace('/^(?:[vVIVX]+\s*)?ABSTRACT\b\s*/i', '', $flatText);

        if (preg_match(
            '/^([A-ZÑ\s,\.]+(?:,\s*[A-ZÑ\s,\.]+\.?)+?)\.\s+[A-ZÑ]/u',
            trim($flatText),
            $matches
        )) {
            $authors = $this->splitAuthors($matches[1]);
            if ($authors !== []) {
                return $authors;
            }
        }

        return [];
    }

    private function splitAuthors(string $value): array
    {
        $value = trim($value);
        $value = preg_replace('/\s+(?:and|&)\s+/i', '|', $value) ?? $value;
        $value = str_replace([";", "\n"], '|', $value);

        $parts = preg_split('/\s*\|\s*/', $value) ?: [];
        $authors = [];

        foreach ($parts as $part) {
            $part = $this->cleanField($part);
            if ($part === null) {
                continue;
            }

            if (str_contains($part, ',')) {
                $commaParts = array_values(array_filter(
                    array_map('trim', explode(',', $part)),
                    fn (string $item): bool => $item !== ''
                ));

                if (count($commaParts) >= 2 && count($commaParts) <= 8) {
                    foreach ($commaParts as $namePart) {
                        if ($this->looksLikePersonName($namePart)) {
                            $authors[] = $namePart;
                        }
                    }
                    continue;
                }
            }

            if ($this->looksLikePersonName($part)) {
                $authors[] = $part;
            }
        }

        $authors = array_map(
            fn (string $name): string => $this->cleanField($name) ?? '',
            $authors
        );

        return array_values(array_unique(array_filter($authors)));
    }

    private function looksLikePersonName(string $name): bool
    {
        $name = trim($name);
        if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
            return false;
        }

        if (
            preg_match('/\d/', $name)
            || preg_match('/\b(?:bachelor|science|university|college|department|campus|abstract|thesis|project|online|management)\b/i', $name)
            || $this->isMetadataLabel($name)
        ) {
            return false;
        }

        return preg_match('/^[\p{L}][\p{L}\s.\'-]*$/u', $name) === 1;
    }

    private function extractAdviser(string $text): ?string
    {
        return $this->extractLabeledValue(
            $text,
            ['project adviser', 'project advisor', 'research adviser', 'research advisor', 'adviser', 'advisor', 'thesis adviser', 'thesis advisor']
        );
    }

    private function extractProgram(string $text): ?string
    {
        $programPatterns = [
            '/\b(Bachelor\s+of\s+Science\s+in\s+Computer\s+Science)\b/i',
            '/\b(Bachelor\s+of\s+Science\s+in\s+Information\s+Technology)\b/i',
            '/\b(Bachelor\s+of\s+Science\s+in\s+Hospitality\s+Management)\b/i',
        ];

        foreach ($programPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->cleanField($matches[1]);
            }
        }

        if (preg_match('/\b(BSCS|BSIT|BSHM)\b/i', $text, $matches)) {
            return strtoupper($matches[1]);
        }

        return $this->extractLabeledValue($text, ['degree program', 'program', 'course']);
    }

    private function extractDepartment(string $text): ?string
    {
        $value = $this->extractLabeledValue($text, ['academic department', 'department']);
        if ($value !== null) {
            return $value;
        }

        if (preg_match('/\b(Department\s+of\s+[\p{L}\s&-]{3,100}?)(?=\s+(?:of\s+Cavite|Cavite\s+State|Undergraduate|Bachelor)|[.,\n]|\z)/iu', $text, $matches)) {
            return $this->cleanField($matches[1]);
        }

        if (preg_match('/\b(Computer\s+Studies\s+Department)\b/i', $text, $matches)) {
            return $this->cleanField($matches[1]);
        }

        return null;
    }

    private function extractInstitution(string $text): ?string
    {
        $patterns = [
            '/\bCavite\s+State\s+University\s*[-–—,]?\s*(?:Imus\s+Campus|Imus\s+City(?:\s*,?\s*Cavite)?|Campus\s+of\s+Imus)\b/i',
            '/\bCavite\s+State\s+University\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->cleanField($matches[0]);
            }
        }

        return 'Cavite State University – Imus Campus';
    }

    private function extractPublicationDate(string $text): ?string
    {
        $months = 'January|February|March|April|May|June|July|August|September|October|November|December';

        if (preg_match('/\b(' . $months . ')\s+((?:19|20)\d{2})\b/i', $text, $matches)) {
            return ucfirst(strtolower($matches[1])) . ' ' . $matches[2];
        }

        if (preg_match('/\b((?:19|20)\d{2})\b/', $text, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractAbstract(string $text): ?string
    {
        if (preg_match(
            '/(?:^|\n)\s*ABSTRACT\s*[:\-]?\s*\n?(.*?)(?=\n\s*(?:keywords?|key\s+words|introduction|chapter\s+1|table\s+of\s+contents|acknowledg(?:e)?ments?|references|bibliography|chapter\s+\d+)\b|\z)/is',
            $text,
            $matches
        )) {
            $abstract = $this->cleanField($matches[1]);
            if ($abstract !== null && mb_strlen($abstract) >= 20) {
                return $abstract;
            }
        }

        return null;
    }

    private function extractLabeledValue(string $text, array $labels): ?string
    {
        $labelsPattern = implode('|', array_map(fn (string $label): string => preg_quote($label, '/'), $labels));
        $pattern = '/(?:^|\n)\s*(?:' . $labelsPattern . ')\s*[:\-]\s*([^\n]+)/i';

        if (preg_match($pattern, $text, $matches)) {
            return $this->cleanField($matches[1]);
        }

        return null;
    }

    private function cleanField(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
        $value = preg_replace('/^[A-ZÑ\s\.,\-\']+\.,\s*/u', '', $value);
        $value = trim($value, " \t\n\r\0\x0B:;,-–—|.");

        return $value === '' ? null : $value;
    }

    private function isPlausibleTitle(string $line): bool
    {
        return mb_strlen($line) >= 12
            && preg_match('/[\p{L}]{3}/u', $line) === 1
            && !preg_match('/^(?:by|prepared by|submitted by|in partial fulfillment|a thesis|a capstone project)\b/i', $line);
    }

    private function isMetadataLabel(string $line): bool
    {
        return preg_match('/^(?:title|project\s+title|capstone\s+project\s+title|authors?|researchers?|proponents?|prepared\s+by|submitted\s+by|adviser|advisor|abstract|program|degree\s+program|course|department|institution|school|university|college|publication\s+date|date|keywords?)\b/i', trim($line)) === 1;
    }

    private function isAuthorLabel(string $line): bool
    {
        return preg_match('/^(?:authors?|researchers?|proponents?|prepared\s+by|submitted\s+by)\b/i', trim($line)) === 1;
    }

    private function isInstitutionLine(string $line): bool
    {
        return preg_match('/\b(university|college|institution|campus)\b/i', $line) === 1;
    }

    private function isProgramLine(string $line): bool
    {
        return preg_match('/\b(BSCS|BSIT|BSHM|Bachelor\s+of\s+Science)\b/i', $line) === 1;
    }

    private function isDateLine(string $line): bool
    {
        return preg_match('/\b(?:19|20)\d{2}\b/', $line) === 1;
    }
}