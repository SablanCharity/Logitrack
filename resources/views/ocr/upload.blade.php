<x-app-layout>
    <div class="max-w-3xl mx-auto py-10 px-4">
        <h2 class="text-2xl font-bold mb-6">Upload Old Hardbound Capstone Scan</h2>
        
        <form action="{{ route('ocr.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Capstone Cover / Abstract Image (JPEG, PNG)</label>
                <input type="file" name="image" class="w-full border p-2 rounded" required>
                @error('image')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Process with OCR & Extract Metadata
            </button>
        </form>
    </div>
</x-app-layout>