<?php

namespace App\Services\Ocr;

class MetadataExtractionService
{
    public function extract(string $text): array
    {
        // Normalize line endings and remove unwanted OCR spacing.
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = trim($text);

        // Remove common OCR artifacts before the document header.
        $text = preg_replace(
            '/^(?:[vV]\s*\n\s*)?ABSTRACT\b\s*/i',
            '',
            $text
        ) ?? $text;

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

        // Extract the adviser from the same line as the label.
        if (preg_match(
            '/\bAdviser\s*:\s*([^\n]*)/i',
            $text,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            $adviserLine = trim($matches[1][0]);

            $metadata['adviser'] = trim(
                $adviserLine,
                " \t\n\r\0\x0B.,;"
            );

            // Everything after the adviser line is the abstract candidate.
            $endOfHeader = $matches[0][1]
                + strlen($matches[0][0]);

            $abstractText = trim(substr($text, $endOfHeader));

            if ($abstractText !== '') {
                $metadata['abstract'] = $abstractText;
            }
        }

        // Extract degree program.
        if (preg_match(
            '/Bachelor\s+of\s+Science\s+in\s+' .
            '(Computer\s+Science|Information\s+Technology)/i',
            $text,
            $matches
        )) {
            $metadata['program'] = 'Bachelor of Science in '
                . preg_replace('/\s+/', ' ', trim($matches[1]));
        }

        // Extract department.
        if (preg_match(
            '/Computer Studies Department/i',
            $text,
            $matches
        )) {
            $metadata['department'] = trim($matches[0]);
        }

        // Extract institution.
        if (preg_match(
            '/Cavite State University\s*[-–—]\s*Imus Campus/i',
            $text,
            $matches
        )) {
            $metadata['institution'] = preg_replace(
                '/\s+/',
                ' ',
                trim($matches[0])
            );
        }

        // Extract month and year.
        if (preg_match(
            '/\b(January|February|March|April|May|June|July|' .
            'August|September|October|November|December)\s+\d{4}\b/i',
            $text,
            $matches
        )) {
            $metadata['publication_date'] = trim($matches[0]);
        }

        /*
         * Extract authors and title from the bibliographic header.
         * This pattern assumes author names end before the title,
         * followed by ". Undergraduate Thesis".
         */
        if (preg_match(
            '/^(.*)\.\s*,\s*(.+?)\.\s*Undergraduate Thesis\b/is',
            $text,
            $matches
        )) {
            $authorText = trim($matches[1]);
            $titleText = trim($matches[2]);

            // Split names separated by the OCR pattern ".,"
            $authors = preg_split(
                '/\s*\.\s*,\s*/',
                $authorText
            ) ?: [];

            $metadata['authors'] = array_values(array_filter(
                array_map(function ($author) {
                    $author = trim($author, " \t\n\r\0\x0B,.");

                    // Remove the conjunction before the final author.
                    $author = preg_replace(
                        '/^AND\s+/i',
                        '',
                        $author
                    ) ?? $author;

                    // Collapse line breaks within a person's name.
                    return trim(preg_replace('/\s+/', ' ', $author));
                }, $authors),
                fn ($author) => $author !== ''
            ));

            // Remove stray OCR symbols and collapse wrapped title lines.
            $titleText = str_replace('|', '', $titleText);

            $titleText = preg_replace('/\s+/', ' ', $titleText)
                ?? $titleText;

            $metadata['title'] = trim(
                preg_replace('/\s+/', ' ', $titleText),
                " \t\n\r\0\x0B,."
            );
        }

        return $metadata;
    }
}
