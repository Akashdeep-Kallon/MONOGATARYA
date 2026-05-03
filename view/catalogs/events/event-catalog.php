<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/main.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Eventos</title>
</head>

<body>
    <?php $showSearch = true;
    include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>
    <main class="page-main">
        <div class="layout-container">

            <?php require 'event-global-catalog.php'; ?>

        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>
</body>

</html>