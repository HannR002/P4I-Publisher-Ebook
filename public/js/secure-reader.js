document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('pdf-container');

    // Anti-DevTools and Print Shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.keyCode === 123) { // F12
            e.preventDefault();
            return false;
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode === 'I'.charCodeAt(0)) { // Ctrl+Shift+I
            e.preventDefault();
            return false;
        }
        if (e.ctrlKey && e.keyCode === 'P'.charCodeAt(0)) { // Ctrl+P
            e.preventDefault();
            return false;
        }
        if (e.ctrlKey && e.keyCode === 'S'.charCodeAt(0)) { // Ctrl+S
            e.preventDefault();
            return false;
        }
    });

    // Render PDF
    let pdfDoc = null;
    let scale = 1.5;

    // Load PDF
    pdfjsLib.getDocument(STREAM_URL).promise.then(doc => {
        pdfDoc = doc;
        renderAllPages();
    }).catch(err => {
        console.error('Error loading PDF:', err);
        container.innerHTML = '<p style="color: white;">Failed to load PDF. The link might be expired or invalid.</p>';
    });

    let pagesRendered = 0;

    function renderAllPages() {
        for (let num = 1; num <= pdfDoc.numPages; num++) {
            renderPage(num);
        }
    }

    function renderPage(num) {
        pdfDoc.getPage(num).then(page => {
            const viewport = page.getViewport({ scale: scale });
            const canvas = document.createElement('canvas');
            canvas.dataset.pageNum = num; // Add page number data attribute
            canvas.id = 'page-' + num; // ID for scrolling
            
            const ctx = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            container.appendChild(canvas);

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };

            page.render(renderContext).promise.then(() => {
                // Draw watermark after page is rendered (Bottom Center, highly transparent)
                ctx.font = '14px Arial';
                ctx.fillStyle = 'rgba(150, 150, 150, 0.15)';
                ctx.textAlign = 'center';
                ctx.fillText(WATERMARK_TEXT, canvas.width / 2, canvas.height - 20);

                // Check if all pages requested have rendered, then scroll to START_PAGE
                pagesRendered++;
                if (pagesRendered === pdfDoc.numPages && START_PAGE > 1) {
                    setTimeout(() => {
                        const targetCanvas = document.getElementById('page-' + START_PAGE);
                        if (targetCanvas) {
                            targetCanvas.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }, 500); // Small delay to ensure layout is settled
                }
                
                // Attach observer
                observer.observe(canvas);
            });
        });
    }

    // Bookmark / Progress tracking
    let progressTimer = null;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const pageNum = entry.target.dataset.pageNum;
                // Debounce the save to prevent spamming
                clearTimeout(progressTimer);
                progressTimer = setTimeout(() => {
                    saveProgress(pageNum);
                }, 1000);
            }
        });
    }, { threshold: 0.5 }); // Fire when 50% of the page is visible

    function saveProgress(page) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch(PROGRESS_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                license_key: LICENSE_KEY,
                page: parseInt(page)
            })
        }).catch(err => console.error('Error saving progress:', err));
    }
});
