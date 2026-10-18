<?php

namespace App\Http\Controllers;

use App\Models\OldCapstone;
use App\Services\Ocr\MetadataExtractionService;
use App\Services\Ocr\TesseractService;
use Illuminate\Http\Request;

class OldCapstoneController extends Controller
{
    // Show upload form for scanning old capstones
    public function create()
    {
        if (!in_array(auth()->user()?->role, ['admin', 'ojt'])) {
            abort(403, 'Unauthorized action.');
        }

        return view('ocr.upload');
    }

    // Handle image upload, run Tesseract and Metadata Extraction
    public function store(Request $request, TesseractService $tesseract, MetadataExtractionService $extractor)
    {
        if (!in_array(auth()->user()?->role, ['admin', 'ojt'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        // Store the uploaded image in storage/app/public/ocr_uploads
        $imagePath = $request->file('image')->store('ocr_uploads', 'public');
        $fullPath = storage_path('app/public/' . $imagePath);

        try {
            // Run Tesseract OCR
            $rawText = $tesseract->extractText($fullPath);

            // Extract structured metadata
            $metadata = $extractor->extract($rawText);

            // Save as a pending old capstone record for human review
            $oldCapstone = OldCapstone::create([
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

            return redirect()->route('ocr.review', $oldCapstone->id)
                ->with('success', 'OCR processing complete. Please review and verify the extracted metadata.');

        } catch (\Exception $e) {
            return back()->withErrors(['image' => 'OCR processing failed: ' . $e->getMessage()]);
        }
    }

    // Display the human review and verification form
    public function review(OldCapstone $oldCapstone)
    {
        if (!in_array(auth()->user()?->role, ['admin', 'ojt'])) {
            abort(403, 'Unauthorized action.');
        }

        return view('ocr.review', compact('oldCapstone'));
    }

    // Update/Correct the extracted metadata after human review
    public function update(Request $request, OldCapstone $oldCapstone)
    {
        if (!in_array(auth()->user()?->role, ['admin', 'ojt'])) {
            abort(403, 'Unauthorized action.');
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

        $oldCapstone->update([
            ...$validated,
            'status' => 'reviewed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('ocr.review', $oldCapstone->id)
            ->with('success', 'Capstone metadata successfully updated and marked as reviewed.');
    }

    // Final Admin approval to officially push/recognize into the repository flow
    public function approve(OldCapstone $oldCapstone)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Only administrators can approve capstones into the final repository.');
        }

        $oldCapstone->update([
            'status' => 'admin_approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('repository.index')
            ->with('success', 'Old capstone successfully approved and added to the repository.');
    }
}