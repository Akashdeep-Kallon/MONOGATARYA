<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/CatalogController.php';

$type = $_GET['type'];
$id = $_GET['id'];
$idChapter = $_GET['idChapter'];
$chapterNumber = $_GET['numberChapter'];

$result = (new Catalog())->returnChapter($id, $idChapter, $chapterNumber, $type);
$active = $result['active'];

requiereActive($active);

$title = $result['title'];
$description = $result['description'];
$number = $result['number'];
$file = $result['File'];
$prevId = $result['prev_id'];
$nextId = $result['next_id'];

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
                <section class="card-panel" aria-labelledby="description">
                    <h2>Capítulo <?php echo $number; ?>: <?php echo htmlspecialchars($title); ?></h2>

                    <div class="video-container">
                        <video controls preload="metadata">
                            <source src="<?php echo ANIME_URL . htmlspecialchars($file); ?>" type="video/mp4">
                            Tu navegador no soporta vídeo HTML5.
                        </video>
                    </div>
                    <details>
                        <summary>Descripción</summary>
                        <article class="content-card">
                            <p class="field-help"><?php echo htmlspecialchars($description); ?></p>
                        </article>
                    </details>
                </section>

                <?php require $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/navegation.php'; ?>

            </section>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>
</body>

</html>