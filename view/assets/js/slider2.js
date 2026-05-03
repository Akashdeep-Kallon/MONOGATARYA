(function () {
    function iniciarSliderPromotores() {
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

        var slider = window.jQuery('.sliderPromotors');

        if (slider.length === 0 || slider.hasClass('slick-initialized')) {
            return true;
        }

        var slideCount = slider.children('.promotor-slide').length;
        var desktopSlides = Math.min(3, Math.max(1, slideCount - 1));
        var tabletSlides = Math.min(2, Math.max(1, slideCount - 1));

        slider.slick({
            dots: true,
            infinite: true,
            speed: 300,
            autoplay: true,
            autoplaySpeed: 2000,
            arrows: true,
            accessibility: true,
            draggable: true,
            focusOnSelect: true,
            pauseOnDotsHover: true,
            swipe: true,
            swipeToSlide: true,
            touchMove: true,
            respondTo: 'window',
            slidesToShow: desktopSlides,
            slidesToScroll: 1,
            responsive: [
                {
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: tabletSlides,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: true,
                        arrows: true
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        arrows: true,
                        dots: true
                    }
                }
            ]
        });

        slider.get(0).addEventListener('click', function (event) {
            var slide = event.target.closest('.promotor-slide');

            if (!slide || !slider.get(0).contains(slide)) {
                return;
            }

            var index = parseInt(slide.getAttribute('data-slick-index'), 10);
            var currentIndex = slider.slick('slickCurrentSlide');

            if (Number.isNaN(index)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            if (index === currentIndex) {
                slider.slick('slickNext');
                return;
            }

            slider.slick('slickGoTo', index);
        }, true);

        return true;
    }

    function esperarSlick(intentos) {
        if (iniciarSliderPromotores() || intentos <= 0) {
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
