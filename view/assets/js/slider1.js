(function () {
    function iniciarSliderUltimos() {
        if (!window.jQuery || typeof window.jQuery.fn.slick !== 'function') {
            return false;
        }

        if (typeof window.jQuery.type !== 'function') {
            window.jQuery.type = function (value) {
                if (value === null) {
                    return 'null';
                }

                if (typeof value === 'undefined') {
                    return 'undefined';
                }

                return Object.prototype.toString.call(value).slice(8, -1).toLowerCase();
            };
        }

        var slider = window.jQuery('.sliderUltimos');

        if (slider.length === 0 || slider.hasClass('slick-initialized')) {
            return true;
        }

        var slideCount    = slider.children('.ultimo-slide').length;
        var desktopSlides = Math.min(3, Math.max(1, slideCount));
        var tabletSlides  = Math.min(2, Math.max(1, slideCount));

        slider.slick({
            dots: true,
            infinite: true,
            speed: 600,
            autoplay: true,
            autoplaySpeed: 3000,
            arrows: true,
            accessibility: true,
            draggable: true,
            focusOnSelect: false,
            pauseOnDotsHover: true,
            pauseOnFocus: true,
            swipe: true,
            swipeToSlide: false,
            touchMove: true,
            respondTo: 'window',
            slidesToShow: desktopSlides,
            slidesToScroll: desktopSlides,
            responsive: [
                {
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: tabletSlides,
                        slidesToScroll: tabletSlides,
                        infinite: true,
                        autoplay: true,
                        autoplaySpeed: 3000,
                        dots: true,
                        arrows: true
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        infinite: true,
                        autoplay: true,
                        autoplaySpeed: 3000,
                        arrows: true,
                        dots: true
                    }
                }
            ]
        });

        return true;
    }

    function esperarSlick(intentos) {
        if (iniciarSliderUltimos() || intentos <= 0) {
            return;
        }

        window.setTimeout(function () {
            esperarSlick(intentos - 1);
        }, 100);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            esperarSlick(30);
        });
    } else {
        esperarSlick(30);
    }

    window.addEventListener('load', function () {
        esperarSlick(10);
    });
})();
