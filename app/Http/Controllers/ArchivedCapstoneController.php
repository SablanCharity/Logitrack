<?php

namespace App\Http\Controllers;

use App\Models\ArchivedCapstone;
use App\Services\Ocr\MetadataExtractionService;
use App\Services\Ocr\TesseractService;
use Illuminate\Http\Request;

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
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        // Store the uploaded image in storage/app/public/ocr_uploads
        $imagePath = $request->file('image')->store('ocr_uploads', 'public');
        $fullPath = storage_path('app/public/'.$imagePath);

        try {
            // Run Tesseract OCR
            $rawText = $tesseract->extractText($fullPath);

            // Extract structured metadata
            $metadata = $extractor->extract($rawText);

            // Save as a pending old capstone record for human review
            $archivedCapstone = ArchivedCapstone::create([
                'title' => $metadata['title'] ?? '',
                'abstract' => $metadata['abstract'],
                'authors' => $metadata['authors'],
                'adviser' => $metadata['adviser'],
                'program' => $metadata['program'],
                'department' => $metadata['department'],
                'institution' => $metadata['institution'],
                'publication_date' => $metadata['publication_date'],
                'metadata_image_path' => $imagePath,
                'ocr_text' => $rawText,
                'status' => 'pending',
            ]);

            return redirect()->route('ocr.review', $archivedCapstone->id)
                ->with('success', 'OCR processing complete. Please review and verify the extracted metadata.');

        } catch (\Exception $e) {
            return back()->withErrors(['image' => 'OCR processing failed: '.$e->getMessage()]);
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

        $archivedCapstone->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('repository.index')
            ->with('success', 'Old capstone successfully approved and added to the repository.');
    }
}
