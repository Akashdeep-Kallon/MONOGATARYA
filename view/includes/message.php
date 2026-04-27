<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Mensajes de éxito
function setSuccess($msg, $location = null) {
    if (!isset($_SESSION['flash_success']) || !is_array($_SESSION['flash_success'])) {
        $_SESSION['flash_success'] = [];
    }
    if (is_array($msg)) {
        $_SESSION['flash_success'] = array_merge($_SESSION['flash_success'], $msg);
    } else {
        $_SESSION['flash_success'][] = $msg;
    }
    if ($location) {
        header("Location: " . $location);
        exit();
    }
}

// Mensajes de error
function setError($msg, $location = null) {
    if (!isset($_SESSION['flash_error']) || !is_array($_SESSION['flash_error'])) {
        $_SESSION['flash_error'] = [];
    }
    if (is_array($msg)) {
        $_SESSION['flash_error'] = array_merge($_SESSION['flash_error'], $msg);
    } else {
        $_SESSION['flash_error'][] = $msg;
    }
    if ($location) {
        header("Location: " . $location);
        exit();
    }
}

$errors    = [];
$successes = [];

if (!empty($_SESSION['flash_error']) && is_array($_SESSION['flash_error'])) {
    $errors = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}
if (!empty($_SESSION['flash_success']) && is_array($_SESSION['flash_success'])) {
    $successes = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

if (!empty($errors) || !empty($successes)):
?>
<!-- Modal de mensajes flash -->
<div id="flash-backdrop" role="dialog" aria-modal="true" aria-labelledby="flash-title">

    <div id="flash-modal" class="<?php echo !empty($errors) ? 'flash-error' : 'flash-success'; ?>">

        <button id="flash-close" aria-label="Cerrar"
                onclick="document.getElementById('flash-backdrop').remove()">✕</button>

        <?php if (!empty($errors)) { ?>
            <p id="flash-title" class="flash-heading">
                ⚠ Se han producido los siguientes errores:
            </p>
            <ul class="flash-list">
                <?php foreach ($errors as $msg){ ?>
                    <li><?php echo htmlspecialchars($msg); ?></li>
                <?php } ?>
            </ul>
        <?php } ?>

        <?php if (!empty($successes)){ ?>
            <p id="flash-title" class="flash-heading">
                ✓ <?php echo htmlspecialchars($successes[0]); ?>
            </p>
            <?php if (count($successes) > 1){ ?>
                <ul class="flash-list">
                    <?php foreach (array_slice($successes, 1) as $msg){ ?>
                        <li><?php echo htmlspecialchars($msg); ?></li>
                    <?php }?>
                </ul>
            <?php } ?>
        <?php } ?>

    </div>
</div>

<style>
#flash-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(2px);
    animation: fadeIn .2s ease;
}

#flash-modal {
    position: relative;
    width: min(420px, 90vw);
    padding: 22px 24px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Inter', Arial, sans-serif;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
    animation: slideIn .22s ease;
}

/* Estilo error — idéntico al .error-box original */
#flash-modal.flash-error {
    background: #ffbaba;
    color: #9c0000;
    border: 1px solid #f5a0a0;
}

/* Estilo éxito */
#flash-modal.flash-success {
    background: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
}

#flash-modal.flash-error,
#flash-modal.flash-error .flash-heading,
#flash-modal.flash-error .flash-list,
#flash-modal.flash-error .flash-list li,
#flash-modal.flash-error #flash-close {
    color: #9c0000;
}

#flash-modal.flash-success,
#flash-modal.flash-success .flash-heading,
#flash-modal.flash-success .flash-list,
#flash-modal.flash-success .flash-list li,
#flash-modal.flash-success #flash-close {
    color: #0f5132;
}

.flash-heading {
    margin: 0 0 10px;
    font-weight: 700;
    font-size: 15px;
}

.flash-list {
    margin: 0;
    padding-left: 20px;
    line-height: 1.7;
}

.flash-list li {
    list-style: disc;
}

#flash-close {
    position: absolute;
    top: 10px;
    right: 12px;
    border: none;
    background: none;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    line-height: 1;
    padding: 2px 4px;
    border-radius: 4px;
    transition: opacity .15s;
}

.flash-error  #flash-close { color: #9c0000; }
.flash-success #flash-close { color: #155724; }

#flash-close:hover { opacity: .6; }

@keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideIn { from { transform: translateY(-12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<script>
// Cierra al hacer clic en el fondo oscuro
document.getElementById('flash-backdrop').addEventListener('click', function(e) {
    if (e.target === this) this.remove();
});
// Cierra con la tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var b = document.getElementById('flash-backdrop');
        if (b) b.remove();
    }
}, { once: true });
</script>
<?php endif; ?>
