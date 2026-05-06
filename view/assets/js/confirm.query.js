(function () {
    const deleteSelectors = [
        'button[name="delete"]',
        'button[name="delete_work"]',
        'button[name="delete_event"]',
        'button[name="delete_chapter"]',
        'a.js-confirm-delete',
        '[data-confirm]'
    ].join(',');

    const $modal   = $('#confirm-backdrop');
    const $msg     = $('#confirm-message');
    const $accept  = $('#confirm-accept');
    const $cancel  = $('#confirm-cancel');
    const $close   = $('#confirm-close');

    let pendingAction = null;

    function openModal(message) {
        $msg.text(message || '¿Seguro que quieres eliminar este elemento? Esta acción no se puede deshacer.');
        $modal.removeClass('confirm-hidden');
    }

    function closeModal() {
        $modal.addClass('confirm-hidden');
        pendingAction = null;
    }

    function getMessage($el) {
        if ($el.data('confirm')) return $el.data('confirm');
        const name = $el.attr('name') || '';
        if (name === 'delete')         return '¿Seguro que quieres dar de baja tu cuenta? Esta acción es irreversible.';
        if (name === 'delete_work')    return '¿Seguro que quieres eliminar esta obra? Se borrarán también todos sus capítulos y archivos.';
        if (name === 'delete_chapter') return '¿Seguro que quieres eliminar este capítulo? Esta acción no se puede deshacer.';
        if (name === 'delete_event')   return '¿Seguro que quieres eliminar este evento? Esta acción no se puede deshacer.';
        return '¿Seguro que quieres continuar? Esta acción no se puede deshacer.';
    }

    $(document).on('click', deleteSelectors, function (e) {
        const $el = $(this);
        e.preventDefault();

        if ($el.is('button')) {
            const form = $el.closest('form')[0];
            const btnName = $el.attr('name');
            const btnValue = $el.val() || '1';
            pendingAction = function () {

                if (btnName) {
                    const hidden = document.createElement('input');
                    hidden.type  = 'hidden';
                    hidden.name  = btnName;
                    hidden.value = btnValue;
                    form.appendChild(hidden);
                }
                form.submit();
            };
        }
        
        else if ($el.is('a')) {
            const href = $el.attr('href');
            pendingAction = function () { window.location.href = href; };
        }

        openModal(getMessage($el));
    });

    $accept.on('click', function () {
        const action = pendingAction;
        closeModal();
        if (typeof action === 'function') action();
    });

    $cancel.on('click', closeModal);
    $close.on('click', closeModal);

    $modal.on('click', function (e) {
        if (e.target === this) closeModal();
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && !$modal.hasClass('confirm-hidden')) closeModal();
    });
})();