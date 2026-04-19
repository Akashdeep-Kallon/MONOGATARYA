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
                    <input type="hidden" name="id_work" value="<?php echo intval($_GET['id'] ?? 0); ?>">

                    <div class="field-group">
                        <label for="tipo-obra">Tipo</label>
                        <input id="title" type="text" name="type"
                            value="<?php echo htmlspecialchars($_GET['type'] ?? ''); ?>" readonly>
                    </div>
                    <div class="field-group">
                        <label for="title">Título del capítulo</label>
                        <input id="title" type="text" name="title" required minlength="5" maxlength="50" required>
                    </div>

                    <div class="field-group">
                        <label for="video">Añadir capítulo</label>
                        <?php if ($_GET['type'] === 'Anime') { ?>
                            <input id="video" type="file" name="video" accept="video/mp4,video/webm,.mov" required>
                            <small>Sube el vídeo del episodio (MP4, WEBM, MOV — máx. 500MB)</small>
                        <?php } else { ?>
                            <input id="video" type="file" name="video" accept=".zip,application/zip" required>
                            <small>Sube un ZIP con las páginas del capítulo en (JPG/PNG/WEBP— máx. 500MB)</small>
                        <?php } ?>
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