<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/UploadController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/message.php';

class Catalog
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = (new Database())->getConnection();
    }

    // Helpers internos

    /**
     * Procesa el resultado de un upload:
     *   - string  → éxito, lo acumula en $successes
     *   - array   → errores, los acumula en $errors
     */
    private function handleUploadResult($result, array &$errors, array &$successes): void
    {
        if (is_array($result)) {
            $errors = array_merge($errors, $result);
        } else {
            $successes[] = $result;
        }
    }

    // Paginación de catálogos

    public function returnCatalog($type, $catalog)
    {
        $queryTotal = null;

        if ($type === 'Works') {
            $queryTotal = $this->connection->query("SELECT COUNT(*) AS total FROM Works WHERE Type = '$catalog'");
        }
        if ($type === 'Events') {
            $queryTotal = $this->connection->query("SELECT COUNT(*) AS total FROM Events");
        }

        $fila = $queryTotal->fetch();
        $totalMedia = $fila['total'];
        $limit = 6;
        $totalPages = max(1, ceil($totalMedia / $limit));
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

        if ($page < 1)
            $page = 1;
        if ($page > $totalPages)
            $page = $totalPages;

        $offset = ($page - 1) * $limit;

        if ($type === 'Events') {
            $sql = "SELECT * FROM Events LIMIT $limit OFFSET $offset";
        } else {
            $escapedCatalog = $this->connection->quote($catalog);
            $sql = "SELECT * FROM Works WHERE Type = $escapedCatalog LIMIT $limit OFFSET $offset";
        }

        $query = $this->connection->query($sql);

        return [
            'page' => $page,
            'totalPages' => $totalPages,
            'query' => $query
        ];
    }

    // Obras (Anime / Manga)

    public function createWork()
    {
        $type = $_POST['type'];
        $redirectType = strtolower($type);
        if (!in_array($redirectType, ['anime', 'manga']))
            $redirectType = 'anime';
        $location = VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-catalog.php';

        $errors = [];

        if (empty($_POST['title']) || strlen($_POST['title']) < 5)
            $errors[] = "El título es obligatorio y debe tener al menos 5 caracteres.";
        if (strlen($_POST['subtitle']) < 5)
            $errors[] = "El subtítulo debe tener al menos 5 caracteres.";
        if (empty($_POST['premiere_date']))
            $errors[] = "La fecha de estreno es obligatoria.";
        if (empty($_POST['studio']))
            $errors[] = "El estudio/plataforma es obligatorio.";
        if (empty($_POST['gender']))
            $errors[] = "El género es obligatorio.";
        if (strlen($_POST['description']) < 10)
            $errors[] = "La descripción debe tener al menos 10 caracteres.";

        $hasImage = !empty($_FILES['image_file']['name']);
        $hasUrl = !empty($_POST['image_url']);

        if ($hasImage && $hasUrl)
            $errors[] = "Solo puedes usar una opción: imagen de portada o URL, no ambas.";

        if (!empty($errors)) {
            setError($errors, $location);
            return;
        }

        $title = $this->connection->quote($_POST['title']);
        $subtitle = $this->connection->quote($_POST['subtitle']);
        $premiereDate = $this->connection->quote($_POST['premiere_date']);
        $studio = $this->connection->quote($_POST['studio']);
        $gender = $this->connection->quote($_POST['gender']);
        $description = $this->connection->quote($_POST['description']);

        $this->connection->query("
            CALL sp_add_Work($type,$title,$subtitle,$studio,$premiereDate,$gender,$description,@p_ID_Work)
        ");

        $idResult = $this->connection->query("SELECT @p_ID_Work AS ID_Work");
        if (!$idResult) {
            setError(["Error al obtener el ID de la obra creada."], $location);
            return;
        }
        $row = $idResult->fetch();
        $idWork = intval($row['ID_Work']);



        $uploadErrors = [];
        $uploadSuccesses = [];

        if ($hasUrl) {
            $imageUrl = $this->connection->quote($_POST['image_url']);
            $this->connection->query("UPDATE Works SET Image = $imageUrl WHERE ID_Work = $idWork");
        }
        if ($hasImage) {
            $result = (new UploadController())->uploadWork($idWork, $type, $_FILES['image_file']);
            $this->handleUploadResult($result, $uploadErrors, $uploadSuccesses);
        }
        if (!empty($_FILES['trailer']['name'])) {
            $result = (new UploadController())->uploadWork($idWork, $type, $_FILES['trailer']);
            $this->handleUploadResult($result, $uploadErrors, $uploadSuccesses);
        }

        if (!empty($uploadErrors)) {
            // La obra se creó pero algún archivo falló
            setSuccess("Obra creada correctamente.");
            setError($uploadErrors, $location);
        } else {
            setSuccess("Obra creada correctamente.", $location);
        }
    }

    public function updateWork()
    {
        $id = intval($_POST['id']);
        $type = $this->connection->quote($_POST['type']);
        $redirectType = strtolower($type);
        if (!in_array($redirectType, ['anime', 'manga']))
            $redirectType = 'anime';
        $location = VIEW_URL . '/catalogs/work-detail.php?type=' . urlencode($type) . '&id=' . $id;

        $errors = [];

        if (empty($_POST['title']) || strlen($_POST['title']) < 5)
            $errors[] = "El título es obligatorio y debe tener al menos 5 caracteres.";
        if (strlen($_POST['subtitle']) < 5)
            $errors[] = "El subtítulo debe tener al menos 5 caracteres.";
        if (empty($_POST['premiere_date']))
            $errors[] = "La fecha de estreno es obligatoria.";
        if (empty($_POST['studio']))
            $errors[] = "El estudio/plataforma es obligatorio.";
        if (empty($_POST['gender']))
            $errors[] = "El género es obligatorio.";
        if (strlen($_POST['description']) < 10)
            $errors[] = "La descripción debe tener al menos 10 caracteres.";

        $hasImage = !empty($_FILES['image_file']['name']);
        $hasUrl = !empty($_POST['image_url']);

        if ($hasImage && $hasUrl)
            $errors[] = "Solo puedes usar una opción: imagen de portada o URL, no ambas.";

        if (!empty($errors)) {
            setError($errors, $location);
            return;
        }

        $title = $this->connection->quote($_POST['title']);
        $subtitle = $this->connection->quote($_POST['subtitle']);
        $premiereDate = $this->connection->quote($_POST['premiere_date']);
        $studio = $this->connection->quote($_POST['studio']);
        $gender = $this->connection->quote($_POST['gender']);
        $description = $this->connection->quote($_POST['description']);
        $active = isset($_POST['active']) ? 1 : 0;

        $this->connection->query("
            UPDATE Works
            SET Title = $title, Subtitle = $subtitle, Date_premiere = $premiereDate,
                Studio = $studio, Gender = $gender, Description = $description, Active = $active
            WHERE ID_Work = $id
        ");

        $uploadErrors = [];
        $uploadSuccesses = [];

        if ($hasUrl) {
            $imageUrl = $this->connection->quote($_POST['image_url']);
            $this->connection->query("UPDATE Works SET Image = $imageUrl WHERE ID_Work = $id");
        }
        if ($hasImage) {
            $result = (new UploadController())->uploadWork($id, $type, $_FILES['image_file']);
            $this->handleUploadResult($result, $uploadErrors, $uploadSuccesses);
        }
        if (!empty($_FILES['video']['name'])) {
            $result = (new UploadController())->uploadWork($id, $type, $_FILES['video']);
            $this->handleUploadResult($result, $uploadErrors, $uploadSuccesses);
        }

        if (!empty($uploadErrors)) {
            setSuccess("Obra actualizada correctamente.");
            setError($uploadErrors, $location);
        } else {
            setSuccess("Obra actualizada correctamente.", $location);
        }
    }

    public function deleteWork()
    {
        $id = intval($_POST['id']);
        $type = $_POST['type'];
        $redirectType = strtolower($type);
        if (!in_array($redirectType, ['anime', 'manga']))
            $redirectType = 'anime';
        $location = VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-catalog.php';

        $this->connection->query("DELETE FROM Works WHERE ID_Work = $id");
        (new UploadController())->deleteWorkUploads($id, $type);

        setSuccess("Obra eliminada correctamente.", $location);
    }

    public function returnWorkDetail($id, $type)
    {
        $id = intval($id);
        $type = $this->connection->quote($type);
        $workQuery = $this->connection->query("SELECT * FROM Works WHERE ID_Work = $id AND Type = $type");

        if ($workRow = $workQuery->fetch()) {
            $chapQuery = $this->connection->query("SELECT * FROM Chapters WHERE ID_Work = $id ORDER BY Chapter_Number ASC");
            $chapters = [];
            while ($ch = $chapQuery->fetch()) {
                $chapters[] = $ch;
            }
            return [
                'title' => $workRow['Title'],
                'subtitle' => $workRow['Subtitle'],
                'image' => $workRow['Image'],
                'trailer' => $workRow['Trailer'],
                'description' => $workRow['Description'],
                'premiere' => $workRow['Date_premiere'],
                'studio' => $workRow['Studio'],
                'gender' => $workRow['Gender'],
                'active' => $workRow['Active'],
                'chapters' => $chapters
            ];
        }

        $redirectType = strtolower($type);
        if ($redirectType !== 'manga')
            $redirectType = 'anime';
        header('Location: ' . VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-catalog.php');
        exit();
    }

    public function returnChapter($id, $idChapter, $chapterNumber, $type)
    {
        $id = intval($id);
        $idChapter = intval($idChapter);
        $chapterNumber = intval($chapterNumber);

        $chapQuery = $this->connection->query(
            "SELECT * FROM Chapters WHERE ID_Chapter = $idChapter AND ID_Work = $id AND Chapter_Number = $chapterNumber"
        );
        if ($chapRow = $chapQuery->fetch()) {
            $prev = $this->connection->query(
                "SELECT ID_Chapter, Chapter_Number FROM Chapters WHERE ID_Work = $id AND Chapter_Number < {$chapRow['Chapter_Number']} ORDER BY Chapter_Number DESC LIMIT 1"
            )->fetch();
            $next = $this->connection->query(
                "SELECT ID_Chapter, Chapter_Number FROM Chapters WHERE ID_Work = $id AND Chapter_Number > {$chapRow['Chapter_Number']} ORDER BY Chapter_Number ASC LIMIT 1"
            )->fetch();

            return [
                'title' => $chapRow['Title'],
                'description' => $chapRow['Description'],
                'number' => $chapRow['Chapter_Number'],
                'File' => $chapRow['File'],
                'prev_id' => $prev['ID_Chapter'] ?? null,
                'next_id' => $next['ID_Chapter'] ?? null,
                'prev_chapter' => $prev['Chapter_Number'] ?? null,
                'next_chapter' => $next['Chapter_Number'] ?? null,
            ];
        }

        $redirectType = strtolower($type);
        if ($redirectType !== 'manga')
            $redirectType = 'anime';
        header('Location: ' . VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-catalog.php');
        exit();
    }

    public function getChapter($idWork, $idChapter)
    {
        $idWork = intval($idWork);
        $idChapter = intval($idChapter);

        $result = $this->connection->query("SELECT * FROM Chapters WHERE ID_Work = $idWork AND ID_Chapter = $idChapter LIMIT 1");
        return $result ? $result->fetch() : null;
    }

    public function updateChapter()
    {
        $idChapter = intval($_POST['id_chapter']);
        $idWork = intval($_POST['id_work']);
        $type = $_POST['type'];

        $redirectType = strtolower($type);
        if (!in_array($redirectType, ['anime', 'manga'])) {
            $redirectType = 'anime';
        }

        $baseLocation = VIEW_URL . '/catalogs/' . $redirectType . '/work-detail.php?type=' . urlencode($type) . '&id=' . $idWork;
        $location = $baseLocation;
        $errors = [];

        if (empty($_POST['title']) || strlen($_POST['title']) < 5) {
            $errors[] = "El título es obligatorio y debe tener al menos 5 caracteres.";
        }
        if (strlen($_POST['description']) < 10) {
            $errors[] = "La descripción debe tener al menos 10 caracteres.";
        }

        $chapterNumber = isset($_POST['chapter_number']) ? intval($_POST['chapter_number']) : 0;
        if ($chapterNumber <= 0) {
            $errors[] = "El número de capítulo debe ser un número válido mayor que cero.";
        }

        if (!empty($errors)) {
            setError($errors, $location);
            return;
        }

        $duplicateQuery = $this->connection->query(
            "SELECT ID_Chapter FROM Chapters WHERE ID_Work = $idWork AND Chapter_Number = $chapterNumber LIMIT 1"
        );

        $duplicateRow = $duplicateQuery->fetch();

        if ($duplicateRow && $duplicateRow['ID_Chapter'] != $idChapter) {
            if (intval($duplicateRow['ID_Chapter']) !== $idChapter) {
                $editLocation = VIEW_URL . '/catalogs/edit-chapter.php?type=' . urlencode($type)
                    . '&id=' . $idWork
                    . '&idChapter=' . $idChapter
                    . '&numberChapter=' . $chapterNumber;
                setError([
                    "El número de capítulo $chapterNumber ya está asignado a otro capítulo. Elige otro número."
                ], $editLocation);
                return;
            }
        }

        $chapterQuery = $this->connection->query("SELECT Chapter_Number FROM Chapters WHERE ID_Chapter = $idChapter AND ID_Work = $idWork");
        $currentRow = $chapterQuery ? $chapterQuery->fetch() : false;
        if (!$currentRow) {
            setError(["No se encontró el capítulo solicitado."], $location);
            return;
        }

        $currentNumber = intval($currentRow['Chapter_Number']);

        $maxResult = $this->connection->query("SELECT COALESCE(MAX(Chapter_Number), 0) AS max_num FROM Chapters WHERE ID_Work = $idWork");
        $maxNumber = intval($maxResult->fetch()['max_num']);

        $location = VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-read.php?type=' . urlencode($type) . '&id=' . $idWork . '&idChapter=' . $idChapter . '&numberChapter=' . $chapterNumber;

        if ($chapterNumber !== $currentNumber) {
            if ($chapterNumber < $currentNumber) {
                $this->connection->query(
                    "UPDATE Chapters
                     SET Chapter_Number = Chapter_Number + 1
                     WHERE ID_Work = $idWork AND Chapter_Number >= $chapterNumber AND Chapter_Number < $currentNumber"
                );
            } elseif ($chapterNumber <= $maxNumber) {
                $this->connection->query(
                    "UPDATE Chapters
                     SET Chapter_Number = Chapter_Number - 1
                     WHERE ID_Work = $idWork AND Chapter_Number <= $chapterNumber AND Chapter_Number > $currentNumber"
                );
            }

            $this->connection->query(
                "UPDATE Chapters
                 SET Chapter_Number = $chapterNumber
                 WHERE ID_Chapter = $idChapter AND ID_Work = $idWork"
            );
        }

        $title = $this->connection->quote($_POST['title']);
        $description = $this->connection->quote($_POST['description']);

        $this->connection->query(
            "UPDATE Chapters
             SET Title = $title, Description = $description
             WHERE ID_Chapter = $idChapter AND ID_Work = $idWork"
        );

        $uploadErrors = [];
        $uploadSuccesses = [];
        if (!empty($_FILES['video']['name'])) {
            $result = (new UploadController())->uploadChapter($idWork, $idChapter, $type, $_FILES['video']);
            $this->handleUploadResult($result, $uploadErrors, $uploadSuccesses);
        }

        if (!empty($uploadErrors)) {
            setSuccess("Capítulo actualizado correctamente.");
            setError($uploadErrors, $location);
        } else {
            setSuccess("Capítulo actualizado correctamente.", $location);
        }
    }

    public function addChapter()
    {
        $type = $_POST['type'];
        $redirectType = strtolower($type);
        if (!in_array($redirectType, ['anime', 'manga']))
            $redirectType = 'anime';
        $idWork = intval($_POST['id_work']);
        $location = VIEW_URL . '/catalogs/work-detail.php?type=' . urlencode($type) . '&id=' . $idWork;

        $errors = [];

        if ($idWork <= 0)
            $errors[] = "La obra no es válida.";
        if (empty($_POST['title']) || strlen($_POST['title']) < 5)
            $errors[] = "El título es obligatorio y debe tener al menos 5 caracteres.";
        if (strlen($_POST['description']) < 10)
            $errors[] = "La descripción debe tener al menos 10 caracteres.";

        $fileLabel = ($type === 'Anime') ? 'un vídeo (MP4, WEBM, MOV o MKV)' : 'un archivo ZIP';
        if (empty($_FILES['video']['name']) || $_FILES['video']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = "Debes subir $fileLabel con el capítulo.";
        }

        $chapterNumber = isset($_POST['chapter_number']) ? intval($_POST['chapter_number']) : 0;

        if (!empty($errors)) {
            setError($errors, $location);
            return;
        }

        $nextQuery = $this->connection->query(
            "SELECT COALESCE(MAX(Chapter_Number), 0) + 1 AS next_num FROM Chapters WHERE ID_Work = $idWork"
        );
        $nextNumber = intval($nextQuery->fetch()['next_num']);

        if ($chapterNumber <= 0) {
            $chapterNumber = $nextNumber;
        } elseif ($chapterNumber < $nextNumber) {
            $duplicateQuery = $this->connection->query(
                "SELECT 1 FROM Chapters WHERE ID_Work = $idWork AND Chapter_Number = $chapterNumber LIMIT 1"
            );
            $duplicateRow = $duplicateQuery ? $duplicateQuery->fetch() : false;
            if ($duplicateRow) {
                setError([
                    "El número de capítulo $chapterNumber ya está asignado a otro capítulo. Elige otro número o deja el campo vacío para usar el siguiente número disponible."
                ], $location);
                return;
            }
        }

        $title = $this->connection->quote($_POST['title']);
        $description = $this->connection->quote($_POST['description']);

        $inserted = $this->connection->query(
            "INSERT INTO Chapters (Title, Description, Chapter_Number, ID_Work)
             VALUES ($title, $description, $chapterNumber, $idWork)"
        );

        if (!$inserted) {
            setError(["Error al crear el capítulo. Puede que el número de capítulo ya exista."], $location);
            return;
        }

        $idChapter = intval($this->connection->lastInsertId());
        $readLocation = VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-read.php?type=' . urlencode($type) . '&id=' . $idWork . '&idChapter=' . $idChapter . '&numberChapter=' . $chapterNumber;

        $uploadResult = (new UploadController())->uploadChapter($idWork, $idChapter, $type, $_FILES['video']);

        if (is_array($uploadResult)) {
            $this->connection->query("DELETE FROM Chapters WHERE ID_Chapter = $idChapter");
            setError($uploadResult, $location);
            return;
        }

        setSuccess($uploadResult, $readLocation);
    }

    public function deleteChapter()
    {
        $idChapter = intval($_POST['id_chapter']);
        $idWork = intval($_POST['id_work']);
        $type = $_POST['type'];
        $redirectType = strtolower($type);
        if (!in_array($redirectType, ['anime', 'manga']))
            $redirectType = 'anime';
        $location = VIEW_URL . '/catalogs/work-detail.php?type=' . urlencode($type) . '&id=' . $idWork;

        $result = $this->connection->query("SELECT Chapter_Number FROM Chapters WHERE ID_Chapter = $idChapter AND ID_Work = $idWork");
        $chapterRow = $result ? $result->fetch() : null;

        if (!$chapterRow) {
            setError(["No se encontró el capítulo solicitado."], $location);
            return;
        }

        $deletedNumber = intval($chapterRow['Chapter_Number']);

        $this->connection->query("DELETE FROM Chapters WHERE ID_Chapter = $idChapter");

        (new UploadController())->deleteChapterUploads($idWork, $idChapter, $type);

        setSuccess("Capítulo eliminado correctamente.", $location);
    }

    // Eventos

    public function eventDetail($id)
    {
        $id = intval($id);
        $eventQuery = $this->connection->query("SELECT * FROM Events WHERE ID_Event = $id");

        if ($eventRow = $eventQuery->fetch()) {
            $mediaQuery = $this->connection->query("SELECT * FROM Event_Media WHERE ID_Event = $id LIMIT 1");
            $mediaRow = $mediaQuery->fetch();

            return [
                'title' => $eventRow['Title'],
                'subtitle' => $eventRow['Subtitle'],
                'image' => $eventRow['Image'] ?? '',
                'description' => $eventRow['Description'],
                'premiere' => $eventRow['Date_event'],
                'location' => $eventRow['Location'],
                'capacity' => $eventRow['Capacity'],
                'active' => $eventRow['Active'],
                'video' => $mediaRow['Video'],
                'audio' => $mediaRow['Audio'],
                't_Video' => $mediaRow['Transcription_Video'],
                't_Audio' => $mediaRow['Transcription_Audio']
            ];
        }

        header('Location: ' . VIEW_URL . '/catalogs/events/event-catalog.php');
        exit();
    }

    public function createEvent()
    {
        $location = VIEW_URL . '/catalogs/events/event-catalog.php';
        $errors = [];

        if (empty($_POST['title']) || strlen($_POST['title']) < 5)
            $errors[] = "El título es obligatorio y debe tener al menos 5 caracteres.";
        if (strlen($_POST['subtitle']) < 5)
            $errors[] = "El subtítulo debe tener al menos 5 caracteres.";
        if (empty($_POST['date_event']))
            $errors[] = "La fecha del evento es obligatoria.";
        if (empty($_POST['location']))
            $errors[] = "El lugar es obligatorio.";
        if (empty($_POST['capacity']) || intval($_POST['capacity']) < 50)
            $errors[] = "El aforo es obligatorio y debe ser mínimo 50.";
        if (strlen($_POST['description']) < 10)
            $errors[] = "La descripción debe tener al menos 10 caracteres.";

        $hasImageFile = !empty($_FILES['image_file']['name']);
        $hasImageUrl = !empty($_POST['image_url']);

        if (!$hasImageFile && !$hasImageUrl)
            $errors[] = "Debes subir una imagen de portada o proporcionar una URL.";
        if ($hasImageFile && $hasImageUrl)
            $errors[] = "Solo puedes usar una opción para la imagen: archivo o URL, no ambas.";

        if (!empty($errors)) {
            setError($errors, $location);
            return;
        }

        $title = $this->connection->quote($_POST['title']);
        $subtitle = $this->connection->quote($_POST['subtitle']);
        $description = $this->connection->quote($_POST['description']);
        $dateEvent = $this->connection->quote($_POST['date_event']);
        $locationVal = $this->connection->quote($_POST['location']);
        $capacity = intval($_POST['capacity']);
        $image = $hasImageUrl ? $this->connection->quote($_POST['image_url']) : '';

        $this->connection->query("
            CALL sp_add_Event($title,$subtitle,$description,$image,$dateEvent,$locationVal,$capacity)
        ");

        $idResult = $this->connection->query("SELECT LAST_INSERT_ID() AS ID_Event");
        $idEvent = intval($idResult->fetch()['ID_Event']);

        $uploadErrors = [];
        $uploadSuccesses = [];

        if ($hasImageFile) {
            $result = (new UploadController())->uploadEventImage($idEvent, $_FILES['image_file']);
            $this->handleUploadResult($result, $uploadErrors, $uploadSuccesses);
        }

        if (!empty($uploadErrors)) {
            setSuccess("Evento creado correctamente.");
            setError($uploadErrors, $location);
        } else {
            setSuccess("Evento creado correctamente.", $location);
        }
    }

    public function updateEvent()
    {
        $id = intval($_POST['id']);
        $location = VIEW_URL . '/catalogs/events/event-catalog.php';
        $errors = [];

        if (empty($_POST['title']) || strlen($_POST['title']) < 5)
            $errors[] = "El título es obligatorio y debe tener al menos 5 caracteres.";
        if (strlen($_POST['subtitle']) < 5)
            $errors[] = "El subtítulo debe tener al menos 5 caracteres.";
        if (empty($_POST['date_event']))
            $errors[] = "La fecha del evento es obligatoria.";
        if (empty($_POST['location']))
            $errors[] = "El lugar es obligatorio.";
        if (empty($_POST['capacity']) || intval($_POST['capacity']) < 50)
            $errors[] = "El aforo es obligatorio y debe ser mínimo 50.";
        if (strlen($_POST['description']) < 10)
            $errors[] = "La descripción debe tener al menos 10 caracteres.";

        $hasImageFile = !empty($_FILES['image_file']['name']);
        $hasImageUrl = !empty($_POST['image_url']);
        $hasVideoFile = !empty($_FILES['video_file']['name']);
        $hasVideoUrl = !empty($_POST['video_url']);
        $hasAudioFile = !empty($_FILES['audio_file']['name']);
        $hasAudioUrl = !empty($_POST['audio_url']);

        if ($hasImageFile && $hasImageUrl)
            $errors[] = "Solo puedes usar una opción para la imagen: archivo o URL, no ambas.";
        if ($hasVideoFile && $hasVideoUrl)
            $errors[] = "Solo puedes usar una opción para el vídeo: archivo o URL, no ambas.";
        if ($hasAudioFile && $hasAudioUrl)
            $errors[] = "Solo puedes usar una opción para el audio: archivo o URL, no ambas.";

        if (!empty($errors)) {
            setError($errors, $location);
            return;
        }

        $title = $this->connection->quote($_POST['title']);
        $subtitle = $this->connection->quote($_POST['subtitle']);
        $description = $this->connection->quote($_POST['description']);
        $dateEvent = $this->connection->quote($_POST['date_event']);
        $locationVal = $this->connection->quote($_POST['location']);
        $capacity = intval($_POST['capacity']);
        $active = isset($_POST['active']) ? 1 : 0;

        $this->connection->query("
            UPDATE Events
            SET Title = $title, Subtitle = $subtitle, Description = $description,
                Date_event = $dateEvent, Location = $locationVal, Capacity = $capacity, Active = $active
            WHERE ID_Event = $id
        ");

        $uploadErrors = [];
        $uploadSuccesses = [];

        if ($hasImageUrl) {
            $imageUrl = $this->connection->quote($_POST['image_url']);
            $this->connection->query("UPDATE Events SET Image = $imageUrl WHERE ID_Event = $id");
        }
        if ($hasImageFile) {
            $result = (new UploadController())->uploadEventImage($id, $_FILES['image_file']);
            $this->handleUploadResult($result, $uploadErrors, $uploadSuccesses);
        }

        $mediaQuery = $this->connection->query("SELECT ID_Media FROM Event_Media WHERE ID_Event = $id LIMIT 1");
        $mediaRow = $mediaQuery->fetch();

        $videoValue = $hasVideoUrl ? $this->connection->quote($_POST['video_url']) : '';
        $audioValue = $hasAudioUrl ? $this->connection->quote($_POST['audio_url']) : '';
        $tVideo = $this->connection->quote($_POST['t_video'] ?? '');
        $tAudio = $this->connection->quote($_POST['t_audio'] ?? '');

        if ($mediaRow) {
            $idMedia = $mediaRow['ID_Media'];
            $this->connection->query("
                UPDATE Event_Media
                SET Transcription_Video = $tVideo, Transcription_Audio = $tAudio
                WHERE ID_Media = $idMedia
            ");
        } else {
            $this->connection->query("
                INSERT INTO Event_Media (ID_Event, Video, Audio, Transcription_Video, Transcription_Audio)
                VALUES ($id, $videoValue, $audioValue, $tVideo, $tAudio)
            ");
            $idMedia = intval($this->connection->lastInsertId());
        }

        if ($hasVideoFile) {
            $result = (new UploadController())->uploadEventVideo($id, $idMedia, $_FILES['video_file']);
            $this->handleUploadResult($result, $uploadErrors, $uploadSuccesses);
        }
        if ($hasAudioFile) {
            $result = (new UploadController())->uploadEventAudio($id, $idMedia, $_FILES['audio_file']);
            $this->handleUploadResult($result, $uploadErrors, $uploadSuccesses);
        }

        if (!empty($uploadErrors)) {
            setSuccess("Evento actualizado correctamente.");
            setError($uploadErrors, $location);
        } else {
            setSuccess("Evento actualizado correctamente.", $location);
        }
    }

    public function deleteEvent()
    {
        $id = intval($_POST['id']);
        $location = VIEW_URL . '/catalogs/events/event-catalog.php';

        $this->connection->query("DELETE FROM Events WHERE ID_Event = $id");
        (new UploadController())->deleteEventUploads($id);

        setSuccess("Evento eliminado correctamente.", $location);
    }
}

// Dispatcher

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catalog = new Catalog();

    if (isset($_POST['create_work']))
        $catalog->createWork();

    if (isset($_POST['edit_work']))
        $catalog->updateWork();

    if (isset($_POST['delete_work']))
        $catalog->deleteWork();

    if (isset($_POST['add_chapter']))
        $catalog->addChapter();

    if (isset($_POST['edit_chapter']))
        $catalog->updateChapter();

    if (isset($_POST['delete_chapter']))
        $catalog->deleteChapter();

    if (isset($_POST['create_event']))
        $catalog->createEvent();

    if (isset($_POST['edit_event']))
        $catalog->updateEvent();

    if (isset($_POST['delete_event']))
        $catalog->deleteEvent();
}
?>