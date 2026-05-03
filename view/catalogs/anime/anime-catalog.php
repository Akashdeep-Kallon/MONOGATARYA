<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php $assets = $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/assets'; ?>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/main.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Animes</title>
</head>

<body>
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/CatalogController.php';

    $result = (new Catalog())->returnCatalog('Works', 'Anime');
    $query = $result['query'];
    $page = $result['page'];
    $totalPages = $result['totalPages'];
    ?>

    <?php $showSearch = true;
    include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <section class="card-panel" aria-labelledby="catalogo-title">

                <div class="section-header">
                    <h2 id="catalogo-title" class="section-title">Catálogo de Animes</h2>
                    <?php if (isPromoter()) { ?>
                        <a class="btn btn-add" href="../work-create.php?type=Anime">Añadir Anime</a>
                    <?php } ?>
                </div>
                <!-- Tarjetas de esta página -->
                <div class="card-grid card-grid-3">
                    <?php while ($anime = $query->fetch()) {
                        // Si la BD tiene columna de imagen úsala; si no, placeholder
                        $img = getCoverImageUrl($anime['Image'], 'Anime');
                        $title = htmlspecialchars($anime['Title']);
                        $subtitle = htmlspecialchars($anime['Subtitle']);
                        $id = $anime['ID_Work'];
                        $active = $anime['Active'];
                        ?>

                        <article class="content-card">
                            <img class="card-image" src="<?php echo htmlspecialchars($img); ?>"
                                alt="Portada de <?php echo $title; ?>">
                            <h3><?php echo $title; ?></h3>
                            <p><?php echo $subtitle; ?></p>
                            <?php if ($active || isPromoter()) { ?>
                                <a class="btn-link" href="../work-detail.php?type=Anime&id=<?php echo $id; ?>">
                                    Ver Anime
                                </a>
                            <?php } else { ?>
                                <button class="btn-link btn-muted" type="button" disabled>
                                    Próximamente
                                </button>
                            <?php } ?>
                        </article>
                    <?php } ?>
                </div>

                <?php require __DIR__ . '/../../includes/pagination.php'; ?>

            </section>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>
</body>

</html>