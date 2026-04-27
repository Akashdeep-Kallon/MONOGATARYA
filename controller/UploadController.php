<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/model/User.php';

class UploadController
{
    private $connection;
    private $animeLocation = '/var/www/uploads/Anime/';
    private $eventLocation = '/var/www/uploads/Event/';
    private $mangaLocation = '/var/www/uploads/Manga/';
    private $userLocation  = '/var/www/uploads/User/';

    public function __construct()
    {
        $this->connection = (new Database())->getConnection();
    }

    // Usuario

    public function uploadAvatar($user, $avatar)
    {
        $errors = [];

        $type        = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
        $userId      = $user->getUserID();
        $destination = $this->userLocation . $userId . '/';
        $file_dest   = $destination . 'avatar.' . $type;

        if ($avatar['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error al recibir el archivo.";
        }
        $check = getimagesize($avatar['tmp_name']);
        if ($check === false) {
            $errors[] = "El archivo no es una imagen.";
        }
        if ($avatar['size'] > 5000000) {
            $errors[] = "La imagen es demasiado grande. Máximo 5MB.";
        }
        if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
            $errors[] = "Solo se permiten archivos JPG, JPEG, PNG y WEBP.";
        }
        if (!empty($errors)) {
            $errors[] = "La imagen no se ha subido.";
            return $errors;
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        if (move_uploaded_file($avatar['tmp_name'], $file_dest)) {
            $rutaBD = $userId . '/avatar.' . $type;
            $user->updateAvatar($rutaBD);
            return "Imagen subida correctamente.";
        }
        return ["Hubo un error al mover el archivo."];
    }

    public function deleteUserUploads($userId)
    {
        $userDir = $this->userLocation . $userId . '/';
        if (is_dir($userDir)) {
            $archivos = glob($userDir . '*');
            foreach ($archivos as $archivo) {
                unlink($archivo);
            }
            rmdir($userDir);
        }
    }

    // Obras (Anime / Manga)

    /**
     * Sube la imagen de portada o el tráiler de una obra.
     * Devuelve string en éxito, array de errores en fallo.
     */
    public function uploadWork($id, $type, $media)
    {
        $errors = [];

        // 1. Error de subida del servidor
        if ($media['error'] !== UPLOAD_ERR_OK) {
            if ($media['error'] === UPLOAD_ERR_INI_SIZE || $media['error'] === UPLOAD_ERR_FORM_SIZE) {
                return ["El archivo supera el tamaño máximo permitido."];
            }
            if ($media['error'] === UPLOAD_ERR_NO_FILE) {
                return ["No se recibió ningún archivo."];
            }
            return ["Error al recibir el archivo (código: {$media['error']})."];
        }

        $mime    = mime_content_type($media['tmp_name']);
        $isImage = str_starts_with($mime, 'image/');
        $isVideo = str_starts_with($mime, 'video/');

        $destination = ($type === 'Anime')
            ? $this->animeLocation . $id . '/'
            : $this->mangaLocation . $id . '/';

        if ($isImage) {
            if ($media['size'] > 5000000) {
                $errors[] = "La imagen es demasiado grande. Máximo 5MB.";
            }
            $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $errors[] = "La imagen debe ser JPG, JPEG, PNG o WEBP.";
            }
            $finalName = "banner.$ext";
            $column    = "Image";

        } elseif ($isVideo) {
            if ($media['size'] > 50000000) {
                $errors[] = "El vídeo es demasiado grande. Máximo 50MB.";
            }
            $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['mp4', 'webm', 'mov', 'mkv'])) {
                $errors[] = "El tráiler debe ser MP4, WEBM, MOV o MKV.";
            }
            $finalName = "trailer.$ext";
            $column    = "Trailer";

        } else {
            return ["El archivo no es una imagen ni un vídeo válido."];
        }

        if (!empty($errors)) {
            return $errors;
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $finalPath = $destination . $finalName;
        if (!move_uploaded_file($media['tmp_name'], $finalPath)) {
            return ["Error al mover el archivo al directorio destino."];
        }

        $rutaBD = $id . '/' . $finalName;
        $this->connection->query("UPDATE Works SET $column = '$rutaBD' WHERE ID_Work = '$id';");

        return $isImage ? "Banner subido correctamente." : "Tráiler subido correctamente.";
    }

    public function deleteWorkUploads($id, $type)
    {
        $dir = ($type === 'Anime')
            ? $this->animeLocation . $id . '/'
            : $this->mangaLocation . $id . '/';

        if (is_dir($dir)) {
            $this->deleteDir($dir);
        }
    }

    // Capítulos

    /**
     * Sube el archivo de un capítulo (vídeo para Anime, ZIP para Manga).
     * Devuelve string en éxito, array de errores en fallo.
     */
    public function uploadChapter($idWork, $idChapter, $type, $media)
    {
        if ($media['error'] !== UPLOAD_ERR_OK) {
            if ($media['error'] === UPLOAD_ERR_INI_SIZE || $media['error'] === UPLOAD_ERR_FORM_SIZE) {
                return ["El archivo supera el tamaño máximo permitido."];
            }
            return ["Error al recibir el archivo (código: {$media['error']})."];
        }

        $mime         = mime_content_type($media['tmp_name']);
        $basePath     = ($type === 'Anime')
            ? $this->animeLocation . $idWork . '/'
            : $this->mangaLocation . $idWork . '/';
        $chaptersPath  = $basePath . "chapters/";
        $extractFolder = $chaptersPath . $idChapter . "/";

        if (!is_dir($extractFolder)) {
            mkdir($extractFolder, 0755, true);
        }

        // Anime: vídeo 
        if ($type === 'Anime') {
            $isVideo = str_starts_with($mime, 'video/');
            if (!$isVideo) {
                return ["El archivo debe ser un vídeo (MP4, WEBM, MOV o MKV)."];
            }
            if ($media['size'] > 500000000) {
                return ["El vídeo es demasiado grande. Máximo 500MB."];
            }

            $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['mp4', 'webm', 'mov', 'mkv'])) {
                return ["Formato no permitido. Usa MP4, WEBM, MOV o MKV."];
            }

            $finalPath = $extractFolder . "episode." . $ext;
            if (!move_uploaded_file($media['tmp_name'], $finalPath)) {
                return ["Error al mover el vídeo al directorio destino."];
            }

            $rutaBD = $idWork . '/chapters/' . $idChapter . '/episode.' . $ext;
            $this->connection->query("UPDATE Chapters SET File = '$rutaBD' WHERE ID_Chapter = '$idChapter'");

            return "Episodio subido correctamente.";
        }

        // Manga: ZIP 
        $isZip = ($mime === 'application/zip' || $mime === 'application/x-zip-compressed');
        if (!$isZip) {
            return ["El archivo debe ser un ZIP válido."];
        }
        if ($media['size'] > 500000000) {
            return ["El ZIP es demasiado grande. Máximo 500MB."];
        }
        $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            return ["El archivo debe tener extensión .zip."];
        }

        $zipPath = $extractFolder . "chapter.zip";
        if (!move_uploaded_file($media['tmp_name'], $zipPath)) {
            return ["Error al mover el ZIP al directorio destino."];
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== TRUE) {
            return ["No se pudo descomprimir el archivo ZIP."];
        }
        $zip->extractTo($extractFolder);
        $zip->close();
        unlink($zipPath);

        $files = array_filter(scandir($extractFolder), function ($file) {
            return preg_match('/\.(jpg|jpeg|png|webp)$/i', $file);
        });

        if (empty($files)) {
            return ["El ZIP no contiene imágenes válidas para el capítulo."];
        }

        natsort($files);
        file_put_contents($extractFolder . "index.txt", implode(PHP_EOL, $files));

        $rutaBD = $idWork . '/chapters/' . $idChapter . '/';
        $this->connection->query("UPDATE Chapters SET File = '$rutaBD' WHERE ID_Chapter = '$idChapter'");

        return "Capítulo subido correctamente.";
    }

    public function deleteChapterUploads($idWork, $idChapter, $type)
    {
        $dir = ($type === 'Anime')
            ? $this->animeLocation . $idWork . '/chapters/' . $idChapter . '/'
            : $this->mangaLocation . $idWork . '/chapters/' . $idChapter . '/';

        if (is_dir($dir)) {
            $this->deleteDir($dir);
        }
    }

    // Eventos

    /**
     * Devuelve string en éxito, array de errores en fallo.
     */
    public function uploadEventImage($idEvent, $media)
    {
        $errors = [];

        if ($media['error'] !== UPLOAD_ERR_OK) {
            return ["Error al recibir la imagen del evento."];
        }
        $check = getimagesize($media['tmp_name']);
        if ($check === false) {
            return ["El archivo no es una imagen válida."];
        }
        if ($media['size'] > 5000000) {
            $errors[] = "La imagen es demasiado grande. Máximo 5MB.";
        }
        $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $errors[] = "La imagen debe ser JPG, JPEG, PNG o WEBP.";
        }
        if (!empty($errors)) {
            return $errors;
        }

        $destination = $this->eventLocation . $idEvent . '/';
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $finalName = "banner.$ext";
        $finalPath = $destination . $finalName;
        if (!move_uploaded_file($media['tmp_name'], $finalPath)) {
            return ["Error al mover la imagen al directorio destino."];
        }

        $rutaBD = 'Event' . $idEvent . '/' . $finalName;
        $this->connection->query("UPDATE Events SET Image = '$rutaBD' WHERE ID_Event = '$idEvent'");

        return "Imagen del evento subida correctamente.";
    }

    /**
     * Devuelve string en éxito, array de errores en fallo.
     * Acepta MP4, WEBM, MOV y MKV.
     */
    public function uploadEventVideo($idEvent, $idMedia, $media)
    {
        $errors = [];

        if ($media['error'] !== UPLOAD_ERR_OK) {
            return ["Error al recibir el vídeo del evento."];
        }

        $mime    = mime_content_type($media['tmp_name']);
        $isVideo = str_starts_with($mime, 'video/');
        if (!$isVideo) {
            return ["El archivo no es un vídeo válido."];
        }
        if ($media['size'] > 500000000) {
            $errors[] = "El vídeo es demasiado grande. Máximo 500MB.";
        }
        $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp4', 'webm', 'mov', 'mkv'])) {
            $errors[] = "El vídeo debe ser MP4, WEBM, MOV o MKV.";
        }
        if (!empty($errors)) {
            return $errors;
        }

        $destination = $this->eventLocation . $idEvent . '/';
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $finalName = "video.$ext";
        $finalPath = $destination . $finalName;
        if (!move_uploaded_file($media['tmp_name'], $finalPath)) {
            return ["Error al mover el vídeo al directorio destino."];
        }

        $rutaBD = 'Event' . $idEvent . '/' . $finalName;
        $this->connection->query("UPDATE Event_Media SET Video = '$rutaBD' WHERE ID_Media = '$idMedia'");

        return "Vídeo del evento subido correctamente.";
    }

    /**
     * Devuelve string en éxito, array de errores en fallo.
     */
    public function uploadEventAudio($idEvent, $idMedia, $media)
    {
        $errors = [];

        if ($media['error'] !== UPLOAD_ERR_OK) {
            return ["Error al recibir el audio del evento."];
        }

        $mime    = mime_content_type($media['tmp_name']);
        $isAudio = str_starts_with($mime, 'audio/');
        if (!$isAudio) {
            return ["El archivo no es un audio válido."];
        }
        if ($media['size'] > 100000000) {
            $errors[] = "El audio es demasiado grande. Máximo 100MB.";
        }
        $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'])) {
            $errors[] = "El audio debe ser MP3, WAV, OGG o M4A.";
        }
        if (!empty($errors)) {
            return $errors;
        }

        $destination = $this->eventLocation . $idEvent . '/';
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $finalName = "audio.$ext";
        $finalPath = $destination . $finalName;
        if (!move_uploaded_file($media['tmp_name'], $finalPath)) {
            return ["Error al mover el audio al directorio destino."];
        }

        $rutaBD = 'Event' . $idEvent . '/' . $finalName;
        $this->connection->query("UPDATE Event_Media SET Audio = '$rutaBD' WHERE ID_Media = '$idMedia'");

        return "Audio del evento subido correctamente.";
    }

    public function deleteEventUploads($idEvent)
    {
        $dir = $this->eventLocation . $idEvent . '/';
        if (is_dir($dir)) {
            $this->deleteDir($dir);
        }
    }

    // Utilidades

    private function deleteDir($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $ruta = $dir . $file;
            is_dir($ruta) ? $this->deleteDir($ruta) : unlink($ruta);
        }
        rmdir($dir);
    }
}
