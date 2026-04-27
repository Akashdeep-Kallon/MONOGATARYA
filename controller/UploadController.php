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
    private $userLocation = '/var/www/uploads/User/';

    public function __construct()
    {
        $this->connection = (new Database())->getConnection();
    }

    //usuaurio

    public function uploadAvatar($user, $avatar)
    {
        $errors = [];

        $type = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
        $userId = $user->getUserID();
        $destination = $this->userLocation . $userId . '/';
        $file_destination = $destination . 'avatar.' . $type;

        // 1. Error de subida → PRIMERO
        if ($avatar['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error al recibir el archivo.";
        }
        // 2. Validar que es imagen real
        $check = getimagesize($avatar['tmp_name']);
        if ($check === false) {
            $errors[] = "El archivo no es una imagen.";
        }
        // 3. Tamaño máximo 5MB
        if ($avatar['size'] > 5000000) {
            $errors[] = "La imagen es demasiado grande. Máximo 5MB.";
        }
        // 4. Extensiones permitidas
        if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
            $errors[] = "Solo se permiten archivos JPG, JPEG, PNG y WEBP.";
        }
        // Si hay errores → devolverlos
        if (!empty($errors)) {
            $errors[] = "La imagen no se ha subido.";
            return $errors;
        }
        // Crear carpeta si no existe
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        // Mover archivo y guardar ruta en BD
        if (move_uploaded_file($avatar['tmp_name'], $file_destination)) {
            $rutaBD = $userId . '/avatar.' . $type;
            // CORRECCIÓN: updateAvatar solo recibe la ruta, usa su propia conexión internamente
            $user->updateAvatar($rutaBD);
            return "Imagen subida correctamente.";
        } else {
            return ["Hubo un error al mover el archivo."];
        }
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

    //(Anime / Manga)

    public function uploadWork($id, $type, $media)
    {
        $errors = [];
        $messages = [];

        // Comprobar si hubo algún error al subir el archivo
        if ($media['error'] !== UPLOAD_ERR_OK) {
            // Caso 1: archivo demasiado grande
            if ($media['error'] === UPLOAD_ERR_INI_SIZE || $media['error'] === UPLOAD_ERR_FORM_SIZE) {
                $errors[] = "El archivo supera el tamaño máximo permitido.";
            }
            // Caso 2: no se envió ningún archivo
            else if ($media['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = "No se recibió ningún archivo.";
            }
            // Caso 3: cualquier otro error
            else {
                $errors[] = "Error al recibir el archivo (código: {$media['error']}).";
            }
            return $errors;
        }


        // 2. llamar a mime_content_type
        $mime = mime_content_type($media['tmp_name']);
        $isImage = str_starts_with($mime, 'image/');
        $isVideo = str_starts_with($mime, 'video/');

        // Definir destino según tipo de obra
        $destination = ($type === 'Anime')
            ? $this->animeLocation . $id . '/'
            : $this->mangaLocation . $id . '/';

        // Validaciones generales
        if ($media['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error al recibir el archivo.";
        }

        // Validaciones específicas
        if ($isImage) {

            if ($media['size'] > 5000000) {
                $errors[] = "La imagen es demasiado grande. Máximo 5MB.";
            }

            $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $errors[] = "La imagen debe ser JPG, JPEG, PNG o WEBP.";
            }

            $finalName = "banner.$ext";
            $column = "Image";

        } elseif ($isVideo) {

            if ($media['size'] > 50000000) {
                $errors[] = "El vídeo es demasiado grande. Máximo 50MB.";
            }

            $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['mp4', 'webm', 'mov', 'mkv'])) {
                $errors[] = "El tráiler debe ser MP4, WEBM, MOV o MKV.";
            }

            $finalName = "trailer.$ext";
            $column = "Trailer";

        } else {
            $errors[] = "El archivo no es una imagen ni un vídeo válido.";
        }

        if (!empty($errors)) {
            return $errors;
        }

        // Crear carpeta si no existe
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $finalPath = $destination . $finalName;

        if (!move_uploaded_file($media['tmp_name'], $finalPath)) {
            return ["Error al mover el archivo al directorio destino."];
        }

        // Guardar ruta en BD
        $rutaBD = $id . '/' . $finalName;
        $this->connection->query("UPDATE Works SET $column = '$rutaBD' WHERE ID_Work = '$id';");

        $messages[] = $isImage
            ? "Banner subido correctamente."
            : "Tráiler subido correctamente.";

        return $messages;
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

    //capitulos
    public function uploadChapter($idWork, $idChapter, $type, $media)
    {
        if ($media['error'] !== UPLOAD_ERR_OK) {
            return ["Error al recibir el archivo."];
        }

        $mime = mime_content_type($media['tmp_name']);

        $basePath = ($type === 'Anime')
            ? $this->animeLocation . $idWork . '/'
            : $this->mangaLocation . $idWork . '/';
        $chaptersPath = $basePath . "chapters/";
        $extractFolder = $chaptersPath . $idChapter . "/";

        if (!is_dir($extractFolder)) {
            mkdir($extractFolder, 0755, true);
        }

        // Anime
        if ($type === 'Anime') {

            $isVideo = strpos($mime, 'video/') === 0;
            if (!$isVideo) {
                return ["El archivo debe ser un vídeo (MP4, WEBM, MOV)."];
            }
            if ($media['size'] > 500000000) {
                return ["El vídeo es demasiado grande. Máximo 500MB."];
            }

            $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['mp4', 'webm', 'mov'])) {
                return ["Formato no permitido. Usa MP4, WEBM o MOV."];
            }

            $finalPath = $extractFolder . "episode." . $ext;
            if (!move_uploaded_file($media['tmp_name'], $finalPath)) {
                return ["Error al mover el vídeo al directorio destino."];
            }

            $rutaBD = $idWork . '/chapters/' . $idChapter . '/episode.' . $ext;
            $this->connection->query("UPDATE Chapters SET File = '$rutaBD' WHERE ID_Chapter = '$idChapter'");

            return ["Episodio subido correctamente."];
        }

        // Manga
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

        return ["Capítulo subido correctamente."];
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

    //evetos

    public function uploadEventImage($idEvent, $media)
    {
        $errors = [];

        // Validar que es imagen real
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

        // Guardar ruta en BD
        $rutaBD = 'Event' . $idEvent . '/' . $finalName;
        $this->connection->query("UPDATE Events SET Image = '$rutaBD' WHERE ID_Event = '$idEvent'");

        return ["Imagen del evento subida correctamente."];
    }

    public function uploadEventVideo($idEvent, $idMedia, $media)
    {
        $errors = [];

        if ($media['error'] !== UPLOAD_ERR_OK) {
            return ["Error al recibir el vídeo del evento."];
        }

        $mime = mime_content_type($media['tmp_name']);
        $isVideo = str_starts_with($mime, 'video/');

        if (!$isVideo) {
            return ["El archivo no es un vídeo válido."];
        }

        if ($media['size'] > 500000000) {
            $errors[] = "El vídeo es demasiado grande. Máximo 500MB.";
        }

        $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp4', 'webm', 'mov'])) {
            $errors[] = "El vídeo debe ser MP4, WEBM o MOV.";
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

        // Guardar ruta en BD (tabla Event_Media)
        $rutaBD = 'Event' . $idEvent . '/' . $finalName;
        $this->connection->query("UPDATE Event_Media SET Video = '$rutaBD' WHERE ID_Media = '$idMedia'");

        return ["Vídeo del evento subido correctamente."];
    }

    public function uploadEventAudio($idEvent, $idMedia, $media)
    {
        $errors = [];

        if ($media['error'] !== UPLOAD_ERR_OK) {
            return ["Error al recibir el audio del evento."];
        }

        $mime = mime_content_type($media['tmp_name']);
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

        // Guardar ruta en BD (tabla Event_Media)
        $rutaBD = 'Event' . $idEvent . '/' . $finalName;
        $this->connection->query("UPDATE Event_Media SET Audio = '$rutaBD' WHERE ID_Media = '$idMedia'");

        return ["Audio del evento subido correctamente."];
    }

    public function deleteEventUploads($idEvent)
    {
        $dir = $this->eventLocation . $idEvent . '/';
        if (is_dir($dir)) {
            $this->deleteDir($dir);
        }
    }

    // Borra una carpeta y todo su contenido de forma recursiva
    private function deleteDir($dir)
    { //Escanea la carpeta y lo mete en un array y despues elimina del array las carpeta . y ..
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $ruta = $dir . $file;
            //borrar contenido carpeta o solo es archivo borrarlo
            is_dir($ruta) ? $this->deleteDir($ruta) : unlink($ruta);
        }

        //borrar carpeta si esta vacia
        rmdir($dir);
    }
}