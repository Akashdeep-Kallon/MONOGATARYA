<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php $assets = $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/assets'; ?>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/main.css?v=<?php echo filemtime("$assets/styles/main.css"); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/user.css?v=<?php echo filemtime("$assets/styles/user.css"); ?>" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Perfil de Usuario</title>
</head>

<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <section class="card-panel profile-panel" aria-labelledby="perfil-titulo">

                <?php echo "<h2 id=\"perfil-titulo\" class=\"section-title\">Perfil " . htmlspecialchars(ucfirst($_SESSION['status'])) . "</h2>"; ?>

                <form class="profile-layout" action="/DAM-Transversal/controller/UserController.php" method="post"
                    enctype="multipart/form-data">
                    <!-- COLUMNA IZQUIERDA -->
                    <aside class="avatar-box">
                        <?php if (isPromoter() && !empty($_SESSION['avatar'])) { ?>
                            <img src="<?php echo USER_URL . $_SESSION['avatar']; ?>" class="avatar avatar-img">
                        <?php } else { ?>
                            <svg class="avatar avatar-svg">
                                <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#usuario"></use>
                            </svg>
                        <?php } ?>

                        <?php if (isPromoter()) { ?>
                            <label for="foto-user" class="file-label">Cambiar foto de perfil</label>
                            <input id="foto-user" type="file" accept="image/*" name="avatar">
                        <?php } ?>
                    </aside>

                    <!-- COLUMNA DERECHA -->
                    <section class="profile-form">

                        <div class="field">
                            <label for="nombre">Nombre</label>
                            <input id="nombre" name="name" required minlength="2"
                                value="<?php echo htmlspecialchars($_SESSION['name']); ?>">
                        </div>

                        <div class="field">
                            <label for="apellido">Apellidos</label>
                            <input id="apellido" name="surname" required minlength="2"
                                value="<?php echo htmlspecialchars($_SESSION['surname']); ?>">
                        </div>

                        <div class="field">
                            <label for="usuario">Correo electrónico</label>
                            <input id="usuario" name="email" required minlength="4"
                                value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
                        </div>

                        <div class="field">
                            <label for="password">Contraseña</label>
                            <input id="password" name="password" type="password" minlength="6" maxlength="20"
                                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$"
                                title="La contraseña debe tener al menos 6 caracteres, una mayúscula, una minúscula y un número."
                                placeholder="Deja vacío para mantener la misma contraseña">
                        </div>

                        <div class="field full">
                            <label for="bio">Biografía</label>
                            <textarea id="bio" name="bio"><?php echo htmlspecialchars($_SESSION['bio']); ?></textarea>
                        </div>

                        <div class="profile-actions">
                            <button type="submit" class="btn btn-delete" name="update">Guardar cambios</button>
                            <button type="submit" class="btn btn-delete" name="delete">Borrar cuenta</button>
                            <button type="reset" class="btn btn-add">Reiniciar</button>
                        </div>
                        <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/message.php'; ?>
                    </section>
                </form>
            </section>
        </div>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>
</body>

</html>