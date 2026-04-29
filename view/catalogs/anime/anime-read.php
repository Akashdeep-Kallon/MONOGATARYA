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
$prevChapter = $result['prev_chapter'];
$nextChapter = $result['next_chapter'];
$subtitleTracks = [];
$chapterDir = trim(dirname($file), '.\\/') . '/';
$subtitleDir = '/var/www/uploads/Anime/' . $chapterDir . 'subtitles/';
$subtitleUrl = ANIME_URL . $chapterDir . 'subtitles/';

if (is_dir($subtitleDir)) {
    foreach (glob($subtitleDir . '*.vtt') as $subtitlePath) {
        $filename = basename($subtitlePath);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $label = ucwords(str_replace(['-', '_'], ' ', $name));
        $language = strtolower(preg_split('/[-_]/', $name)[0] ?? 'sub');

        $subtitleTracks[] = [
            'src' => $subtitleUrl . rawurlencode($filename),
            'label' => $label,
            'srclang' => preg_match('/^[a-z]{2,3}$/', $language) ? $language : 'und'
        ];
    }
}

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
    <style>
        video::cue {
            background: transparent;
            color: white;
            font-family: 'Inter', Arial, sans-serif;
            font-size: 1.5rem;
            font-weight: 500;
            line-height: 1.35;
        }
    </style>
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
                            <?php foreach ($subtitleTracks as $track) { ?>
                                <track kind="subtitles" src="<?php echo htmlspecialchars($track['src']); ?>"
                                    srclang="<?php echo htmlspecialchars($track['srclang']); ?>"
                                    label="<?php echo htmlspecialchars($track['label']); ?>">
                            <?php } ?>
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
