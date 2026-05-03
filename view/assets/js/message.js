$(function () {
    function closeFlashModal() {
        $('#flash-backdrop').fadeOut(150, function () {
            $(this).remove();
        });
    }

    $(document).on('click', function (e) {
        if ($('#flash-backdrop').length && $(e.target).closest('#flash-modal').length === 0) {
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

});
