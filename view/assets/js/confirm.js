(function () {
    function init() {
        const modal = document.getElementById('confirm-backdrop');
        if (!modal) return;

        const msgEl = document.getElementById('confirm-message');
        const btnOk = document.getElementById('confirm-accept');
        const btnNo = document.getElementById('confirm-cancel');
        const btnX = document.getElementById('confirm-close');

        let pendingAction = null;

        function open(message) {
            msgEl.textContent = message;
            modal.classList.remove('confirm-hidden');
        }

        function close() {
            modal.classList.add('confirm-hidden');
            pendingAction = null;
        }

        function getMessage(el) {
            if (el.dataset.confirm) return el.dataset.confirm;

            const name = el.getAttribute('name') || '';

            if (name === 'delete') return '¿Seguro que quieres dar de baja tu cuenta?';
            if (name === 'delete_work') return '¿Seguro que quieres eliminar esta obra?';
            if (name === 'delete_chapter') return '¿Seguro que quieres eliminar este capítulo?';
            if (name === 'delete_event') return '¿Seguro que quieres eliminar este evento?';

            return '¿Seguro que quieres continuar?';
        }

        document.addEventListener('click', function (e) {
            const el = e.target.closest(
                'button[name="delete"], button[name="delete_work"], button[name="delete_event"], button[name="delete_chapter"], a.js-confirm-delete, [data-confirm]'
            );

            if (!el) return;

            e.preventDefault();
            e.stopPropagation();

            if (el.tagName === 'BUTTON') {
                const form = el.closest('form');
                const btnName = el.getAttribute('name');
                const btnValue = el.value || '1';

                pendingAction = function () {
                    if (form && btnName) {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = btnName;
                        hidden.value = btnValue;
                        form.appendChild(hidden);
                        form.submit();
                    }
                };
            } else if (el.tagName === 'A') {
                const href = el.getAttribute('href');
                pendingAction = function () {
                    window.location.href = href;
                };
            }

            open(getMessage(el));
        }, true);

        btnOk.addEventListener('click', function () {
            const action = pendingAction;
            close();
            if (typeof action === 'function') action();
        });

        btnNo.addEventListener('click', close);
        btnX.addEventListener('click', close);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) close();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('confirm-hidden')) {
                close();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();