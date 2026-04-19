<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/CatalogController.php';
requireRole('promoter');

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
$active = $result['active'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../assets/styles/main.css" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Editar Evento</title>
</head>

<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <section class="card-panel" aria-labelledby="editar-evento-title">
                <h2 id="editar-evento-title" class="section-title">Editar evento</h2>

                <form class="form-vertical" action="<?php echo CONTROLLER_URL; ?>/CatalogController.php" method="post"
                    enctype="multipart/form-data">

                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

                    <div class="field-group">
                        <label for="nombre-evento">Título</label>
                        <input id="nombre-evento" type="text" name="title" required minlength="5" maxlength="50"
                            value="<?php echo htmlspecialchars($title); ?>">
                    </div>

                    <div class="field-group">
                        <label for="subtitle">Subtítulo</label>
                        <input id="subtitle" type="text" name="subtitle" required minlength="5" maxlength="75"
                            value="<?php echo htmlspecialchars($subtitle); ?>">
                    </div>

                    <div class="field-group">
                        <label for="fecha-evento">Fecha</label>
                        <input id="fecha-evento" type="date" name="date_event" required
                            value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($premiere))); ?>">
                    </div>

                    <div class="field-group">
                        <label for="lugar-evento">Lugar</label>
                        <input id="lugar-evento" type="text" name="location" required minlength="1" maxlength="150"
                            value="<?php echo htmlspecialchars($location); ?>">
                    </div>

                    <div class="field-group">
                        <label for="aforo">Aforo</label>
                        <input id="aforo" type="number" name="capacity" min="50" required
                            value="<?php echo htmlspecialchars($capacity); ?>">
                    </div>

                    <div class="field-group">
                        <label for="image-file" class="file-label">Subir nueva imagen de portada</label>
                        <input id="image-file" type="file" accept="image/*" name="image_file">
                    </div>

                    <div class="field-group">
                        <label for="image-url">URL de la imagen actual</label>
                        <input id="image-url" type="text" name="image_url"
                            value="<?php echo htmlspecialchars($image); ?>">
                    </div>

                    <div class="field-group">
                        <label for="video-file" class="file-label">Subir nuevo vídeo</label>
                        <input id="video-file" type="file" accept="video/*" name="video_file">
                    </div>

                    <div class="field-group">
                        <label for="video-url">URL del vídeo actual</label>
                        <input id="video-url" type="text" name="video_url"
                            value="<?php echo htmlspecialchars($video); ?>">
                    </div>

                    <div class="field-group">
                        <label for="t-video">Transcripción del vídeo</label>
                        <textarea id="t-video" name="t_video" minlength="5" maxlength="5000"><?php
                        echo htmlspecialchars($t_Video);
                        ?></textarea>
                    </div>

                    <div class="field-group">
                        <label for="audio-file" class="file-label">Subir nuevo audio</label>
                        <input id="audio-file" type="file" accept="audio/*" name="audio_file">
                    </div>

                    <div class="field-group">
                        <label for="audio-url">URL del audio actual</label>
                        <input id="audio-url" type="text" name="audio_url"
                            value="<?php echo htmlspecialchars($audio); ?>">
                    </div>

                    <div class="field-group">
                        <label for="t-audio">Transcripción del audio</label>
                        <textarea id="t-audio" name="t_audio" minlength="5" maxlength="5000"><?php
                        echo htmlspecialchars($t_Audio);
                        ?></textarea>
                    </div>

                    <div class="field-group">
                        <label for="descripcion-evento">Descripción</label>
                        <textarea id="descripcion-evento" name="description" required minlength="10" maxlength="500"><?php
                        echo htmlspecialchars($description);
                        ?></textarea>
                    </div>

                    <div class="field-group">
                        <label>Activar evento</label>
                        <input id="remember" name="active" type="checkbox" <?php echo ($active == 1 ? 'checked' : ''); ?>>
                    </div>

                    <div class="inline-actions">
                        <button type="submit" class="btn btn-add" name="edit_event">Guardar cambios</button>
                        <button type="submit" class="btn btn-delete" name="delete_event">Eliminar Evento</button>
                        <button type="reset" class="btn btn-delete">Reiniciar</button>
                    </div>
                </form>
                <?php if (!empty($_SESSION['login_error'])) { ?>
                    <div class="error-box">
                        <span class="icon">ⓘ</span>
                        <span>
                            <?php
                            foreach ($_SESSION['login_error'] as $error) {
                                echo htmlspecialchars($error) . "<br>";
                            }
                            ?>
                        </span>
                    </div>
                    <?php unset($_SESSION['login_error']); ?>
                <?php } ?>
            </section>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>

</body>

</html>