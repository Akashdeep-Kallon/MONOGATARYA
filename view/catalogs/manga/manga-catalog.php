<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../../assets/styles/main.css" />
    <link rel="stylesheet" href="../../assets/styles/catalog.css" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Mangas</title>
</head>

<body>
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/CatalogController.php';

    $result = (new Catalog())->returnCatalog('Works', 'Manga');
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
                    <h2 id="catalogo-title" class="section-title">Catálogo de Mangas</h2>
                    <?php if (isPromoter()) { ?>
                        <a class="btn btn-add" href="../work-create.php?type=Manga">Añadir Manga</a>
                    <?php } ?>
                </div>
                <!-- Tarjetas de esta página -->
                <div class="card-grid card-grid-3">
                    <?php while ($manga = $query->fetch()) {
                        // Si la BD tiene columna de imagen úsala; si no, placeholder
                        $img = getCoverImageUrl($manga['Image'], 'Manga');
                        $title = htmlspecialchars($manga['Title']);
                        $subtitle = htmlspecialchars($manga['Subtitle']);
                        $id = $manga['ID_Work'];
                        $active = $manga['Active'];
                        ?>

                        <article class="content-card">
                            <img class="card-image" src="<?php echo htmlspecialchars($img); ?>"
                                alt="Portada de <?php echo $title; ?>">
                            <h3><?php echo $title; ?></h3>
                            <p><?php echo $subtitle; ?></p>
                            <?php if ($active || isPromoter()) { ?>
                                <a class="btn-link" href="../work-detail.php?type=Manga&id=<?php echo $id; ?>">
                                    Leer Manga
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