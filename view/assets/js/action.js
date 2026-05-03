$(document).ready(function () {

    /* ══════════════════════════════════════════
       Menú lateral desplegable (jQuery)
    ══════════════════════════════════════════ */
    var $menuBtn = $('#menuBtn');
    var $menuSidebar = $('#menuSidebar');
    var $menuOverlay = $('#menuOverlay');

    function openMenu() {
        $menuSidebar.addClass('is-open').attr('aria-hidden', 'false');
        $menuOverlay.addClass('is-open');
        $menuBtn.attr('aria-expanded', 'true');
        $('body').css('overflow', 'hidden');
    }

    function closeMenu() {
        $menuSidebar.removeClass('is-open').attr('aria-hidden', 'true');
        $menuOverlay.removeClass('is-open');
        $menuBtn.attr('aria-expanded', 'false');
        $('body').css('overflow', '');
    }

    $menuBtn.on('click', function () {
        if ($menuSidebar.hasClass('is-open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    $menuOverlay.on('click', closeMenu);

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });


    /* ══════════════════════════════════════════
       Carrusel / Galería hero (jQuery)
    ══════════════════════════════════════════ */
    var $gallery = $('#heroGallery');
    if (!$gallery.length) return;

    var $cards = $gallery.find('.card');
    var $dotsWrap = $gallery.find('.gallery-dots');
    var TOTAL = $cards.length;
    var current = 0;
    var autoTimer = null;

    // Limpiar dots previos para evitar duplicados
    $dotsWrap.empty();

    // Crear dots de navegación
    var $dots = $cards.map(function (i) {
        var $btn = $('<button>', {
            class: 'gallery-dot',
            'aria-label': 'Mostrar portada ' + (i + 1)
        });
        $btn.on('click', function () {
            stopAutoplay();
            goTo(i);
            startAutoplay();
        });
        $dotsWrap.append($btn);
        return $btn[0];
    });

    function goTo(index) {
        var prev = (index - 1 + TOTAL) % TOTAL;
        var next = (index + 1) % TOTAL;

        $cards.removeClass('is-active is-prev is-next');
        $($dots).removeClass('is-active');

        $cards.eq(index).addClass('is-active');
        $cards.eq(prev).addClass('is-prev');
        $cards.eq(next).addClass('is-next');
        $($dots[index]).addClass('is-active');

        current = index;
    }

    function startAutoplay() {
        autoTimer = setInterval(function () {
            goTo((current + 1) % TOTAL);
        }, 4000);
    }

    function stopAutoplay() {
        clearInterval(autoTimer);
    }

    // Clic en carta lateral → avanza al slide
    $cards.each(function (i) {
        $(this).on('click', function () {
            stopAutoplay();
            goTo(i);
            startAutoplay();
        });
    });

    // Soporte swipe táctil
    var touchStartX = 0;

    $gallery[0].addEventListener('touchstart', function (e) {
        touchStartX = e.touches[0].clientX;
        stopAutoplay();
    }, { passive: true });

    $gallery[0].addEventListener('touchend', function (e) {
        var diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 40) {
            goTo(diff > 0 ? (current + 1) % TOTAL : (current - 1 + TOTAL) % TOTAL);
        }
        startAutoplay();
    }, { passive: true });

    // Pausar autoplay con hover
    $gallery.on('mouseenter', stopAutoplay).on('mouseleave', startAutoplay);

    goTo(0);
    startAutoplay();

});