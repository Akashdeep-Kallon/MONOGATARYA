<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/CatalogController.php';
requireRole('promoter');

if (isset($_GET['type'])) {
    $type = $_GET['type'];
} else {
    $type = '';
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} else {
    $id = 0;
}

if (isset($_GET['idChapter'])) {
    $idChapter = intval($_GET['idChapter']);
} else {
    $idChapter = 0;
}

$catalog = new Catalog();
$chapter = $catalog->getChapter($id, $idChapter);

if (!$chapter) {
    if ($type === 'Manga') {
        $redirectType = 'manga';
    } else {
        $redirectType = 'anime';
    }
    header('Location: ' . VIEW_URL . '/catalogs/' . $redirectType . '/work-detail.php?type=' . urlencode($type) . '&id=' . $id);
    exit();
}

$pageType = $type;
$chapterNumber = $chapter['Chapter_Number'];

if ($pageType === 'Anime') {
    $uploadAccept = 'video/mp4,video/webm,.mov';
    $uploadHint = 'Sube el vídeo del episodio (MP4, WEBM, MOV — máx. 1GB)';
} else {
    $uploadAccept = '.zip,application/zip';
    $uploadHint = 'Sube un ZIP con las páginas del capítulo en JPG/PNG/WEBP — máx. 500MB.';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../assets/styles/main.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Editar Capítulo</title>
</head>

<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <section class="card-panel" aria-labelledby="edit-chapter-title">
                <h2 id="edit-chapter-title" class="section-title">Editar capítulo
                    <?php echo htmlspecialchars($chapter['Chapter_Number']); ?></h2>

                <form class="form-vertical" action="<?php echo CONTROLLER_URL; ?>/CatalogController.php" method="post"
                    enctype="multipart/form-data">
                    <input type="hidden" name="id_work" value="<?php echo $id; ?>">
                    <input type="hidden" name="id_chapter" value="<?php echo $idChapter; ?>">
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($pageType); ?>">

                    <div class="field-group">
                        <label for="tipo-obra">Tipo</label>
                        <input id="tipo-obra" type="text" name="type_display"
                            value="<?php echo htmlspecialchars($pageType); ?>" readonly>
                    </div>

                    <div class="field-group">
                        <label for="chapter-number">Número de capítulo</label>
                        <input id="chapter-number" type="number" name="chapter_number" min="1"
                            value="<?php echo htmlspecialchars($chapter['Chapter_Number']); ?>" required>
                    </div>

                    <div class="field-group">
                        <label for="title">Título del capítulo</label>
                        <input id="title" type="text" name="title" required minlength="1" maxlength="50"
                            value="<?php echo htmlspecialchars($chapter['Title']); ?>">
                    </div>

                    <div class="field-group">
                        <label for="video">Subir nuevo archivo (opcional)</label>
                        <input id="video" type="file" name="video" accept="<?php echo $uploadAccept; ?>">
                        <small><?php echo $uploadHint; ?></small>
                    </div>

                    <div class="field-group">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description"
                            maxlength="100"><?php echo htmlspecialchars($chapter['Description']); ?></textarea>
                    </div>

                    <div class="inline-actions">
                        <button type="submit" class="btn btn-add" name="edit_chapter">Guardar cambios</button>
                        <button type="submit" class="btn btn-delete" name="delete_chapter"
                            onclick="return confirm('¿Estás seguro de que quieres eliminar este capítulo');">Eliminar
                            capítulo</button>
                    </div>
                </form>
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/message.php'; ?>
            </section>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>

</body>

</html>
