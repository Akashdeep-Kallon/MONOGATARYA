<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
?>
 
<div id="menu-overlay" class="menu-overlay" aria-hidden="true"></div>
 
<ul class="menu-sidebar" id="menuSidebar" role="dialog" aria-modal="true" aria-label="Menú de navegación" aria-hidden="true">
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
 
 
<script>
(function () {
    const sidebar  = document.getElementById('menuSidebar');
    const overlay  = document.getElementById('menu-overlay');
    const toggleBtn = document.querySelector('[data-menu-toggle]');
 
    function openMenu() {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-open');
        sidebar.setAttribute('aria-hidden', 'false');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('menu-open');
        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
    }
 
    function closeMenu() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-open');
        sidebar.setAttribute('aria-hidden', 'true');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('menu-open');
        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
    }
 
    function toggleMenu() {
        sidebar.classList.contains('is-open') ? closeMenu() : openMenu();
    }
 
    // Botón hamburguesa del header
    if (toggleBtn) toggleBtn.addEventListener('click', toggleMenu);
 
    // Cerrar al hacer clic en el overlay
    overlay.addEventListener('click', closeMenu);
 
    // Cerrar con Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && sidebar.classList.contains('is-open')) closeMenu();
    });
 
    // Cerrar al navegar a otra página
    sidebar.querySelectorAll('a').forEach(link =>
        link.addEventListener('click', closeMenu)
    );
 
    // Exponer función global para el botón del header
    window.toggleMenu = toggleMenu;
})();
</script>