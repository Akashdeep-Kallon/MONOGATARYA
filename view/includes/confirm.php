<?php if (!defined('CONFIRM_INCLUDED')): define('CONFIRM_INCLUDED', true); ?>

    <div id="confirm-backdrop" class="confirm-hidden" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
        <div id="confirm-modal">
            <button id="confirm-close" type="button" aria-label="Cerrar">✕</button>
            <p id="confirm-title" class="confirm-heading">⚠ Confirmar acción</p>
            <p id="confirm-message" class="confirm-text">
                ¿Seguro que quieres eliminar este elemento? Esta acción no se puede deshacer.
            </p>
        </div>
    </div>
<?php endif; ?>