$(document).ready(function () {

    /* ══════════════════════════════════════════
       Gestió de cookies amb localStorage (jQuery)
    ══════════════════════════════════════════ */

    var COOKIE_KEY = 'cookiesAccepted';
    var $banner = $('#cookieBanner');
    var $loginBtn = $('#loginBtn');
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

});