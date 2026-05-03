$(function () {
    function closeFlashModal() {
        $('#flash-backdrop').fadeOut(150, function () {
            $(this).remove();
        });
    }

    $('#flash-backdrop').on('click', function (e) {
        if ($(e.target).is('#flash-backdrop')) {
            closeFlashModal();
        }
    });

    $('#flash-modal').on('click', function (e) {
        e.stopPropagation();
    });

    $('#flash-close').on('click', function (e) {
        e.preventDefault();
        closeFlashModal();
    });

    $(document).one('keydown.flash', function (e) {
        if (e.key === 'Escape') {
            closeFlashModal();
        }
    });
});
