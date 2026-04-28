<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/CatalogController.php';

$type = $_GET['type'];
$id = $_GET['id'] ?? 0;

$result = (new Catalog())->returnWorkDetail($id, $type);
$active = $result['active'];

requiereActive($active);

$title = $result['title'];
$subtitle = $result['subtitle'];
$image = $result['image'];
$trailer = $result['trailer'];
$description = $result['description'];
$premiere = $result['premiere'];
$studio = $result['studio'];
$gender = $result['gender'];
$chapters = $result['chapters'];

$redirectType = strtolower($type);
if ($redirectType !== 'anime' && $redirectType !== 'manga') {
    $redirectType = 'anime';
}

$linkMedia = ($redirectType === 'manga') ? MANGA_URL : ANIME_URL;
$coverUrl = getCoverImageUrl($image, $type);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/main.css" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - <?php echo htmlspecialchars($title); ?></title>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <article class="card-panel event-editorial" aria-labelledby="work-title">

                <!-- Columna principal -->
                <section>
                    <h2 id="work-title" class="section-title">
                        <?php echo htmlspecialchars($title); ?>
                    </h2>
                    <?php if (!empty($subtitle)) { ?>
                        <p class="work-subtitle"><?php echo htmlspecialchars($subtitle); ?></p>
                    <?php } ?>

                    <?php if (!empty($trailer)) { ?>
                        <details open>
                            <summary>Tráiler oficial</summary>
                            <p id="trailer-desc" class="field-help">
                                Tráiler oficial de <?php echo htmlspecialchars($title); ?>
                            </p>
                            <video controls preload="metadata" aria-describedby="trailer-desc">
                                <source src="<?php echo $linkMedia . $trailer; ?>" type="video/mp4">
                                Tu navegador no soporta vídeo HTML5.
                            </video>
                        </details>
                    <?php } ?>

                    <!-- Lista de capítulos -->
                    <?php if (!empty($chapters)) { ?>
                        <h3 class="chapter-heading">
                            <?php echo count($chapters); ?> capítulos en total
                        </h3>
                        <ul class="chapter-list" aria-label="Lista de capítulos">
                            <?php foreach ($chapters as $ch) {
                                $chId = $ch['ID_Chapter'];
                                $chNum = $ch['Chapter_Number'];
                                $chUrl = $redirectType . '/' . $redirectType . '-read.php'
                                    . '?type=' . $type
                                    . '&id=' . $id
                                    . '&idChapter=' . $chId
                                    . '&numberChapter=' . $chNum;
                                ?>
                                <li class="chapter-row">
                                    <a class="chapter-item" href="<?php echo $chUrl; ?>">
                                        <span class="chapter-dot" aria-hidden="true"></span>
                                        <span class="chapter-info">
                                            <span class="chapter-title">
                                                Capítulo <?php echo $chNum; ?>
                                            </span>
                                            <?php if (!empty($ch['Title'])) { ?>
                                                <span class="chapter-subtitle">
                                                    <?php echo htmlspecialchars($ch['Title']); ?>
                                                </span>
                                            <?php } ?>
                                        </span>
                                    </a>
                                    <?php if (isPromoter()) { ?>
                                        <a class="btn btn-add btn-small" href="edit-chapter.php?type=<?php echo urlencode($type); ?>&id=<?php echo $id; ?>&idChapter=<?php echo $chId; ?>&numberChapter=<?php echo $chNum; ?>">
                                            Editar
                                        </a>
                                    <?php } ?>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </section>

                <!-- Aside -->
                <aside class="event-aside" aria-labelledby="datos-work">
                    <img class="aside-cover" src="<?php echo htmlspecialchars($coverUrl); ?>"
                        alt="Portada de <?php echo htmlspecialchars($title); ?>">

                    <?php if (!empty($description)) { ?>
                        <p class="work-description"><?php echo htmlspecialchars($description); ?></p>
                    <?php } ?>

                    <h3 id="datos-work">Datos</h3>
                    <dl>
                        <dt>Tipo</dt>
                        <dd><?php echo htmlspecialchars($type); ?></dd>

                        <?php if (!empty($premiere)) { ?>
                            <dt>Estreno</dt>
                            <dd><?php echo htmlspecialchars($premiere); ?></dd>
                        <?php } ?>

                        <?php if (!empty($studio)) { ?>
                            <dt>Estudio</dt>
                            <dd><?php echo htmlspecialchars($studio); ?></dd>
                        <?php } ?>

                        <?php if (!empty($gender)) { ?>
                            <dt>Género</dt>
                            <dd><?php echo htmlspecialchars($gender); ?></dd>
                        <?php } ?>

                        <?php if (!empty($chapters)) { ?>
                            <dt>Capítulos</dt>
                            <dd><?php echo count($chapters); ?></dd>
                        <?php } else {
                            echo "Sin capitulos";
                        } ?>
                    </dl>

                    <div class="stack-actions">
                        <?php if (!empty($chapters)) {
                            $firstUrl = $redirectType . '/' . $redirectType . '-read.php'
                                . '?type=' . $type
                                . '&id=' . $id
                                . '&idChapter=' . $chapters[0]['ID_Chapter']
                                . '&numberChapter=' . $chapters[0]['Chapter_Number'];
                            ?>
                            <a href="<?php echo $firstUrl; ?>" class="btn btn-add">
                                Empezar a ver
                            </a>
                        <?php } ?>

                        <?php if (isPromoter()) { ?>
                            <a href="add-chapter.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>" class="btn btn-add">
                                Subir capitulo
                            </a>
                            <a href="work-edit.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>" class="btn btn-add">
                                Editar obra
                            </a>
                        <?php } ?>
                    </div>
                </aside>

            </article>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>
</body>

</html>