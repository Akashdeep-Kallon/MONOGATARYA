$(document).ready(function () {
    var COOKIE_KEY = 'cookiesAccepted';
    var $banner = $('#cookieBanner');
    var $loginBtn = $('#loginBtn');
    var $showCookieBannerBtn = $('#showCookieBannerBtn');
    var $loginAllowedArea = $('#loginAllowedArea');
    var $loginCookieBlocked = $('#loginCookieBlocked');
    var $cookiesAcceptedInput = $('#cookiesAcceptedInput');

    function getCookieStatus() {
        try {
            return localStorage.getItem(COOKIE_KEY);
        } catch (e) {
            return null;
        }
    }

    function saveCookieStatus(status) {
        try {
            localStorage.setItem(COOKIE_KEY, status);
            return true;
        } catch (e) {
            return false;
        }
    }

    function applyCookieState(status) {
        var accepted = status === 'accepted';
        var hasDecision = status === 'accepted' || status === 'rejected';

        $banner.toggleClass('cookie-banner--visible', !hasDecision)
            .attr('aria-hidden', hasDecision ? 'true' : 'false');

        $loginBtn.css('display', accepted ? 'flex' : 'none');
        $showCookieBannerBtn.css('display', accepted ? 'none' : 'flex');
        $loginAllowedArea.toggle(accepted);
        $loginCookieBlocked.toggle(!accepted);
        $cookiesAcceptedInput.val(accepted ? '1' : '0');
    }

    function showCookieBanner() {
        if ($banner.length) {
            $banner.addClass('cookie-banner--visible').attr('aria-hidden', 'false');
        }
    }

    applyCookieState(getCookieStatus());

    $('#cookieAccept').on('click', function () {
        if (saveCookieStatus('accepted')) {
            applyCookieState('accepted');
        } else {
            showCookieBanner();
        }
    });

    $('#cookieReject').on('click', function () {
        saveCookieStatus('rejected');
        applyCookieState('rejected');
    });

    $('.cookie-show-banner').on('click', function () {
        showCookieBanner();
    });

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
