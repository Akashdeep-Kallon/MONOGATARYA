<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/CatalogController.php';
requireRole('promoter');

$type = $_GET['type'];
$id = $_GET['id'];

$result = (new Catalog())->returnWorkDetail($id, $type);

$title = $result['title'];
$subtitle = $result['subtitle'];
$image = $result['image'];
$trailer = $result['trailer'];
$description = $result['description'];
$premiere = $result['premiere'];
$studio = $result['studio'];
$gender = $result['gender'];
$chapters = $result['chapters'];
$active = $result['active'];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../assets/styles/main.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Editar Obra</title>
</head>

<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <section class="card-panel" aria-labelledby="crear-evento-title">
                <h2 id="crear-evento-title" class="section-title">Formulario de creación de obra</h2>

                <form class="form-vertical" action="<?php echo CONTROLLER_URL; ?>/CatalogController.php" method="post"
                    enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

                    <div class="field-group">
                        <label for="tipo-obra">Tipo</label>
                        <input id="title" type="text" name="type" value="<?php echo $type; ?>" readonly>
                    </div>

                    <div class="field-group">
                        <label for="title">Título de la obra</label>
                        <input id="title" type="text" name="title" value="<?php echo htmlspecialchars($title); ?>"
                            required minlength="1" maxlength="50">
                    </div>

                    <div class="field-group">
                        <label for="subtitle">Subtítulo</label>
                        <input id="subtitle" type="text" name="subtitle"
                            value="<?php echo htmlspecialchars($subtitle); ?>" required minlength="5" maxlength="75">
                    </div>

                    <div class="field-group">
                        <label for="image-file" class="file-label">Subir imagen de portada (Png, Jpg, Webp...)</label>
                        <input id="image-file" type="file" accept="image/*" name="image_file">
                    </div>

                    <div class="field-group">
                        <label for="image-url">URL de la imagen de portada (opcional)</label>
                        <input id="image-url" type="text" name="image_url"
                            value="<?php echo htmlspecialchars($image); ?>">
                    </div>

                    <div class="field-group">
                        <label for="video">Subir tráiler (opcional)</label>
                        <input id="video" type="file" name="video" accept="video/*">
                    </div>

                    <div class="field-group">
                        <label for="premiere_date">Fecha de estreno</label>
                        <input id="premiere_date" type="date" name="premiere_date" value="<?php echo $premiere; ?>"
                            required>
                    </div>

                    <div class="field-group">
                        <label for="studio">Estudio / plataforma</label>
                        <input id="studio" type="text" name="studio" value="<?php echo htmlspecialchars($studio); ?>"
                            max="25" required>
                    </div>

                    <div class="field-group">
                        <label for="gender">Género</label>
                        <input id="gender" type="text" name="gender" value="<?php echo htmlspecialchars($gender); ?>"
                            max="50" required>
                    </div>

                    <div class="field-group">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description" maxlength="500"><?php
                        echo htmlspecialchars($description);
                        ?></textarea>
                    </div>

                    <div class="field-group">
                        <label>Marcar la obra como activa</label>
                        <input id="remember" name="active" type="checkbox" <?php echo ($active == 1 ? 'checked' : ''); ?>>
                    </div>

                    <div class="inline-actions">
                        <button type="submit" class="btn btn-add" name="edit_work">Guardar cambios</button>
                        <button type="submit" class="btn btn-delete" name="delete_work">Eliminar Obra</button>
                        <button type="reset" class="btn btn-delete" name="">Reiniciar</button>
                    </div>
                </form>
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/message.php'; ?>
            </section>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>

</body>

</html>
