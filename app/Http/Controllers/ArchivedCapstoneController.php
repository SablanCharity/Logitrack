<?php

namespace App\Http\Controllers;

use App\Models\ArchivedCapstone;
use App\Models\CapstoneProject;
use App\Services\Ocr\MetadataExtractionService;
use App\Services\Ocr\TesseractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivedCapstoneController extends Controller
{
    // Show upload form for scanning old capstones
    public function create()
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        return view('ocr.upload');
    }

    // Handle image upload, run Tesseract and Metadata Extraction
    public function store(Request $request, TesseractService $tesseract, MetadataExtractionService $extractor)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:2'],
            'images.0' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:10240'],
            'images.1' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:10240'],
        ]);

        $imagePaths = [];

        try {
            $ocrTexts = [];

            // Laravel preserves the file input order; OCR each image in that order.
            foreach (array_values($request->file('images')) as $image) {
                $imagePath = $image->store('ocr_uploads', 'public');
                $imagePaths[] = $imagePath;
                $ocrTexts[] = trim($tesseract->extractText(storage_path('app/public/'.$imagePath)));
            }

            $rawText = trim(implode("\n\n", $ocrTexts));
            $displayOcrText = implode("\n\n", array_map(
                static fn (string $text, int $index): string => 'Image '.($index + 1)." OCR:\n".$text,
                $ocrTexts,
                array_keys($ocrTexts),
            ));

            // Extract structured metadata
            $metadata = $extractor->extract($rawText);
            $title = $metadata['title'] ?? '';

            if ($this->repositoryContainsTitle($title)) {
                Storage::disk('public')->delete($imagePaths);

                return back()->withErrors([
                    'images' => 'Duplicate study detected. “'.$title.'” matches a study already in the repository and cannot be added again.',
                ]);
            }

            // Save as a pending old capstone record for human review
            $archivedCapstone = ArchivedCapstone::create([
                'title' => $title,
                'abstract' => $metadata['abstract'],
                'authors' => $metadata['authors'],
                'adviser' => $metadata['adviser'],
                'program' => $metadata['program'],
                'department' => $metadata['department'],
                'institution' => $metadata['institution'],
                'publication_date' => $metadata['publication_date'],
                'metadata_image_path' => $imagePaths[0],
                'metadata_image_paths' => $imagePaths,
                'ocr_text' => $displayOcrText,
                'status' => 'pending',
            ]);

            return redirect()->route('ocr.review', $archivedCapstone->id)
                ->with('success', 'OCR processing complete. Please review and verify the extracted metadata.');

        } catch (\Exception $e) {
            Storage::disk('public')->delete($imagePaths);

            return back()->withErrors(['images' => 'OCR processing failed: '.$e->getMessage()]);
        }
    }

    // Display the human review and verification form
    public function review(ArchivedCapstone $archivedCapstone)
    {
        if (! in_array(auth()->user()?->role, ['admin', 'ojt'])) {
            abort(403, 'Unauthorized action.');
        }

        return view('ocr.review', compact('archivedCapstone'));
    }

    // Update/Correct the extracted metadata after human review
    public function update(Request $request, ArchivedCapstone $archivedCapstone)
    {
        if (! in_array(auth()->user()?->role, ['admin', 'ojt'])) {
            abort(403, 'Unauthorized action.');
        }

        if (auth()->user()?->role === 'ojt' && $archivedCapstone->status === 'approved') {
            abort(403, 'Approved capstones cannot be changed by OJTs.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['nullable', 'string'],
            'authors' => ['nullable', 'string'],
            'adviser' => ['nullable', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
            'publication_date' => ['nullable', 'string', 'max:255'],
        ]);

        if (isset($validated['authors']) && is_string($validated['authors'])) {
            $validated['authors'] = array_map('trim', explode(',', $validated['authors']));
        }

        $archivedCapstone->update([
            ...$validated,
            'status' => 'reviewed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('ocr.review', $archivedCapstone->id)
            ->with('success', 'Capstone metadata successfully updated and marked as reviewed.');
    }

    // Final Admin approval to officially push/recognize into the repository flow
    public function approve(ArchivedCapstone $archivedCapstone)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Only administrators can approve capstones into the final repository.');
        }

        if ($this->hasRepositoryDuplicate($archivedCapstone)) {
            return redirect()
                ->route('ocr.review', $archivedCapstone)
                ->with('error', 'Duplicate study detected. This title matches a study already in the repository, so it cannot be added again.');
        }

        $archivedCapstone->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('repository.index')
            ->with('success', 'Old capstone successfully approved and added to the repository.');
    }

    private function hasRepositoryDuplicate(ArchivedCapstone $archivedCapstone): bool
    {
        if ($this->repositoryContainsTitle($archivedCapstone->title, $archivedCapstone->id)) {
            return true;
        }

        $archivedMetadata = [
            'title' => $this->normalizeMetadataValue($archivedCapstone->title),
            'abstract' => $this->normalizeMetadataValue($archivedCapstone->abstract),
            'program' => $this->normalizeMetadataValue($archivedCapstone->program),
            'publication_date' => $this->normalizePublicationDate($archivedCapstone->publication_date),
        ];

        $approvedArchivedCapstones = ArchivedCapstone::query()
            ->where('status', 'approved')
            ->where('id', '!=', $archivedCapstone->id)
            ->get(['title', 'abstract', 'program', 'publication_date']);

        foreach ($approvedArchivedCapstones as $approvedCapstone) {
            if ($archivedMetadata === [
                'title' => $this->normalizeMetadataValue($approvedCapstone->title),
                'abstract' => $this->normalizeMetadataValue($approvedCapstone->abstract),
                'program' => $this->normalizeMetadataValue($approvedCapstone->program),
                'publication_date' => $this->normalizePublicationDate($approvedCapstone->publication_date),
            ]) {
                return true;
            }
        }

        $approvedModernCapstones = CapstoneProject::query()
            ->where('status', 'admin_approved')
            ->get(['title', 'abstract', 'program', 'publication_date']);

        foreach ($approvedModernCapstones as $approvedCapstone) {
            if ($archivedMetadata === [
                'title' => $this->normalizeMetadataValue($approvedCapstone->title),
                'abstract' => $this->normalizeMetadataValue($approvedCapstone->abstract),
                'program' => $this->normalizeMetadataValue($approvedCapstone->program),
                'publication_date' => $this->normalizePublicationDate($approvedCapstone->publication_date),
            ]) {
                return true;
            }
        }

        return false;
    }

    private function repositoryContainsTitle(?string $title, ?int $excludeArchivedId = null): bool
    {
        $normalizedTitle = $this->normalizeMetadataValue($title);

        if ($normalizedTitle === '') {
            return false;
        }

        $archivedStudies = ArchivedCapstone::query()
            ->where('status', 'approved')
            ->when($excludeArchivedId !== null, fn ($query) => $query->where('id', '!=', $excludeArchivedId))
            ->get(['title']);

        foreach ($archivedStudies as $study) {
            if ($this->normalizeMetadataValue($study->title) === $normalizedTitle) {
                return true;
            }
        }

        $modernStudies = CapstoneProject::query()
            ->where('status', 'admin_approved')
            ->get(['title']);

        foreach ($modernStudies as $study) {
            if ($this->normalizeMetadataValue($study->title) === $normalizedTitle) {
                return true;
            }
        }

        return false;
    }

    private function normalizeMetadataValue(?string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value ?? '') ?? ''));
    }

    private function normalizePublicationDate(mixed $value): string
    {
        $normalizedValue = $this->normalizeMetadataValue((string) $value);
        $timestamp = strtotime($normalizedValue);

        return $timestamp === false
            ? $normalizedValue
            : date('Y-m-d', $timestamp);
    }
}
