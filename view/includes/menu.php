<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
?>

<input type="checkbox" id="menu-toggle">

<ul class="menu-sidebar">
    <li><a href="<?php echo VIEW_URL; ?>/index.php">Página de inicio</a></li>

    <li><a href="<?php echo VIEW_URL; ?>/catalogs/anime/anime-catalog.php">
            Catálogo de Animes
        </a></li>

    <li><a href="<?php echo VIEW_URL; ?>/catalogs/manga/manga-catalog.php">
            Catálogo de Mangas
        </a></li>

    <li><a href="<?php echo VIEW_URL; ?>/catalogs/events/event-catalog.php">
            Catálogo de Eventos
        </a></li>

    <?php if (isLogged()){ ?>
        <li class="logout">
            <form action="<?php echo CONTROLLER_URL; ?>/UserController.php" method="POST">
                <input type="hidden" name="logout">
                <button type="submit">Cerrar sesión</button>
            </form>
        </li>
    <?php } ?>

</ul>