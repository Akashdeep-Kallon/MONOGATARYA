<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/main.css" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/index.css" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
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
                            <img src="<?php echo ASSETS_URL; ?>/gallery/card-dragonBall.webp" alt="Portada de Dragon Ball Z">
                        </div>
                        <div class="card" data-index="2" aria-label="Portada de Attack on Titan">
                            <img src="<?php echo ASSETS_URL; ?>/gallery/card-shingekyNoKyojin.webp" alt="Portada de Attack on Titan">
                        </div>
                    </div>
                    <div class="gallery-dots" aria-label="Navegación del carrusel"></div>
                </div>

                <script>
                    (function() {
                        const gallery = document.getElementById('heroGallery');
                        const cards = Array.from(gallery.querySelectorAll('.card'));
                        const dotsWrap = gallery.querySelector('.gallery-dots');
                        const TOTAL = cards.length;
                        let current = 0;
                        let autoTimer = null;

                        // Crear dots de navegación
                        const dots = cards.map((_, i) => {
                            const btn = document.createElement('button');
                            btn.className = 'gallery-dot';
                            btn.setAttribute('aria-label', `Mostrar portada ${i + 1}`);
                            btn.addEventListener('click', () => {
                                stopAutoplay();
                                goTo(i);
                                startAutoplay();
                            });
                            dotsWrap.appendChild(btn);
                            return btn;
                        });

                        function goTo(index) {
                            const prev = (index - 1 + TOTAL) % TOTAL;
                            const next = (index + 1) % TOTAL;

                            cards.forEach(c => {
                                c.classList.remove('is-active', 'is-prev', 'is-next');
                            });
                            dots.forEach(d => d.classList.remove('is-active'));

                            cards[index].classList.add('is-active');
                            cards[prev].classList.add('is-prev');
                            cards[next].classList.add('is-next');
                            dots[index].classList.add('is-active');

                            current = index;
                        }

                        function startAutoplay() {
                            autoTimer = setInterval(() => goTo((current + 1) % TOTAL), 3500);
                        }

                        function stopAutoplay() {
                            clearInterval(autoTimer);
                        }

                        // Clic en carta lateral → avanza al slide
                        cards.forEach((card, i) => card.addEventListener('click', () => {
                            stopAutoplay();
                            goTo(i);
                            startAutoplay();
                        }));

                        // Soporte swipe táctil
                        let touchStartX = 0;
                        gallery.addEventListener('touchstart', e => {
                            touchStartX = e.touches[0].clientX;
                            stopAutoplay();
                        }, {
                            passive: true
                        });
                        gallery.addEventListener('touchend', e => {
                            const diff = touchStartX - e.changedTouches[0].clientX;
                            if (Math.abs(diff) > 40)
                                goTo(diff > 0 ? (current + 1) % TOTAL : (current - 1 + TOTAL) % TOTAL);
                            startAutoplay();
                        }, {
                            passive: true
                        });

                        // Pausar autoplay con hover
                        gallery.addEventListener('mouseenter', stopAutoplay);
                        gallery.addEventListener('mouseleave', startAutoplay);

                        goTo(0);
                        startAutoplay();
                    })();
                </script>
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

            <?php require __DIR__ . '/catalogs/events/event-global-catalog.php'; ?>

        </div>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>
</body>

</html>