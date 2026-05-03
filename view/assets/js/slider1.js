$(document).ready(iniciarSlider1);

function iniciarSlider1() {
    $('#heroGallery .cards').slick({
        centerMode: true,
        centerPadding: '80px',
        slidesToShow: 1,
        dots: true,
        autoplay: true,
        autoplaySpeed: 4000,
        pauseOnHover: true,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    centerPadding: '40px'
                }
            },
            {
                breakpoint: 600,
                settings: {
                    centerMode: false,
                    centerPadding: '0'
                }
            }
        ]
    });
}
