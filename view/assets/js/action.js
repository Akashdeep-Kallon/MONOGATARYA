$(document).ready(function () {

    /* ══════════════════════════════════════════
       Gestió de cookies amb localStorage (jQuery)
    ══════════════════════════════════════════ */

    var COOKIE_KEY = 'cookiesAccepted';
    var $banner       = $('#cookieBanner');
    var $loginBtn     = $('#loginBtn');
    var $showAgainBtn = $('#showCookieBannerBtn');

    /**
     * Aplica l'estat visual segons si l'usuari ha acceptat
     * o rebutjat les cookies.
     * @param {string} status  'accepted' | 'rejected' | null
     */
    function applyCookieState(status) {
        if (status === 'accepted') {
            // Amaga el banner i mostra el botó de login
            $banner.removeClass('cookie-banner--visible').attr('aria-hidden', 'true');
            $loginBtn.show();
            $showAgainBtn.hide();
        } else if (status === 'rejected') {
            // Amaga el banner i mostra el botó "torna a veure l'avís"
            $banner.removeClass('cookie-banner--visible').attr('aria-hidden', 'true');
            $loginBtn.hide();
            $showAgainBtn.show();
        } else {
            // Estat inicial: mostra el banner i amaga tots dos botons de login
            $banner.addClass('cookie-banner--visible').attr('aria-hidden', 'false');
            $loginBtn.hide();
            $showAgainBtn.hide();
        }
    }

    // ── Comprova l'estat guardat a localStorage en carregar la pàgina ──
    var savedStatus = localStorage.getItem(COOKIE_KEY);
    applyCookieState(savedStatus);

    // ── L'usuari ACCEPTA les cookies ──
    $('#cookieAccept').on('click', function () {
        localStorage.setItem(COOKIE_KEY, 'accepted');
        applyCookieState('accepted');
    });

    // ── L'usuari REBUTJA les cookies ──
    $('#cookieReject').on('click', function () {
        localStorage.setItem(COOKIE_KEY, 'rejected');
        applyCookieState('rejected');
    });

    // ── El botó "!" torna a mostrar el banner ──
    $showAgainBtn.on('click', function () {
        $banner.addClass('cookie-banner--visible').attr('aria-hidden', 'false');
        $loginBtn.hide();
        $showAgainBtn.hide();
    });


    /* ══════════════════════════════════════════
       Menú lateral desplegable (jQuery)
    ══════════════════════════════════════════ */
    var $menuBtn     = $('#menuBtn');
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
       Carrusel / Galeria hero (jQuery)
    ══════════════════════════════════════════ */
    var $gallery = $('#heroGallery');
    if (!$gallery.length) return;

    var $cards    = $gallery.find('.card');
    var $dotsWrap = $gallery.find('.gallery-dots');
    var TOTAL     = $cards.length;
    var current   = 0;
    var autoTimer = null;

    $dotsWrap.empty();

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

    $cards.each(function (i) {
        $(this).on('click', function () {
            stopAutoplay();
            goTo(i);
            startAutoplay();
        });
    });

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

    $gallery.on('mouseenter', stopAutoplay).on('mouseleave', startAutoplay);

    goTo(0);
    startAutoplay();

});
