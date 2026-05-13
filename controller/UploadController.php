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
    private $ffmpegPath = 'ffmpeg';
    private $ffprobePath = 'ffprobe';

    public function __construct()
    {
        $this->connection = (new Database())->getConnection();
    }

    // Usuario

    public function uploadAvatar($user, $avatar)
    {
        $errors = [];

        $type = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
        $userId = $user->getUserID();
        $destination = $this->userLocation . $userId . '/';
        $file_dest = $destination . 'avatar.' . $type;

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

        $mime = mime_content_type($media['tmp_name']);
        $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
        $isImage = str_starts_with($mime, 'image/');
        $isVideo = str_starts_with($mime, 'video/') || in_array($ext, ['mp4', 'webm', 'mov', 'mkv']);

        $destination = ($type === 'Anime')
            ? $this->animeLocation . $id . '/'
            : $this->mangaLocation . $id . '/';

        if ($isImage) {
            if ($media['size'] > 5000000) {
                $errors[] = "La imagen es demasiado grande. Máximo 5MB.";
            }
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $errors[] = "La imagen debe ser JPG, JPEG, PNG o WEBP.";
            }
            $finalName = "banner.$ext";
            $column = "Image";

        } elseif ($isVideo) {
            if ($media['size'] > 1073741824) {
                $errors[] = "El vídeo es demasiado grande. Máximo 1GB.";
            }
            if (!in_array($ext, ['mp4', 'webm', 'mov', 'mkv'])) {
                $errors[] = "El tráiler debe ser MP4, WEBM, MOV o MKV.";
            }
            if (!$this->commandExists($this->ffmpegPath) || !$this->commandExists($this->ffprobePath)) {
                $errors[] = "FFmpeg/ffprobe no esta instalado o no esta disponible para PHP. No se puede convertir el trailer a MP4.";
            }
            $finalName = "trailer.mp4";
            $sourceName = "upload-trailer-source.$ext";
            $column = "Trailer";

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
        if ($isVideo) {
            $sourcePath = $destination . $sourceName;
            if (!move_uploaded_file($media['tmp_name'], $sourcePath)) {
                return ["Error al mover el archivo al directorio destino."];
            }

            $conversionResult = $this->convertVideoToMp4($sourcePath, $finalPath);
            @unlink($sourcePath);
            if (is_array($conversionResult)) {
                return $conversionResult;
            }

            $rutaBD = $id . '/' . $finalName;
            $this->connection->query("UPDATE Works SET $column = '$rutaBD' WHERE ID_Work = '$id';");

            return "Trailer convertido a MP4 correctamente.";
        }

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

        $mime = mime_content_type($media['tmp_name']);
        $basePath = ($type === 'Anime')
            ? $this->animeLocation . $idWork . '/'
            : $this->mangaLocation . $idWork . '/';
        $chaptersPath = $basePath . "chapters/";
        $extractFolder = $chaptersPath . $idChapter . "/";

        if (!is_dir($extractFolder)) {
            mkdir($extractFolder, 0755, true);
        }

        // Anime: vídeo 
        if ($type === 'Anime') {
            $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
            $isVideo = str_starts_with($mime, 'video/') || in_array($ext, ['mp4', 'webm', 'mov', 'mkv']);
            if (!$isVideo) {
                return ["El archivo debe ser un vídeo (MP4, WEBM, MOV o MKV)."];
            }
            if ($media['size'] > 1073741824) {
                return ["El vídeo es demasiado grande. Máximo 1GB."];
            }

            if (!in_array($ext, ['mp4', 'webm', 'mov', 'mkv'])) {
                return ["Formato no permitido. Usa MP4, WEBM, MOV o MKV."];
            }

            if (!$this->commandExists($this->ffmpegPath) || !$this->commandExists($this->ffprobePath)) {
                return ["FFmpeg/ffprobe no esta instalado o no esta disponible para PHP. No se puede convertir el video ni extraer subtitulos."];
            }

            $sourcePath = $extractFolder . "upload-source." . $ext;
            $finalPath = $extractFolder . "episode.mp4";
            $subtitlesPath = $extractFolder . "subtitles/";
            if (!move_uploaded_file($media['tmp_name'], $sourcePath)) {
                return ["Error al mover el vídeo al directorio destino."];
            }

            $conversionResult = $this->convertVideoToMp4($sourcePath, $finalPath);
            if (is_array($conversionResult)) {
                @unlink($sourcePath);
                return $conversionResult;
            }

            $subtitleCount = $this->extractEmbeddedSubtitles($sourcePath, $subtitlesPath);
            @unlink($sourcePath);

            $rutaBD = $idWork . '/chapters/' . $idChapter . '/episode.mp4';
            $this->connection->query("UPDATE Chapters SET File = '$rutaBD' WHERE ID_Chapter = '$idChapter'");

            if ($subtitleCount > 0) {
                return "Episodio convertido a MP4 correctamente. Subtitulos extraidos: $subtitleCount.";
            }

            return "Episodio convertido a MP4 correctamente. No se encontraron subtitulos compatibles.";
        }

        // Manga: ZIP 
        $isZip = in_array($mime, ['application/zip', 'application/x-zip-compressed', 'application/x-zip', 'application/octet-stream', 'multipart/x-zip']);
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

        $rutaBD = $idEvent . '/' . $finalName;
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

        $mime = mime_content_type($media['tmp_name']);
        $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
        $isVideo = str_starts_with($mime, 'video/') || in_array($ext, ['mp4', 'webm', 'mov', 'mkv']);
        if (!$isVideo) {
            return ["El archivo no es un vídeo válido."];
        }
        if ($media['size'] > 500000000) {
            $errors[] = "El vídeo es demasiado grande. Máximo 500MB.";
        }
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

        if (!$this->commandExists($this->ffmpegPath) || !$this->commandExists($this->ffprobePath)) {
            return ["FFmpeg/ffprobe no esta instalado o no esta disponible para PHP. No se puede convertir el video a MP4."];
        }

        $sourceName = "upload-video-source.$ext";
        $finalName = "video.mp4";
        $finalPath = $destination . $finalName;
        $sourcePath = $destination . $sourceName;
        if (!move_uploaded_file($media['tmp_name'], $sourcePath)) {
            return ["Error al mover el vídeo al directorio destino."];
        }

        $rutaBD = $idEvent . '/' . $finalName;
        $conversionResult = $this->convertVideoToMp4($sourcePath, $finalPath);
        @unlink($sourcePath);
        if (is_array($conversionResult)) {
            return $conversionResult;
        }
        $this->connection->query("UPDATE Event_Media SET Video = '$rutaBD' WHERE ID_Media = '$idMedia'");

        return "Video del evento convertido a MP4 correctamente.";
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

        $rutaBD = $idEvent . '/' . $finalName;
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

    private function commandExists($command)
    {
        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $command)) {
            return false;
        }

        $checkCommand = stripos(PHP_OS_FAMILY, 'Windows') === 0
            ? 'where ' . escapeshellcmd($command)
            : 'command -v ' . escapeshellarg($command);

        exec($checkCommand, $output, $code);
        return $code === 0;
    }

    private function convertVideoToMp4($sourcePath, $finalPath)
    {
        // Fast path: remux copying the video stream, re-encode audio to AAC only.
        // This handles H.264/H.265 sources (virtually all anime) in seconds instead of minutes.
        $command = sprintf(
            '%s -y -i %s -map 0:v:0 -map 0:a? -c:v copy -c:a aac -b:a 160k -movflags +faststart -sn %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($sourcePath),
            escapeshellarg($finalPath)
        );

        exec($command, $output, $code);

        if ($code === 0 && file_exists($finalPath) && filesize($finalPath) > 0) {
            return true;
        }

        // Slow fallback: full re-encode for VP9, AV1, or other codecs incompatible with MP4.
        @unlink($finalPath);
        $command = sprintf(
            '%s -y -i %s -map 0:v:0 -map 0:a? -c:v libx264 -preset veryfast -crf 23 -c:a aac -b:a 160k -movflags +faststart -sn %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($sourcePath),
            escapeshellarg($finalPath)
        );

        exec($command, $output, $code);

        if ($code !== 0 || !file_exists($finalPath) || filesize($finalPath) === 0) {
            @unlink($finalPath);
            return ["No se pudo convertir el video a MP4. Detalle: " . $this->lastCommandLine($output)];
        }

        return true;
    }

    private function extractEmbeddedSubtitles($sourcePath, $subtitlesPath)
    {
        $streams = $this->getSubtitleStreams($sourcePath);
        if (empty($streams)) {
            return 0;
        }

        if (!is_dir($subtitlesPath)) {
            mkdir($subtitlesPath, 0755, true);
        }

        // Build output paths and a single FFmpeg command that extracts all tracks in one pass.
        $outputParts = [];
        $outputPaths = [];

        foreach ($streams as $position => $stream) {
            $index = intval($stream['index']);
            $language = $this->sanitizeSubtitleCode($stream['tags']['language'] ?? ('sub' . ($position + 1)));
            $title = $this->sanitizeSubtitleCode($stream['tags']['title'] ?? '');
            $suffix = $title !== '' ? $language . '-' . $title : $language;
            $outputPath = $subtitlesPath . $suffix . '.vtt';

            if (file_exists($outputPath) || in_array($outputPath, $outputPaths)) {
                $outputPath = $subtitlesPath . $suffix . '-' . ($position + 1) . '.vtt';
            }

            $outputPaths[] = $outputPath;
            $outputParts[] = sprintf('-map 0:%d -c:s webvtt %s', $index, escapeshellarg($outputPath));
        }

        $command = sprintf(
            '%s -y -i %s %s 2>&1',
            escapeshellcmd($this->ffmpegPath),
            escapeshellarg($sourcePath),
            implode(' ', $outputParts)
        );

        exec($command, $output, $code);

        $count = 0;
        foreach ($outputPaths as $outputPath) {
            if (file_exists($outputPath) && filesize($outputPath) > 0) {
                $count++;
            } else {
                @unlink($outputPath);
            }
        }

        if ($count === 0 && is_dir($subtitlesPath) && count(array_diff(scandir($subtitlesPath), ['.', '..'])) === 0) {
            rmdir($subtitlesPath);
        }

        return $count;
    }

    private function getSubtitleStreams($sourcePath)
    {
        // -select_streams s limits ffprobe to subtitle streams only, avoiding full stream scan.
        $command = sprintf(
            '%s -v quiet -print_format json -show_streams -select_streams s %s 2>&1',
            escapeshellcmd($this->ffprobePath),
            escapeshellarg($sourcePath)
        );

        exec($command, $output, $code);
        if ($code !== 0) {
            return [];
        }

        $data = json_decode(implode("\n", $output), true);
        if (!isset($data['streams']) || !is_array($data['streams'])) {
            return [];
        }

        return array_values($data['streams']);
    }

    private function sanitizeSubtitleCode($value)
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9_-]+/', '-', $value);
        $value = trim($value, '-_');

        return $value !== '' ? $value : 'sub';
    }

    private function lastCommandLine($output)
    {
        $output = array_values(array_filter($output, function ($line) {
            return trim($line) !== '';
        }));

        if (empty($output)) {
            return "FFmpeg no devolvio detalles.";
        }

        return substr(end($output), 0, 300);
    }

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