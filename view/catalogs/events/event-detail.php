<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/CatalogController.php';

$id = $_GET['id'];

$result = (new Catalog())->eventDetail($id);

$title = $result['title'];
$subtitle = $result['subtitle'];

$image = $result['image'];
$video = $result['video'];
$audio = $result['audio'];
$t_Video = $result['t_Video'];
$t_Audio = $result['t_Audio'];

$description = $result['description'];
$premiere = $result['premiere'];
$location = $result['location'];
$capacity = $result['capacity'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/main.css" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Evento</title>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">

            <article class="card-panel event-layout" aria-labelledby="evento-titulo">

                <section>
                    <h2 id="evento-titulo" class="section-title">
                        <?php echo htmlspecialchars($title); ?>
                    </h2>

                    <figure class="event-hero-image">

                        <img src="<?php echo !empty($image) ? EVENT_URL . htmlspecialchars($image) : ASSETS_URL . '/img/background-image.webp'; ?>"
                            alt="Imagen del evento <?php echo htmlspecialchars($title); ?>">
                    </figure>

                    <h3>Descripción</h3>
                    <p><?php echo nl2br(htmlspecialchars($description)); ?></p>

                    <?php if (!empty($video)): ?>
                        <h3>Vídeo del evento</h3>
                        <video controls class="event-video">
                            <source src="<?php echo htmlspecialchars($video); ?>" type="video/mp4">
                            Tu navegador no soporta reproducción de vídeo.
                        </video>

                        <?php if (!empty($t_Video)): ?>
                            <h4>Transcripción del vídeo</h4>
                            <p><?php echo nl2br(htmlspecialchars($t_Video)); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($audio)): ?>
                        <h3>Audio del evento</h3>
                        <audio controls>
                            <source src="<?php echo htmlspecialchars($audio); ?>" type="audio/mpeg">
                            Tu navegador no soporta reproducción de audio.
                        </audio>

                        <?php if (!empty($t_Audio)): ?>
                            <h4>Transcripción del audio</h4>
                            <p><?php echo nl2br(htmlspecialchars($t_Audio)); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>

                </section>

                <aside class="event-aside" aria-labelledby="datos-evento">
                    <h3 id="datos-evento">Datos del evento</h3>

                    <dl>
                        <dt>Fecha</dt>
                        <dd><?php echo date("d/m/Y", strtotime($premiere)); ?></dd>

                        <dt>Ubicación</dt>
                        <dd><?php echo htmlspecialchars($location); ?></dd>

                        <dt>Aforo</dt>
                        <dd><?php echo htmlspecialchars($capacity); ?> asistentes</dd>
                    </dl>

                    <div class="stack-actions">
                        <button type="button" class="btn btn-add">Reservar plaza</button>
                        <button type="button" class="btn btn-add">Anular reserva</button>
                        <?php if (isPromoter()) { ?>
                            <a href="<?php echo VIEW_URL; ?>/event/event-edit.php?id=<?php echo $id; ?>"
                                class="btn btn-add">
                                Editar evento
                            </a>
                            <a href="<?php echo CONTROLLER_URL; ?>/CatalogController.php?delete_event=<?php echo $id; ?>"
                                class="btn btn-delete">
                                Eliminar evento
                            </a>
                        <?php } ?>
                    </div>
                </aside>

            </article>

        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>
</body>

</html>