<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';

$cookiesContext = $cookiesContext ?? 'site';
$isLoginContext = $cookiesContext === 'login';
$bannerTitle = $isLoginContext ? 'Cookies necesarias para iniciar sesion' : 'Uso de cookies';
$bannerText = $isLoginContext
    ? 'Para iniciar sesion necesitamos guardar tu aceptacion de cookies en localStorage. Aceptalas para mostrar el formulario de login.'
    : 'Utilizamos cookies propias y de terceros para mejorar tu experiencia, analizar el trafico y personalizar el contenido. Puedes aceptarlas o rechazarlas.';
?>

<div id="cookieBanner"
    class="cookie-banner<?php echo $isLoginContext ? ' cookie-banner--login' : ''; ?>"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cookieBannerTitle"
    aria-describedby="cookieBannerDesc"
    aria-hidden="true">
    <div class="cookie-banner__inner">
        <div class="cookie-banner__icon" aria-hidden="true">C</div>
        <div class="cookie-banner__text">
            <strong id="cookieBannerTitle"><?php echo htmlspecialchars($bannerTitle); ?></strong>
            <p id="cookieBannerDesc"><?php echo htmlspecialchars($bannerText); ?></p>
        </div>
        <div class="cookie-banner__actions">
            <button id="cookieAccept" class="btn btn-cookie-accept" type="button">
                Aceptar
            </button>
            <button id="cookieReject" class="btn btn-cookie-reject" type="button">
                Rechazar
            </button>
        </div>
    </div>
</div>
