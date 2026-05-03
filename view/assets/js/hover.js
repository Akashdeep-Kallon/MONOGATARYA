$(document).ready(iniciarHover);

function iniciarHover() {
    $('.card').each(function () {
        var titol = $(this).attr('aria-label');
        $(this).append('<div class="hover-msg">' + titol + '</div>');
    });

    $('.card').mouseenter(function () {
        $(this).find('.hover-msg').fadeIn(300);
    });

    $('.card').mouseleave(function () {
        $(this).find('.hover-msg').fadeOut(200);
    });
}
