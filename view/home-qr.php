<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/home-qr.css" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Inicio</title>
</head>

<body>

    <header>
        <div class="header-group">
            <a href="<?php echo VIEW_URL; ?>/home.php" class="logo-link" aria-label="Volver a inicio">
                <img src="<?php echo ASSETS_URL; ?>/img/logo.webp" alt="Logo de la página Monogatarya">
            </a>
            <h1>MONOGATARYA</h1>
            <div class="right-group">
                <form action="auth/login.php">
                    <button class="btn btn-session" type="submit">Iniciar Sesión</button>
                </form>
                <form action="auth/register.html">
                    <button class="btn btn-register" type="submit">Registrarse</button>
                </form>
            </div>
        </div>
    </header>

    <main>
        <div class="main-layout">
            <div class="main-left">
                <section class="hero" aria-labelledby="hero-title">
                    <h2 id="hero-title">El mayor catálogo de manga y anime del mundo</h2>
                    <p>Explora estrenos, eventos y perfiles de la comunidad en cualquier dispositivo.</p>
                </section>

                <div class="home-actions">
                    <a href="index.php" class="btn-ver-pagina">Ver la página</a>
                </div>
            </div>

            <aside class="qr-panel" aria-label="Código QR de la app">
                <div class="qr-card">
                    <p class="qr-label">Escanea y accede</p>
                    <img src="<?php echo ASSETS_URL; ?>/img/qr.jpg" alt="Código QR de Monogatarya" class="qr-img" />
                    <p class="qr-sub">Llévate Monogatarya a tu móvil</p>
                </div>
            </aside>
        </div>

        <footer>© 2026 Monogatarya. Todos los derechos reservados.</footer>
    </main>

</body>

</html>
