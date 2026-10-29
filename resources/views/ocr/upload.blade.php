<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Legacy Capstone (OCR) &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>

<body class="lt-body">

    @include('components.site-navigation')

    <main class="lt-container lt-animate" style="max-width: 680px;">
        <a href="{{ route('dashboard') }}" class="lt-back">&larr; Back to Dashboard</a>

        <div class="lt-page-head">
            <span class="lt-eyebrow">OCR Scanner &amp; Import</span>
            <h1>Upload Legacy Capstone Document</h1>
            <p class="lt-page-head__sub">Upload a scanned document, image, or PDF of an old capstone paper to automatically extract its metadata using Tesseract OCR.</p>
        </div>

        @if($errors->any())
            <div class="lt-alert lt-alert--error" role="alert">
                <span class="lt-alert__icon">!</span>
                <div>
                    <strong>Please fix the following:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <section class="lt-card">
            <form method="POST" action="{{ route('ocr.store') }}" enctype="multipart/form-data" class="lt-form">
                @csrf

                <div class="lt-field">
                    <label for="image" class="lt-label">Document File (JPEG, PNG, JPG, PDF)</label>
                    <input type="file" id="image" name="image" class="lt-input" accept=".jpeg,.png,.jpg,.pdf" required>
                    <span class="lt-hint">Maximum file size: 10MB. Ensure the title page or abstract is clearly visible for accurate scanning.</span>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="lt-btn lt-btn--primary" style="width: 100%;">
                        Upload and Run OCR Extraction
                    </button>
                </div>
            </form>
        </section>
    </main>

</body>
</html>