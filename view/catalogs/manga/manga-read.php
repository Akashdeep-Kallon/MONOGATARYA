<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/CatalogController.php';

$type = $_GET['type'];
$id = $_GET['id'];
$idChapter = $_GET['idChapter'];
$chapterNumber = $_GET['numberChapter'];

$result = (new Catalog())->returnChapter($id, $idChapter, $chapterNumber, $type);

$title = $result['title'];
$description = $result['description'];
$number = $result['number'];
$file = $result['File'];
$prevId = $result['prev_id'];
$nextId = $result['next_id'];

// Ruta absoluta al index.txt
$chapterPath = MANGA_URL . $file;
$indexFile = $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/media/' . $file . 'index.txt';

// Leer páginas
$pages = file($indexFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/main.css" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Capítulo <?php echo $number; ?></title>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <section class="card-panel">

                <h2>Capítulo <?php echo $number; ?>: <?php echo htmlspecialchars($title); ?></h2>

                <details>
                    <summary>Descripción</summary>
                    <article class="content-card">
                        <p class="field-help"><?php echo htmlspecialchars($description); ?></p>
                    </article>
                </details>

                <div class="manga-container">
                    <?php foreach ($pages as $page) { ?>
                        <img class="manga-page" src="<?php echo MANGA_URL . $file . $page; ?>"
                            alt="Página del capítulo <?php echo $number; ?>">
                    <?php } ?>
                </div>

                <?php require $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/navegation.php'; ?>

    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>
</body>

</html>