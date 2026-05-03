$(document).ready(iniciarCarrusel);

function iniciarCarrusel() {
    var gallery = $('#heroGallery');
    if (gallery.length === 0) return;

    var cards = gallery.find('.card');
    var dotsWrap = gallery.find('.gallery-dots');
    var total = cards.length;
    var current = 0;
    var autoTimer = null;

    dotsWrap.empty();

    // Crear dots de navegació
    var dots = [];
    cards.each(function (i) {
        var btn = $('<button class="gallery-dot" aria-label="Mostrar portada ' + (i + 1) + '"></button>');
        btn.click(function () {
            stopAutoplay();
            goTo(i);
            startAutoplay();
        });
        dotsWrap.append(btn);
        dots.push(btn);
    });

    function goTo(index) {
        var prev = (index - 1 + total) % total;
        var next = (index + 1) % total;

        cards.removeClass('is-active is-prev is-next');
        $.each(dots, function (i, d) { d.removeClass('is-active'); });

        $(cards[index]).addClass('is-active');
        $(cards[prev]).addClass('is-prev');
        $(cards[next]).addClass('is-next');
        $(dots[index]).addClass('is-active');

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

    // Clic en carta lateral
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

    // Suport swipe tàctil
    var touchStartX = 0;
    gallery.on('touchstart', function (e) {
        touchStartX = e.originalEvent.touches[0].clientX;
        stopAutoplay();
    });
    gallery.on('touchend', function (e) {
        var diff = touchStartX - e.originalEvent.changedTouches[0].clientX;
        if (Math.abs(diff) > 40) {
            goTo(diff > 0 ? (current + 1) % total : (current - 1 + total) % total);
        }
        startAutoplay();
    });

    goTo(0);
    startAutoplay();
}
