<?php

namespace Tests\Feature;

use App\Services\Ocr\MetadataExtractionService;
use Tests\TestCase;

class MetadataExtractionServiceTest extends TestCase
{
    public function test_it_extracts_metadata_from_actual_ocr_text(): void
    {
        $text = <<<'OCR'
ABSTRACT
BRENN ALDWIN SANTIAGO., PRINCESS LEA SERANTES., AND ALDEN CRAIG
CATALUNA., An Online Management Information System of Capstone Project with
| Decision Support for Cavite State University - Imus Campus. Undergraduate Thesis Bachelor
of Science in Computer Science, Computer Studies Department, Cavite State University — Imus
Campus Imus, Cavite. June 2022. Adviser: Mrs. Grace S. Ibanez.

The Cavite State University - imus Campus was founded on August 15, 2003.

The researchers hope to create an online management information system of capstone
project with decision support for Cavite state university - Imus campus.
OCR;

        $service = app(MetadataExtractionService::class);
        $result = $service->extract($text);

        $this->assertSame(
            'An Online Management Information System of Capstone Project with Decision Support for Cavite State University - Imus Campus',
            $result['title']
        );

        $this->assertSame([
            'BRENN ALDWIN SANTIAGO',
            'PRINCESS LEA SERANTES',
            'ALDEN CRAIG CATALUNA',
        ], $result['authors']);

        $this->assertSame(
            'Mrs. Grace S. Ibanez',
            $result['adviser']
        );

        $this->assertSame(
            'Bachelor of Science in Computer Science',
            $result['program']
        );

        $this->assertSame(
            'Computer Studies Department',
            $result['department']
        );

        $this->assertSame(
            'Cavite State University - Imus Campus',
            $result['institution']
        );

        $this->assertSame(
            'June 2022',
            $result['publication_date']
        );

        $this->assertStringContainsString(
            'The researchers hope to create an online management information system',
            $result['abstract']
        );
    }

    public function test_it_returns_empty_metadata_for_empty_text(): void
    {
        $service = app(MetadataExtractionService::class);

        $result = $service->extract('');

        $this->assertNull($result['title']);
        $this->assertSame([], $result['authors']);
        $this->assertNull($result['adviser']);
        $this->assertNull($result['program']);
        $this->assertNull($result['department']);
        $this->assertNull($result['institution']);
        $this->assertNull($result['publication_date']);
        $this->assertNull($result['abstract']);
    }
}
