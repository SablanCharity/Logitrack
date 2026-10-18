<?php

namespace App\Services\Ocr;

use RuntimeException;
use Symfony\Component\Process\Process;

class TesseractService
{
    public function extractText(string $imagePath): string
    {
        if (! is_file($imagePath) || ! is_readable($imagePath)) {
            throw new RuntimeException(
                'The image file does not exist or cannot be read.'
            );
        }

        $binary = config('services.tesseract.binary', 'tesseract');

        $process = new Process([
            $binary,
            $imagePath,
            'stdout',
            '-l',
            'eng',
            '--psm',
            (string) config('services.tesseract.psm', 3),
        ]);

        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                'Tesseract OCR failed: '.$process->getErrorOutput()
            );
        }

        return trim($process->getOutput());
    }
}
