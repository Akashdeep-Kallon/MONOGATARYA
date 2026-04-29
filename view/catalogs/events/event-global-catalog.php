<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/CatalogController.php';

$result = (new Catalog())->returnCatalog('Events', NULL);
$query = $result['query'];
$page = $result['page'];
$totalPages = $result['totalPages'];
?>

<section class="card-panel" aria-labelledby="catalogo-title">

    <div class="section-header">
        <h2 id="catalogo-title" class="section-title">Catálogo de Eventos</h2>
        <?php if (isPromoter()) { ?>
            <a class="btn btn-add" href="<?php echo VIEW_URL; ?>/catalogs/events/event-create.php">Añadir Evento</a>
        <?php } ?>
    </div>

    <!-- Tarjetas -->
    <div class="card-grid card-grid-3">
        <?php while ($event = $query->fetch()) {
            $img = !empty($event['Image']) ? EVENT_URL . htmlspecialchars($event['Image']) : ASSETS_URL . '/img/background-image.webp';
            $title = htmlspecialchars($event['Title']);
            $subtitle = htmlspecialchars($event['Subtitle']);
            $id = $event['ID_Event'];
            $active = $event['Active'];
            ?>
            <article class="content-card">
                <img class="card-image" src="<?php echo $img; ?>" alt="Cartel <?php echo $title; ?>">
                <h3><?php echo $title; ?></h3>
                <p><?php echo $subtitle; ?></p>

                <?php if ($active || isPromoter()) { ?>
                    <a class="btn-link" href="event-detail.php?id=<?php echo $id; ?>">
                        Más información
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