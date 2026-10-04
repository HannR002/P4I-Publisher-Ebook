<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pembaca Digital - {{ $title ?? 'P4I Digital Library' }}</title>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
        const STREAM_URL = "{!! $streamUrl !!}";
        const WATERMARK_TEXT = "{{ $userEmail ?? '' }} | IP: {{ $userIp ?? '' }} | Key: {{ $licenseKey ?? '' }}";
        const START_PAGE = {{ $lastReadPage ?? 1 }};
        const PROGRESS_URL = "{{ route('drm.progress') }}";
        const LICENSE_KEY = "{{ $licenseKey ?? '' }}";
    </script>

    <x-theme-init />
</head>
<body class="bg-background text-text-primary antialiased h-screen flex flex-col overflow-hidden" oncontextmenu="return false;">

    <!-- Top Bar -->
    <header class="h-16 flex-none bg-surface border-b border-border px-4 sm:px-6 flex items-center justify-between z-10 shadow-sm">
        <div class="flex items-center gap-4 truncate">
            @if(isset($backUrl))
                <a href="{{ $backUrl }}" class="flex items-center text-text-secondary hover:text-primary transition-colors">
                    <svg class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    <span class="hidden sm:inline font-medium">Kembali</span>
                </a>
            @else
                <button onclick="window.close()" class="flex items-center text-text-secondary hover:text-primary transition-colors">
                    <svg class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    <span class="hidden sm:inline font-medium">Tutup</span>
                </button>
            @endif

            <div class="h-6 w-px bg-border hidden sm:block"></div>

            <h1 class="text-base sm:text-lg font-bold truncate">
                {{ $title ?? 'Pembaca Digital' }}
            </h1>
        </div>

        <div class="flex items-center gap-3">
            @if(isset($downloadUrl) && $downloadUrl)
                <a href="{{ $downloadUrl }}" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-text-secondary hover:text-primary bg-background border border-border rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    <span class="hidden md:inline">Download</span>
                </a>
            @endif

            <x-theme-switcher />
        </div>
    </header>

    <!-- Reader Controls Toolbar (Optional, depending on PDF.js implementation in js) -->
    <div class="h-12 flex-none bg-background border-b border-border px-4 flex items-center justify-center gap-2 sm:gap-4 overflow-x-auto text-sm">
        <button id="prev-page" class="p-1.5 text-text-secondary hover:text-primary rounded" title="Halaman Sebelumnya">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>

        <div class="flex items-center gap-2 font-medium">
            <span id="page-num">1</span>
            <span class="text-text-muted">/</span>
            <span id="page-count" class="text-text-muted">--</span>
        </div>

        <button id="next-page" class="p-1.5 text-text-secondary hover:text-primary rounded" title="Halaman Selanjutnya">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>

        <div class="h-5 w-px bg-border mx-1"></div>

        <button id="zoom-out" class="p-1.5 text-text-secondary hover:text-primary rounded" title="Perkecil">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" /></svg>
        </button>

        <button id="zoom-in" class="p-1.5 text-text-secondary hover:text-primary rounded" title="Perbesar">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
        </button>
    </div>

    <!-- Main Reading Area -->
    <main class="flex-1 relative overflow-y-auto overflow-x-hidden bg-gray-100 dark:bg-gray-900" id="pdf-container">
        <!-- PDF.js will render canvas here -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none" id="loading-indicator">
            <div class="flex flex-col items-center gap-3 bg-surface p-6 rounded-xl shadow-lg border border-border">
                <svg class="w-8 h-8 text-primary animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="font-medium">Memuat Dokumen...</span>
            </div>
        </div>
    </main>

    <style>
        #pdf-container { text-align: center; padding: 20px 0; }
        canvas {
            max-width: 100%;
            margin: 0 auto 20px auto;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            display: block;
        }
    </style>

    <script src="{{ asset('js/secure-reader.js') }}"></script>
    <script>
        // Simple UI event listeners to bind with secure-reader.js if it exposes methods,
        // or just let secure-reader.js handle everything via element IDs.
        // We ensure loading indicator hides when canvas is added.
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length > 0) {
                    const canvasAdded = Array.from(mutation.addedNodes).some(node => node.nodeName === 'CANVAS');
                    if (canvasAdded) {
                        const loader = document.getElementById('loading-indicator');
                        if (loader) loader.style.display = 'none';
                    }
                }
            });
        });
        observer.observe(document.getElementById('pdf-container'), { childList: true });
    </script>
</body>
</html>
