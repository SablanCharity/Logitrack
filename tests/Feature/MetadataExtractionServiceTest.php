<?php

use App\Services\Ocr\MetadataExtractionService;
use PHPUnit\Framework\TestCase;

class MetadataExtractionServiceTest extends TestCase
{
    public function test_extracts_metadata_from_ocr_text_with_the_sample_hardbound_layout(): void
    {
        $service = new MetadataExtractionService();

        $text = <<<'OCR'
ABSTRACT
BRENN ALDWIN SANTIAGO., PRINCESS LEA SERANTES., AND ALDEN CRAIG
CATALUNA., An Online Management Information System of Capstone Project with
| Decision Support for Cavite State University - Imus Campus. Undergraduate Thesis Bachelor
of Science in Computer Science, Computer Studies Department, Cavite State University — Imus
Campus Imus, Cavite. June 2022. Adviser: Mrs. Grace S. Ibanez.

The researchers hope to create an online management information system of capstone
project with decision support for Cavite State University - Imus Campus.

Approve & Push to Repository
OCR;

        $result = $service->extract($text);

        $this->assertContains('BRENN ALDWIN SANTIAGO', $result['authors']);
        $this->assertContains('PRINCESS LEA SERANTES', $result['authors']);
        $this->assertContains('ALDEN CRAIG CATALUNA', $result['authors']);

        $this->assertStringContainsString(
            'An Online Management Information System of Capstone Project',
            $result['title']
        );

        $this->assertSame('Mrs. Grace S. Ibanez', $result['adviser']);
        $this->assertSame('Bachelor of Science in Computer Science', $result['program']);
        $this->assertSame('Computer Studies Department', $result['department']);
        $this->assertSame('Cavite State University - Imus Campus', $result['institution']);
        $this->assertSame('June 2022', $result['publication_date']);

        $this->assertStringContainsString(
            'The researchers hope to create',
            $result['abstract']
        );

        $this->assertStringNotContainsString(
            'Approve & Push to Repository',
            $result['abstract']
        );
    }

    public function test_returns_empty_metadata_when_ocr_text_is_empty(): void
    {
        $service = new MetadataExtractionService();

        $result = $service->extract('');

        $this->assertNull($result['title']);
        $this->assertSame([], $result['authors']);
        $this->assertNull($result['adviser']);
        $this->assertNull($result['abstract']);
    }

    public function test_extracts_an_adviser_name_without_including_the_abstract(): void
    {
        $service = new MetadataExtractionService();

        $text = <<<'OCR'
ABSTRACT
Sample Project. Undergraduate Thesis.
January 2024. Adviser: Mrs. Grace S. Ibanez.

The researchers hope to develop a system.
OCR;

        $result = $service->extract($text);

        $this->assertSame(
            'Mrs. Grace S. Ibanez',
            $result['adviser']
        );
    }

    public function test_extracts_an_adviser_whose_name_wraps_across_lines(): void
    {
        $service = new MetadataExtractionService();

        $text = <<<'OCR'
ABSTRACT
Sample Project. Undergraduate Capstone Project.
January 2024. Adviser: Mr. Rhoel Joseph
R. Sarino.

This study aims to develop a system.
OCR;

        $result = $service->extract($text);

        $this->assertSame(
            'Mr. Rhoel Joseph R. Sarino',
            $result['adviser']
        );
    }
}