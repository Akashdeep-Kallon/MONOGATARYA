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
    <link rel="stylesheet" href="../assets/styles/main.css" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Publicar Obra</title>
</head>

<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <section class="card-panel" aria-labelledby="crear-evento-title">
                <h2 id="crear-evento-title" class="section-title">Formulario de creación de obra</h2>

                <form class="form-vertical" action="<?php echo CONTROLLER_URL; ?>/CatalogController.php" method="post"
                    enctype="multipart/form-data">

                    <div class="field-group">
                        <label for="tipo-obra">Tipo</label>
                        <input id="title" type="text" name="type" value="<?php echo $_GET['type']; ?>" readonly>
                    </div>
                    <div class="field-group">
                        <label for="title">Título de la obra</label>
                        <input id="title" type="text" name="title" required minlength="5" maxlength="50">
                    </div>
                    <div class="field-group">
                        <label for="subtitle">Subtítulo</label>
                        <input id="subtitle" type="text" name="subtitle" required minlength="5" maxlength="75">
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
                        <label for="video">Subir tráiler</label>
                        <input id="video" type="file" name="trailer" accept="video/*">
                    </div>
                    <div class="field-group">
                        <label for="premiere_date">Fecha de estreno</label>
                        <input id="premiere_date" type="date" name="premiere_date" required>
                    </div>
                    <div class="field-group">
                        <label for="studio">Estudio / plataforma</label>
                        <input id="studio" type="text" name="studio" max="25" required>
                    </div>
                    <div class="field-group">
                        <label for="gender">Género</label>
                        <input id="gender" type="text" name="gender" max="50" required>
                    </div>
                    <div class="field-group">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description" required minlength="10"
                            maxlength="500"></textarea>
                    </div>

                    <div class="inline-actions">
                        <button type="submit" class="btn btn-add" name="create_work">Publicar obra</button>
                        <button type="reset" class="btn btn-delete" name="cancelar">Cancelar</button>
                    </div>
                </form>

            </section>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>

</body>

</html>