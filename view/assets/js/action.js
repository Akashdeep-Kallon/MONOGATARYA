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

});