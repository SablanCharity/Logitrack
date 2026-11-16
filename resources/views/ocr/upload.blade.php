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
            <p class="lt-page-head__sub">Upload a scanned document, image of an old capstone paper to automatically extract its metadata using Tesseract OCR.</p>
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
                    <label for="image-first" class="lt-label">First Image (processed first)</label>
                    <input type="file" id="image-first" name="images[0]" class="lt-input" accept=".jpeg,.png,.jpg" required>
                    <span class="lt-hint">Choose the page you want OCR to read first, such as the title page.</span>
                </div>

                <div class="lt-field">
                    <label for="image-second" class="lt-label">Second Image (processed below the first)</label>
                    <input type="file" id="image-second" name="images[1]" class="lt-input" accept=".jpeg,.png,.jpg">
                    <span class="lt-hint">Optional. Choose the next page, such as the continuation of the abstract. Each image can be up to 10MB.</span>
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
