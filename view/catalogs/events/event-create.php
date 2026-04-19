<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
requireRole('promoter');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/main.css" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Publicar Evento</title>
</head>

<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <section class="card-panel" aria-labelledby="crear-evento-title">

                <h2 id="crear-evento-title" class="section-title">Formulario de gestión de evento</h2>

                <form class="form-vertical" action="<?php echo CONTROLLER_URL; ?>/CatalogController.php" method="post">

                    <div class="field-group">
                        <label for="nombre-evento">Título</label>
                        <input id="nombre-evento" type="text" name="title" required minlength="5" maxlength="50">
                    </div>

                    <div class="field-group">
                        <label for="subtitle">Subtítulo</label>
                        <input id="subtitle" type="text" name="subtitle" required minlength="5" maxlength="75">
                    </div>

                    <div class="field-group">
                        <label for="fecha-evento">Fecha</label>
                        <input id="fecha-evento" type="date" name="date_event" required>
                    </div>

                    <div class="field-group">
                        <label for="lugar-evento">Lugar</label>
                        <input id="lugar-evento" type="text" name="location" required minlength="1" maxlength="150">
                    </div>

                    <div class="field-group">
                        <label for="aforo">Aforo</label>
                        <input id="aforo" type="number" name="capacity" min="50" required>
                    </div>

                    <div class="field-group">
                        <label for="image-file" class="file-label">Subir imagen de portada</label>
                        <input id="image-file" type="file" accept="image/*" name="image_file">
                    </div>

                    <div class="field-group">
                        <label for="image-url">URL de la imagen de portada</label>
                        <input id="image-url" type="text" name="image_url">
                    </div>

                    <div class="field-group">
                        <label for="descripcion-evento">Descripción</label>
                        <textarea id="descripcion-evento" name="description" required minlength="10"
                            maxlength="500"></textarea>
                    </div>

                    <div class="inline-actions">
                        <button type="submit" class="btn btn-add" name="create_event">Publicar evento</button>
                        <button type="reset" class="btn btn-delete">Cancelar</button>
                    </div>

                </form>
                <?php if (!empty($_SESSION['login_error'])) { ?>
                    <div class="error-box">
                        <span class="icon">ⓘ</span>
                        <span>
                            <?php
                            foreach ($_SESSION['login_error'] as $error) {
                                echo htmlspecialchars($error) . "<br>";
                            }
                            ?>
                        </span>
                    </div>
                    <?php unset($_SESSION['login_error']); ?>
                <?php } ?>
            </section>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>

</body>

</html>