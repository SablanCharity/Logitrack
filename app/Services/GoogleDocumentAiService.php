<?php

namespace App\Services;

use Google\Cloud\DocumentAI\V1\Client\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\ProcessRequest;
use Google\Cloud\DocumentAI\V1\RawDocument;
use RuntimeException;

class GoogleDocumentAiService
{
    /**
     * Send an image or PDF to Google Document AI and return its OCR text.
     */
    public function extractText(string $filePath, string $mimeType): string
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            throw new RuntimeException(
                'The document file does not exist or cannot be read.'
            );
        }

        $projectId = config('services.google_cloud.project_id');
        $location = config('services.google_cloud.document_ai_location', 'us');
        $processorId = config('services.google_cloud.document_ai_processor_id');
        $credentialsPath = config('services.google_cloud.credentials');

        if (! $projectId || ! $location || ! $processorId) {
            throw new RuntimeException(
                'Google Document AI configuration is incomplete.'
            );
        }

        if (! $credentialsPath || ! is_file($credentialsPath)) {
            throw new RuntimeException(
                'Google Cloud credentials file was not found. Check GOOGLE_APPLICATION_CREDENTIALS in .env.'
            );
        }

        $client = new DocumentProcessorServiceClient([
            'apiEndpoint' => "{$location}-documentai.googleapis.com",
            'credentials' => $credentialsPath,
        ]);

        try {
            $rawDocument = (new RawDocument)
                ->setContent(file_get_contents($filePath))
                ->setMimeType($mimeType);

            $processorName = $client->processorName(
                $projectId,
                $location,
                $processorId
            );

            $request = (new ProcessRequest)
                ->setName($processorName)
                ->setRawDocument($rawDocument);

            $response = $client->processDocument($request);

            $text = $response->getDocument()->getText();

            if (! is_string($text) || trim($text) === '') {
                throw new RuntimeException(
                    'Google Document AI returned no recognized text.'
                );
            }

            return $text;
        } finally {
            $client->close();
        }
    }
}
