<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Legacy Capstone (OCR) · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>

<body class="lt-body">

    <header class="lt-nav lt-nav--landing">
        <div class="lt-nav__inner">
            <a href="{{ route('dashboard') }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>
            <nav class="lt-nav__links lt-nav__links--app lt-admin-nav" aria-label="Admin">
                <a href="{{ route('dashboard') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Dashboard" aria-label="Dashboard"><span class="material-symbols-outlined">dashboard</span><span class="lt-admin-nav-label">Dashboard</span></a>
                <a href="{{ route('repository.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Repository" aria-label="Repository"><span class="material-symbols-outlined">menu_book</span><span class="lt-admin-nav-label">Repository</span></a>
                <a href="{{ route('admin.capstones') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Capstone Review" aria-label="Capstone Review"><span class="material-symbols-outlined">fact_check</span><span class="lt-admin-nav-label">Capstone Review</span></a>
                <a href="{{ route('ocr.upload') }}" class="lt-nav__link lt-admin-nav-link is-active" data-tooltip="Upload Archived" aria-label="Upload Archived"><span class="material-symbols-outlined">upload_file</span><span class="lt-admin-nav-label">Upload Archived</span></a>
                <a href="{{ route('admin.accounts.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Staff Accounts" aria-label="Staff Accounts"><span class="material-symbols-outlined">manage_accounts</span><span class="lt-admin-nav-label">Staff Accounts</span></a>
                <a href="{{ route('admin.students.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Student Accounts" aria-label="Student Accounts"><span class="material-symbols-outlined">school</span><span class="lt-admin-nav-label">Student Accounts</span></a>
            </nav>
            <div class="lt-nav__actions">
                <a href="{{ route('profile.edit') }}" class="lt-user-chip lt-user-chip--link" aria-label="Edit profile"><span class="lt-avatar">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</span>{{ auth()->user()->first_name }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="lt-btn lt-btn--outline-light lt-btn--sm">Log Out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="lt-container lt-animate" style="max-width: 680px;">
        <a href="{{ route('dashboard') }}" class="lt-back">← Back to Dashboard</a>

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