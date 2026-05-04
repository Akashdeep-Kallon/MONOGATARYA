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
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/main.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/user.css?v=<?php echo getAssetVersion(); ?>" />
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
            <?php if (isPromoter()) { ?>
        <section class="card-panel promoter-tools" aria-labelledby="promoter-tools-title">
            <h2 id="promoter-tools-title" class="section-title">Herramientas Promotor</h2>

            <div class="promoter-status">
                <span class="promoter-active-badge">MODO PROMOTOR ACTIVO</span>
                <p>Puedes añadir y gestionar contenido de la plataforma.</p>
            </div>

            <div class="promoter-grid">
                <a href="<?php echo VIEW_URL; ?>/catalogs/work-create.php?type=anime" class="promoter-card">
                    <div class="promoter-card-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 3a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3h12zm-8 4v10l7-5-7-5z"/></svg>
                    </div>
                    <div class="promoter-card-text">
                        <strong>Añadir Anime</strong>
                        <p>Crea una nueva entrada en el catálogo de anime.</p>
                    </div>
                </a>

                <a href="<?php echo VIEW_URL; ?>/catalogs/work-create.php?type=manga" class="promoter-card">
                    <div class="promoter-card-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 2h14a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm0 2v16h14V4H4zm2 3h10v2H6V7zm0 4h10v2H6v-2zm0 4h7v2H6v-2z"/></svg>
                    </div>
                    <div class="promoter-card-text">
                        <strong>Añadir Manga</strong>
                        <p>Publica un manga en la plataforma.</p>
                    </div>
                </a>

                <a href="<?php echo VIEW_URL; ?>/catalogs/events/event-create.php" class="promoter-card">
                    <div class="promoter-card-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 2v2H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1h-3V2h-2v2H10V2H8zm-3 5h14v10H5V7zm2 2v2h2V9H7zm4 0v2h2V9h-2zm4 0v2h2V9h-2zM7 13v2h2v-2H7zm4 0v2h2v-2h-2z"/></svg>
                    </div>
                    <div class="promoter-card-text">
                        <strong>Crear Evento</strong>
                        <p>Organiza eventos para la comunidad.</p>
                    </div>
                </a>

                <a href="<?php echo VIEW_URL; ?>/catalogs/add-chapter.php" class="promoter-card">
                    <div class="promoter-card-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13zm-5 4h8v2H8v-2zm0 4h5v2H8v-2z"/></svg>
                    </div>
                    <div class="promoter-card-text">
                        <strong>Subir Capítulo</strong>
                        <p>Añade capítulos a una obra existente.</p>
                    </div>
                </a>
            </div>
        </section>
        <?php } ?>
    </div>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>
</body>

</html>