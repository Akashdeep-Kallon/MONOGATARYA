<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
?>

<!-- ══════════════════════════════════════════
     Banner d'avís de cookies
══════════════════════════════════════════ -->
<div id="cookieBanner" class="cookie-banner" role="dialog" aria-modal="true"
    aria-labelledby="cookieBannerTitle" aria-describedby="cookieBannerDesc">
    <div class="cookie-banner__inner">
        <div class="cookie-banner__icon" aria-hidden="true">🍪</div>
        <div class="cookie-banner__text">
            <strong id="cookieBannerTitle">Ús de cookies</strong>
            <p id="cookieBannerDesc">
                Utilitzem cookies pròpies i de tercers per millorar la teva experiència,
                analitzar el trànsit i personalitzar el contingut. Pots acceptar-les totes
                o rebutjar-les. Si les rebutges, algunes funcionalitats, com l'inici de sessió,
                no estaran disponibles.
            </p>
        </div>
        <div class="cookie-banner__actions">
            <button id="cookieAccept" class="btn btn-cookie-accept" type="button">
                Acceptar totes
            </button>
            <button id="cookieReject" class="btn btn-cookie-reject" type="button">
                Rebutjar
            </button>
        </div>
    </div>
</div>

<header>
    <div class="header-group">
        <button id="menuBtn" class="icon-btn white" aria-label="Abrir menú" aria-expanded="false"
            aria-controls="menuSidebar">
            <svg class="icon">
                <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#menu"></use>
            </svg>
        </button>

        <a href="<?php echo VIEW_URL; ?>/index.php" class="logo-link" aria-label="Volver a inicio">
            <img src="<?php echo ASSETS_URL; ?>/img/logo.webp" alt="Logo de la página Monogatarya">
        </a>

        <h1>MONOGATARYA</h1>

        <div class="right-group">

            <?php if (!empty($showSearch)) { ?>
                <form action="" autocomplete="on" method="get">
                    <div class="search">
                        <button class="icon-btn red" aria-label="Buscar">
                            <svg class="icon">
                                <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#buscar"></use>
                            </svg>
                        </button>

                        <input class="search-input" type="search" name="search" placeholder="Buscar" required minlength="2"
                            maxlength="40">

                        <button class="icon-btn red" aria-label="Micrófono">
                            <svg class="icon">
                                <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#microfono"></use>
                            </svg>
                        </button>
                    </div>
                </form>
            <?php } ?>

            <div class="user-info">
                <span>
                    <?php
                    if (isLogged()) {
                        echo htmlspecialchars($_SESSION['name']) . " " . htmlspecialchars($_SESSION['surname']);
                    } else {
                        echo "Inicia Sesión";
                    }
                    ?>
                </span>

                <span>
                    <?php
                    if (isRole('guest'))
                        echo "Invitado";
                    if (isRole('reader'))
                        echo "Lector";
                    if (isRole('promoter'))
                        echo "Promotor";
                    ?>
                </span>
            </div>

            <!-- Àrea de login controlada per jQuery segons l'estat de les cookies -->
            <div id="loginArea">
                <?php if (!isLogged()) { ?>
                    <!-- Botó de login: visible si s'accepten cookies -->
                    <a id="loginBtn" href="<?php echo VIEW_URL; ?>/auth/login.php"
                        class="icon-btn white user-link" aria-label="Iniciar sesión">
                        <svg class="icon">
                            <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#usuario"></use>
                        </svg>
                    </a>
                    <!-- Botó alternatiu: mostra de nou el banner si es rebutgen les cookies -->
                    <button id="showCookieBannerBtn" class="icon-btn white user-link cookie-blocked-btn"
                        type="button" title="Has d'acceptar les cookies per iniciar sessió"
                        aria-label="Mostrar avís de cookies">
                        <svg class="icon">
                            <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#usuario"></use>
                        </svg>
                        <span class="cookie-blocked-badge" aria-hidden="true">!</span>
                    </button>
                <?php } else { ?>
                    <!-- Usuari autenticat: botó de perfil sempre visible -->
                    <a href="<?php echo VIEW_URL; ?>/profile.php" class="icon-btn white user-link"
                        aria-label="Ir al perfil">
                        <?php if (isPromoter() && !empty($_SESSION['avatar'])) { ?>
                            <img src="<?php echo USER_URL . $_SESSION['avatar']; ?>" class="header-avatar">
                        <?php } else { ?>
                            <svg class="icon">
                                <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#usuario"></use>
                            </svg>
                        <?php } ?>
                    </a>
                <?php } ?>
            </div>

        </div>
    </div>
</header>
