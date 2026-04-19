<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
?>

<footer class="site-footer">
    <nav class="container" aria-label="Mapa web del sitio">
        <ul class="footer-links">
            <li><a href="<?php echo VIEW_URL; ?>/index.php">Inicio</a></li>
            <li><a href="<?php echo VIEW_URL; ?>/profile.php">Perfil</a></li>
            <li><a href="<?php echo VIEW_URL; ?>/catalogs/anime/anime-catalog.php">Catálogo de animes</a></li>
            <li><a href="<?php echo VIEW_URL; ?>/catalogs/manga/manga-catalog.php">Catálogo de mangas</a></li>
            <li><a href="<?php echo VIEW_URL; ?>/catalogs/events/event-detail.php">Eventos</a></li>
            <li class="logout">
                <form action="<?php echo CONTROLLER_URL; ?>/UserController.php" method="POST">
                    <input type="hidden" name="logout">
                    <button type="submit">Home</button>
                </form>
            </li>
        </ul>
    </nav>


    <p class="footer-legal">© 2026 Monogatarya. Todos los derechos reservados.</p>
</footer>