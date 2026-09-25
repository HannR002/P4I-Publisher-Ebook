<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure E-Book Reader</title>
    <style>
        body, html { margin: 0; padding: 0; background-color: #333; height: 100%; overflow: hidden; display: flex; flex-direction: column; align-items: center; }
        #pdf-container { width: 100%; height: 100vh; overflow-y: auto; text-align: center; }
        canvas { max-width: 100%; margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.5); }
    </style>
    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
        
        const STREAM_URL = "{!! $streamUrl !!}";
        const WATERMARK_TEXT = "{{ $userEmail }} | IP: {{ $userIp }} | Key: {{ $licenseKey }}";
        const START_PAGE = {{ $lastReadPage ?? 1 }};
        const PROGRESS_URL = "{{ route('drm.progress') }}";
        const LICENSE_KEY = "{{ $licenseKey }}";
    </script>
</head>
<body oncontextmenu="return false;">
    <div id="pdf-container"></div>

    <script src="{{ asset('js/secure-reader.js') }}"></script>
</body>
</html>
