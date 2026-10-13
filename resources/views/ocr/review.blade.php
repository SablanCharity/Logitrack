<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 px-4">
        <h2 class="text-2xl font-bold mb-6">Review & Verify Extracted OCR Metadata</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column: Scanned Image Preview -->
            <div class="bg-white p-4 rounded shadow">
                <h3 class="font-semibold text-lg mb-3">Scanned Source Document</h3>
                <img src="{{ asset('storage/' . $oldCapstone->metadata_image_path) }}" alt="Capstone Scan" class="w-full h-auto rounded border">
                
                <div class="mt-4">
                    <h4 class="font-medium text-sm text-gray-600">Raw OCR Text Output:</h4>
                    <pre class="bg-gray-100 p-3 rounded text-xs h-48 overflow-y-auto mt-1 whitespace-pre-wrap">{{ $oldCapstone->ocr_text }}</pre>
                </div>
            </div>

            <!-- Right Column: Editable Metadata Form -->
            <div class="bg-white p-6 rounded shadow">
                <h3 class="font-semibold text-lg mb-3">Extracted Metadata Verification</h3>
                
                <form action="{{ route('ocr.update', $oldCapstone->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="block text-sm font-medium">Title</label>
                        <input type="text" name="title" value="{{ old('title', $oldCapstone->title) }}" class="w-full border p-2 rounded text-sm" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium">Authors (comma-separated or JSON format)</label>
                        <input type="text" name="authors" value="{{ old('authors', implode(', ', $oldCapstone->authors ?? [])) }}" class="w-full border p-2 rounded text-sm">
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium">Adviser</label>
                        <input type="text" name="adviser" value="{{ old('adviser', $oldCapstone->adviser) }}" class="w-full border p-2 rounded text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div>
                            <label class="block text-sm font-medium">Program</label>
                            <input type="text" name="program" value="{{ old('program', $oldCapstone->program) }}" class="w-full border p-2 rounded text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Publication Date / Year</label>
                            <input type="text" name="publication_date" value="{{ old('publication_date', $oldCapstone->publication_date) }}" class="w-full border p-2 rounded text-sm">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium">Abstract</label>
                        <textarea name="abstract" rows="6" class="w-full border p-2 rounded text-sm">{{ old('abstract', $oldCapstone->abstract) }}</textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-700">
                            Save Corrections
                        </button>
                    </div>
                </form>

                <!-- Final Approval Action (Admin Only) -->
                @if(auth()->user()->role === 'admin' && $oldCapstone->status !== 'admin_approved')
                    <form action="{{ route('ocr.approve', $oldCapstone->id) }}" method="POST" class="mt-4 pt-4 border-t">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded text-sm hover:bg-green-700 font-semibold">
                            Approve & Publish to Main Repository
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>