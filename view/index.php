<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/database.php';

$db = (new Database())->getConnection();
$promotorsQuery = $db->query("SELECT name, surname, bio, avatar FROM Users WHERE status = 1 ORDER BY name, surname");
$promotors = $promotorsQuery->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php
    $assets = $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/assets';
    ?>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/main.css?v=<?php echo filemtime("$assets/styles/main.css"); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/index.css?v=<?php echo filemtime("$assets/styles/index.css"); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css?v=<?php echo filemtime("$assets/styles/catalog.css"); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/slick/slick.css?v=<?php echo filemtime("$assets/slick/slick.css"); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/slick/slick-theme.css?v=<?php echo filemtime("$assets/slick/slick-theme.css"); ?>" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <script src="<?php echo ASSETS_URL; ?>/js/jquery.js?v=<?php echo filemtime("$assets/js/jquery.js"); ?>" defer></script>
    <script src="<?php echo ASSETS_URL; ?>/slick/slick.min.js?v=<?php echo filemtime("$assets/slick/slick.min.js"); ?>" defer></script>
    <script src="<?php echo ASSETS_URL; ?>/js/hover.js?v=<?php echo filemtime("$assets/js/hover.js"); ?>" defer></script>
    <script src="<?php echo ASSETS_URL; ?>/js/slider_noSlick.js?v=<?php echo filemtime("$assets/js/slider1.js"); ?>" defer></script>
    <script src="<?php echo ASSETS_URL; ?>/js/slider1.js?v=<?php echo filemtime("$assets/js/slider1.js"); ?>" defer></script>
    <script src="<?php echo ASSETS_URL; ?>/js/slider2.js?v=<?php echo filemtime("$assets/js/slider2.js"); ?>" defer></script>
    <title>Monogatarya - Página principal</title>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main" id="contenido-principal">
        <div class="layout-container">
            <section class="card-panel home-hero" aria-labelledby="hero-title">
                <div>
                    <h2 id="hero-title">Bienvenido a Monogatarya</h2>
                    <p>Descubre novedades de manga y anime, gestiona tu perfil y reserva eventos desde una experiencia
                        responsive y accesible.</p>
                    <div class="inline-actions">
                        <a class="btn-link" href="catalogs/events/event-detail.php">Ver evento destacado</a>
                        <a class="btn-link" href="catalogs/anime/anime-catalog.php">Explorar catálogo</a>
                    </div>
                </div>
                <div class="gallery" id="heroGallery" aria-label="Galería destacada" aria-roledescription="carrusel">
                    <div class="cards">
                        <div class="card" data-index="0" aria-label="Portada de One Piece">
                            <img src="<?php echo ASSETS_URL; ?>/gallery/card-onePiece.webp" alt="Portada de One Piece">
                        </div>
                        <div class="card" data-index="1" aria-label="Portada de Dragon Ball Z">
                            <img src="<?php echo ASSETS_URL; ?>/gallery/card-dragonBall.webp"
                                alt="Portada de Dragon Ball Z">
                        </div>
                        <div class="card" data-index="2" aria-label="Portada de Attack on Titan">
                            <img src="<?php echo ASSETS_URL; ?>/gallery/card-shingekyNoKyojin.webp"
                                alt="Portada de Attack on Titan">
                        </div>
                    </div>
                    <div class="gallery-dots" aria-label="Navegación del carrusel"></div>
                </div>

            </section>

            <section class="card-panel" aria-labelledby="ultimos-title">
                <h2 id="ultimos-title" class="section-title">Últimos lanzamientos</h2>
                <div class="card-grid card-grid-3">
                    <article class="content-card">
                        <h3>One Piece 112</h3>
                        <p>Nuevo arco argumental con edición especial y análisis editorial.</p>
                    </article>
                    <article class="content-card">
                        <h3>Dragon Ball Daima</h3>
                        <p>Nueva temporada disponible con calendario completo de emisión.</p>
                    </article>
                    <article class="content-card">
                        <h3>Shingeki Final</h3>
                        <p>Reedición coleccionista y debate de comunidad en el próximo evento.</p>
                    </article>
                </div>
            </section>

            <!-- Slider Promotors (Slick) -->
            <section class="card-panel promotors-panel" aria-labelledby="promotors-title">
                <h2 id="promotors-title" class="section-title">Nuestros promotores</h2>
                <?php if (!empty($promotors)) { ?>
                    <div class="sliderPromotors" aria-label="Carrusel de promotores">
                        <?php foreach ($promotors as $promotor) {
                            $name = trim($promotor['name'] ?? '');
                            $surname = trim($promotor['surname'] ?? '');
                            $fullName = trim($name . ' ' . $surname);
                            $bio = trim($promotor['bio'] ?? '');
                            ?>
                            <div class="promotor-slide">
                                <article class="slider-item-promotor">
                                    <div class="promotor-logo" aria-hidden="true">
                                        <?php if (!empty($promotor['avatar'])) { ?>
                                            <img src="<?php echo USER_URL . htmlspecialchars($promotor['avatar']); ?>"
                                                alt="">
                                        <?php } else { ?>
                                            <svg class="promotor-logo-fallback" viewBox="0 0 478 522" aria-label="Logo de <?php echo htmlspecialchars($fullName ?: 'promotor'); ?>">
                                                <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#usuario"></use>
                                            </svg>
                                        <?php } ?>
                                    </div>
                                    <div class="promotor-content">
                                        <span class="promotor-kicker">Promotor</span>
                                        <h3><?php echo htmlspecialchars($fullName ?: 'Promotor de Monogatarya'); ?></h3>
                                        <p><?php echo htmlspecialchars($bio ?: 'Promotor de Monogatarya. Pronto compartira su biografia.'); ?></p>
                                    </div>
                                </article>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <article class="promotors-empty">
                        <div class="promotor-logo" aria-hidden="true">
                            <svg class="promotor-logo-fallback" viewBox="0 0 478 522" aria-label="Logo de Monogatarya">
                                <use href="<?php echo ASSETS_URL; ?>/img/icon-sprites.svg#usuario"></use>
                            </svg>
                        </div>
                        <div class="promotor-content">
                            <span class="promotor-kicker">Promotores</span>
                            <h3>Equipo Monogatarya</h3>
                            <p>Pronto se mostraran aqui los promotores con sus logos y biografias.</p>
                        </div>
                    </article>
                <?php } ?>
            </section>

            <?php require __DIR__ . '/catalogs/events/event-global-catalog.php'; ?>

        </div>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>
</body>

</html>
