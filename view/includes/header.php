<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';

include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/cookies.php';
?>

<header>
    <div class="header-group">
        <button id="menuBtn" class="icon-btn white" aria-label="Abrir menu" aria-expanded="false"
            aria-controls="menuSidebar">
            <svg class="icon">
                <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#menu"></use>
            </svg>
        </button>

        <a href="<?php echo VIEW_URL; ?>/index.php" class="logo-link" aria-label="Volver a inicio">
            <img src="<?php echo ASSETS_URL; ?>/img/logo.webp" alt="Logo de la pagina Monogatarya">
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

                        <button class="icon-btn red" aria-label="Microfono">
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
                        echo "Inicia Sesion";
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

            <div id="loginArea">
                <?php if (!isLogged()) { ?>
                    <a id="loginBtn" href="<?php echo VIEW_URL; ?>/auth/login.php" class="icon-btn white user-link cookie-login-link"
                        aria-label="Iniciar sesion">
                        <svg class="icon">
                            <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#usuario"></use>
                        </svg>
                    </a>
                    <button id="showCookieBannerBtn" class="icon-btn white user-link cookie-blocked-btn cookie-show-banner" type="button"
                        title="Has de aceptar las cookies para iniciar sesion" aria-label="Mostrar aviso de cookies">
                        <svg class="icon">
                            <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#usuario"></use>
                        </svg>
                        <span class="cookie-blocked-badge" aria-hidden="true">!</span>
                    </button>
                <?php } else { ?>
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
