<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $assets = $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/assets'; ?>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/auth.css?v=<?php echo filemtime("$assets/styles/auth.css"); ?>">
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Registro promotor</title>
</head>

<body>
    <main class="auth-container" aria-labelledby="register-promoter-title">
        <button class="btn-back" type="button" onclick="history.back()"
            aria-label="Volver a la página anterior">❮</button>

        <h1 id="register-promoter-title">Registro de promotor</h1>

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/message.php'; ?>

        <form action="<?php echo CONTROLLER_URL; ?>/UserController.php" method="POST">

            <label class="sr-only" for="promoter-name">Nombre</label>
            <input id="promoter-name" name="name" class="btn-input input-name" type="text" placeholder="Nombre" required
                minlength="2" maxlength="30">

            <label class="sr-only" for="promoter-lastname">Apellido</label>
            <input id="promoter-lastname" name="username" class="btn-input input-lastname" type="text"
                placeholder="Apellido" required minlength="2" maxlength="30">

            <label class="sr-only" for="promoter-email">Correo electrónico</label>
            <input id="promoter-email" name="email" class="btn-input input-email" type="email" placeholder="Email"
                required>

            <label class="sr-only" for="promoter-password">Contraseña</label>
            <input id="promoter-password" class="btn-input input-password" type="password" name="password"
                placeholder="Contraseña" required minlength="6" maxlength="20"
                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$"
                title="La contraseña debe tener al menos 6 caracteres, una mayúscula, una minúscula y un número.">

            <label class="sr-only" for="promoter-confirm">Confirmar contraseña</label>
            <input id="promoter-confirm" class="btn-input" type="password" name="password_confirm"
                placeholder="Confirmar Contraseña" required minlength="6" maxlength="20"
                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$">

            <input class="btn btn-primary" type="submit" value="Crear cuenta de promotor" name="register_promoter">
        </form>
    </main>
</body>

</html>