document.addEventListener('DOMContentLoaded', () => {

    /* ── Menú lateral desplegable ── */
    const menuBtn = document.getElementById('menuBtn');
    const menuSidebar = document.getElementById('menuSidebar');
    const menuOverlay = document.getElementById('menuOverlay');

    function openMenu() {
        menuSidebar.classList.add('is-open');
        menuOverlay.classList.add('is-open');
        menuSidebar.setAttribute('aria-hidden', 'false');
        menuBtn && menuBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        menuSidebar.classList.remove('is-open');
        menuOverlay.classList.remove('is-open');
        menuSidebar.setAttribute('aria-hidden', 'true');
        menuBtn && menuBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (menuBtn && menuSidebar) {
        menuBtn.addEventListener('click', () => {
            const isOpen = menuSidebar.classList.contains('is-open');
            isOpen ? closeMenu() : openMenu();
        });
    }

    if (menuOverlay) {
        menuOverlay.addEventListener('click', closeMenu);
    }

    // Cerrar menú con tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMenu();
    });


    /* ── Barra de progreso al subir vídeo ── */
    const uploadForm = document.querySelector('form[enctype="multipart/form-data"]');
    const uploadOverlay = document.getElementById('uploadOverlay');
    const uploadBarFill = document.getElementById('uploadBarFill');
    const uploadPct = document.getElementById('uploadPct');
    const uploadMB = document.getElementById('uploadMB');
    const uploadSpeed = document.getElementById('uploadSpeed');
    const uploadETA = document.getElementById('uploadETA');
    const uploadHint = document.getElementById('uploadHint');

    if (uploadForm && uploadOverlay) {
        uploadForm.addEventListener('submit', (e) => {
            const fileInput = uploadForm.querySelector('input[type="file"]');
            if (!fileInput || !fileInput.files.length) return;
            const file = fileInput.files[0];
            if (!file.type.startsWith('video/')) return;

            e.preventDefault();

            const formData = new FormData(uploadForm);
            const xhr = new XMLHttpRequest();
            const totalMB = file.size / (1024 * 1024);
            let startTime = null;
            let lastLoaded = 0;
            let lastTime = null;
            // Media móvil para suavizar la velocidad
            const speedSamples = [];

            uploadOverlay.classList.add('is-active');
            uploadOverlay.setAttribute('aria-hidden', 'false');

            xhr.upload.addEventListener('progress', (ev) => {
                if (!ev.lengthComputable) return;

                const now = Date.now();
                if (!startTime) { startTime = now; lastTime = now; }

                const loaded = ev.loaded;
                const total = ev.total;
                const pct = Math.round((loaded / total) * 100);

                // Velocidad instantánea (bytes/ms → MB/s)
                const dt = (now - lastTime) / 1000;       // segundos
                const dBytes = loaded - lastLoaded;
                if (dt > 0) {
                    speedSamples.push(dBytes / dt / (1024 * 1024));
                    if (speedSamples.length > 6) speedSamples.shift(); // ventana de 6 muestras
                }
                const avgSpeed = speedSamples.length
                    ? speedSamples.reduce((a, b) => a + b, 0) / speedSamples.length
                    : 0;

                const loadedMB = loaded / (1024 * 1024);
                const remainingMB = (total - loaded) / (1024 * 1024);
                const etaSec = avgSpeed > 0 ? remainingMB / avgSpeed : null;

                // Actualizar UI
                uploadBarFill.style.width = pct + '%';
                uploadPct.textContent = pct + '%';
                uploadMB.textContent = `${loadedMB.toFixed(1)} MB / ${totalMB.toFixed(1)} MB  (faltan ${remainingMB.toFixed(1)} MB)`;
                uploadSpeed.textContent = avgSpeed > 0 ? `${avgSpeed.toFixed(2)} MB/s` : '—';
                uploadETA.textContent = etaSec !== null
                    ? `Tiempo restante: ${formatETA(etaSec)}`
                    : '';

                if (pct === 100) {
                    uploadHint.textContent = 'Procesando en el servidor, espera un momento…';
                    uploadETA.textContent = '';
                }

                lastLoaded = loaded;
                lastTime = now;
            });

            xhr.addEventListener('load', () => {
                window.location.href = xhr.responseURL || window.location.href;
            });

            xhr.addEventListener('error', () => {
                uploadOverlay.classList.remove('is-active');
                alert('Error de red al subir el archivo. Inténtalo de nuevo.');
            });

            xhr.open('POST', uploadForm.action);
            xhr.send(formData);
        });
    }

    function formatETA(seconds) {
        if (seconds < 60) return `${Math.round(seconds)}s`;
        if (seconds < 3600) {
            const m = Math.floor(seconds / 60);
            const s = Math.round(seconds % 60);
            return `${m}m ${s}s`;
        }
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        return `${h}h ${m}m`;
    }


    const gallery = document.getElementById('heroGallery');
    if (!gallery) return;

    const cards = Array.from(gallery.querySelectorAll('.card'));
    const dotsWrap = gallery.querySelector('.gallery-dots');
    const TOTAL = cards.length;
    let current = 0;
    let autoTimer = null;

    // Crear dots de navegación
    const dots = cards.map((_, i) => {
        const btn = document.createElement('button');
        btn.className = 'gallery-dot';
        btn.setAttribute('aria-label', `Mostrar portada ${i + 1}`);
        btn.addEventListener('click', () => {
            stopAutoplay();
            goTo(i);
            startAutoplay();
        });
        dotsWrap.appendChild(btn);
        return btn;
    });

    function goTo(index) {
        const prev = (index - 1 + TOTAL) % TOTAL;
        const next = (index + 1) % TOTAL;

        cards.forEach(c => c.classList.remove('is-active', 'is-prev', 'is-next'));
        dots.forEach(d => d.classList.remove('is-active'));

        cards[index].classList.add('is-active');
        cards[prev].classList.add('is-prev');
        cards[next].classList.add('is-next');
        dots[index].classList.add('is-active');

        current = index;
    }

    function startAutoplay() {
        autoTimer = setInterval(() => goTo((current + 1) % TOTAL), 2500);
    }

    function stopAutoplay() {
        clearInterval(autoTimer);
    }

    // Clic en carta lateral → avanza al slide
    cards.forEach((card, i) => card.addEventListener('click', () => {
        stopAutoplay();
        goTo(i);
        startAutoplay();
    }));

    // Soporte swipe táctil
    let touchStartX = 0;
    gallery.addEventListener('touchstart', e => {
        touchStartX = e.touches[0].clientX;
        stopAutoplay();
    }, { passive: true });

    gallery.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 40)
            goTo(diff > 0 ? (current + 1) % TOTAL : (current - 1 + TOTAL) % TOTAL);
        startAutoplay();
    }, { passive: true });

    // Pausar autoplay con hover
    gallery.addEventListener('mouseenter', stopAutoplay);
    gallery.addEventListener('mouseleave', startAutoplay);

    goTo(0);
    startAutoplay();
});
