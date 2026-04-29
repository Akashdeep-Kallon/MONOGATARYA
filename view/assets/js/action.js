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


    /* ── Carrusel / Galería hero ── */
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
