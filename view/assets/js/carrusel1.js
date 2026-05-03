$(document).ready(iniciarCarrusel);

function iniciarCarrusel() {
    var gallery = $('#heroGallery');
    if (gallery.length === 0) return;

    var cards = gallery.find('.card');
    var dotsWrap = gallery.find('.gallery-dots');
    var total = cards.length;
    var current = 0;
    var autoTimer = null;

    // Crear dots de navegació
    dotsWrap.empty();
    cards.each(function (i) {
        var btn = $('<button class="gallery-dot" aria-label="Mostrar portada ' + (i + 1) + '"></button>');
        btn.click(function () {
            stopAutoplay();
            goTo(i);
            startAutoplay();
        });
        dotsWrap.append(btn);
    });

    function goTo(index) {
        var prev = (index - 1 + total) % total;
        var next = (index + 1) % total;

        cards.removeClass('is-active is-prev is-next');
        dotsWrap.find('.gallery-dot').removeClass('is-active');

        cards.eq(index).addClass('is-active');
        cards.eq(prev).addClass('is-prev');
        cards.eq(next).addClass('is-next');
        dotsWrap.find('.gallery-dot').eq(index).addClass('is-active');

        current = index;
    }

    function startAutoplay() {
        autoTimer = setInterval(function () {
            goTo((current + 1) % total);
        }, 4000);
    }

    function stopAutoplay() {
        clearInterval(autoTimer);
    }

    // Clic en carta
    cards.each(function (i) {
        $(this).click(function () {
            stopAutoplay();
            goTo(i);
            startAutoplay();
        });
    });

    // Pausar autoplay amb hover
    gallery.mouseenter(stopAutoplay);
    gallery.mouseleave(startAutoplay);

    goTo(0);
    startAutoplay();
}
