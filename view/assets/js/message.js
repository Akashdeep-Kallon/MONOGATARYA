$('#flash-backdrop').on('click', function (e) {
    if ($(e.target).is('#flash-backdrop')) {
        $(this).fadeOut(150, function () { $(this).remove(); });
    }
});

$('#flash-close').on('click', function (e) {
    e.preventDefault();
    $('#flash-backdrop').fadeOut(150, function () { $(this).remove(); });
});

$(document).one('keydown.flash', function (e) {
    if (e.key === 'Escape') {
        $('#flash-backdrop').fadeOut(150, function () { $(this).remove(); });
    }
});
