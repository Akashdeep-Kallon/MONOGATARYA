<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/auth.css">
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Iniciar sesión</title>
</head>

<body>
    <main class="auth-container" aria-labelledby="login-title">
        <button class="btn-back" type="button" onclick="history.back()"
            aria-label="Volver a la página anterior">❮</button>

        <h1 id="login-title">Iniciar sesión</h1>

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/message.php'; ?>

        <form action="<?php echo CONTROLLER_URL; ?>/UserController.php" method="post">

            <label class="sr-only" for="login-email">Correo electrónico</label>
            <input id="login-email" name="email" class="btn-input input-email" type="email" placeholder="Email"
                required>

            <label class="sr-only" for="login-password">Contraseña</label>
            <input id="login-password" class="btn-input input-password" type="password" name="password"
                placeholder="Contraseña" required minlength="6" maxlength="20"
                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$"
                title="La contraseña debe tener al menos 6 caracteres, una mayúscula, una minúscula y un número.">

            <input class="btn btn-primary" type="submit" value="Iniciar sesión" name="login">
        </form>

        <p class="divider">O</p>

        <form action="register.html">
            <button class="btn btn-secondary" type="submit">Registrarse</button>
        </form>

        <label class="remember" for="remember-me">
            <input id="remember-me" type="checkbox">
            Recuérdame
        </label>
    </main>
</body>

</html>