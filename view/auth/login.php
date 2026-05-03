<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $assets = $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/assets'; ?>
    <link rel="stylesheet"
        href="<?php echo ASSETS_URL; ?>/styles/auth.css?v=<?php echo filemtime("$assets/styles/auth.css"); ?>">
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <script src="<?php echo ASSETS_URL; ?>/js/jquery.js?v=<?php echo filemtime("$assets/js/jquery.js"); ?>" defer></script>
    <script src="<?php echo ASSETS_URL; ?>/js/action.js?v=<?php echo filemtime("$assets/js/action.js"); ?>" defer></script>
    <title>Monogatarya - Iniciar sesion</title>
</head>

<body>
    <?php
    $cookiesContext = 'login';
    include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/cookies.php';
    ?>

    <main class="auth-container" aria-labelledby="login-title">
        <button class="btn-back" type="button"
            onclick="window.location.href='/DAM-Transversal/view/home.php'"
            aria-label="Volver al inicio">
            &lt;
        </button>

        <h1 id="login-title">Iniciar sesion</h1>

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/message.php'; ?>

        <div id="loginCookieBlocked" class="login-cookie-blocked">
            <p>Has de aceptar el uso de cookies antes de iniciar sesion.</p>
            <button class="btn btn-secondary cookie-show-banner" type="button">
                Ver aviso de cookies
            </button>
        </div>

        <div id="loginAllowedArea" class="login-allowed-area">
            <form id="loginForm" action="<?php echo CONTROLLER_URL; ?>/UserController.php" method="post">
                <input id="cookiesAcceptedInput" type="hidden" name="cookies_accepted" value="0">

                <label class="sr-only" for="login-email">Correo electronico</label>
                <input id="login-email" name="email" class="btn-input input-email" type="email" placeholder="Email"
                    required>

                <label class="sr-only" for="login-password">Contrasena</label>
                <input id="login-password" class="btn-input input-password" type="password" name="password"
                    placeholder="Contrasena" required minlength="6" maxlength="20"
                    pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$"
                    title="La contrasena debe tener al menos 6 caracteres, una mayuscula, una minuscula y un numero.">

                <input class="btn btn-primary" type="submit" value="Iniciar sesion" name="login">
            </form>

            <p class="divider">O</p>

            <form action="register.html">
                <button class="btn btn-secondary" type="submit">Registrarse</button>
            </form>

            <label class="remember" for="remember-me">
                <input id="remember-me" type="checkbox">
                Recuerdame
            </label>
        </div>
    </main>

</body>

</html>
