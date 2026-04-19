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
    <title>Monogatarya - Publicar Capítulo</title>
</head>

<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <section class="card-panel" aria-labelledby="crear-evento-title">
                <h2 id="crear-evento-title" class="section-title">Añadir un capitulo</h2>

                <form class="form-vertical" action="<?php echo CONTROLLER_URL; ?>/CatalogController.php" method="post"
                    enctype="multipart/form-data">

                    <div class="field-group">
                        <label for="tipo-obra">Tipo</label>
                        <input id="title" type="text" name="type" value="<?php echo $_GET['type']; ?>" readonly>
                    </div>
                    <div class="field-group">
                        <label for="title">Título de la obra</label>
                        <input id="title" type="text" name="title" required minlength="5" maxlength="50" required>
                    </div>
                    <div class="field-group">
                        <label for="subtitle">Subtítulo de la obra</label>
                        <input id="subtitle" type="text" name="subtitle" required minlength="5" maxlength="50">
                    </div>
                    
                    <div class="field-group">
                        <label for="video">Añadir capítulo</label>
                        <input id="video" type="file" name="video" accept=".zip,application/zip" required>
                    </div>

                    <div class="field-group">
                        <label for="number">Número de capítulo</label>
                        <input id="number" type="number" name="number" min="1" required>
                    </div>

                    <div class="field-group">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description" required minlength="10"
                            maxlength="100"></textarea>
                    </div>

                    <div class="inline-actions">
                        <button type="submit" class="btn btn-add" name="add_chapter">Publicar capítulo</button>
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