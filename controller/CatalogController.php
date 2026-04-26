<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/UploadController.php';

class Catalog
{
    private $connection;

    public function __construct()
    {
        $this->connection = (new Database())->getConnection();
    }

    // paginación de los catalogos

    public function returnCatalog($type, $catalog)
    {
        // ── 1. Cuántos elementos hay en total
        if ($type === 'Works') {
            $queryTotal = mysqli_query($this->connection, "SELECT COUNT(*) AS total FROM Works WHERE Type = '$catalog'");
        }
        if ($type === 'Events') {
            $queryTotal = mysqli_query($this->connection, "SELECT COUNT(*) AS total FROM Events");
        }

        $fila = mysqli_fetch_assoc($queryTotal);
        $totalMedia = $fila['total'];

        // ── 2. Cuántos mostramos por página
        $limit = 6;

        // ── 3. Cuántas páginas necesitamos
        $totalPages = max(1, ceil($totalMedia / $limit));

        // ── 4. En qué página estamos (viene de ?page=N en la URL)
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

        // Seguridad: que la página no sea menor que 1 ni mayor que el total
        if ($page < 1)
            $page = 1;
        if ($page > $totalPages)
            $page = $totalPages;

        // ── 5. Calculamos desde qué registro empezamos
        $offset = ($page - 1) * $limit;

        // ── 6. Consulta (diferente según si son obras o eventos)
        if ($type === 'Events') {
            $sql = "SELECT * FROM Events LIMIT $limit OFFSET $offset";
        } else {
            $escapedCatalog = $this->connection->real_escape_string($catalog);
            $sql = "SELECT * FROM Works WHERE Type = '$escapedCatalog' LIMIT $limit OFFSET $offset";
        }

        $query = mysqli_query($this->connection, $sql);

        return [
            'page' => $page,
            'totalPages' => $totalPages,
            'query' => $query
        ];
    }

    //añadir (Anime o Manga)

    public function createWork()
    {
        $type = $_POST['type'];

        $redirectType = strtolower($type);
        if (!in_array($redirectType, ['anime', 'manga'])) {
            $redirectType = 'anime';
        }

        $location = VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-catalog.php';

        $errors = [];

        if (empty($_POST['title']) || strlen($_POST['title']) < 5) {
            $errors[] = "El título es obligatorio y debe tener al menos 5 caracteres.";
        }
        if (strlen($_POST['subtitle']) < 5) {
            $errors[] = "El subtítulo debe tener al menos 5 caracteres.";
        }
        if (empty($_POST['premiere_date'])) {
            $errors[] = "La fecha de estreno es obligatoria.";
        }
        if (empty($_POST['studio'])) {
            $errors[] = "El estudio/plataforma es obligatorio.";
        }
        if (empty($_POST['gender'])) {
            $errors[] = "El género es obligatorio.";
        }
        if (strlen($_POST['description']) < 10) {
            $errors[] = "La descripción debe tener al menos 10 caracteres.";
        }

        $hasImage = !empty($_FILES['image_file']['name']);
        $hasUrl = !empty($_POST['image_url']);

        if ($hasImage && $hasUrl) {
            $errors[] = "Solo puedes usar una opción: imagen de portada o URL, no ambas.";
        }

        if (!empty($errors)) {
            $this->exitMenssage($errors, $location);
            return;
        }

        $title = $this->connection->real_escape_string($_POST['title']);
        $subtitle = $this->connection->real_escape_string($_POST['subtitle']);
        $premiereDate = $this->connection->real_escape_string($_POST['premiere_date']);
        $studio = $this->connection->real_escape_string($_POST['studio']);
        $gender = $this->connection->real_escape_string($_POST['gender']);
        $description = $this->connection->real_escape_string($_POST['description']);

        $this->connection->query("
        CALL sp_add_Work(
            '$type',
            '$title',
            '$subtitle',
            '$studio',
            '$premiereDate',
            '$gender',
            '$description',
            @p_ID_Work
            )
        ");

        while ($this->connection->more_results()) {
            $this->connection->next_result();
        }

        // Ahora recuperar el OUT parameter
        $idResult = $this->connection->query("SELECT @p_ID_Work AS ID_Work");
        if (!$idResult) {
            $this->exitMenssage(["Error al obtener el ID de la obra creada."], $location);
            return;
        }
        $row = $idResult->fetch_assoc();
        $idWork = intval($row['ID_Work']);

        $this->connection->next_result();

        $mensajes = [];

        if ($hasUrl) {
            $imageUrl = $this->connection->real_escape_string($_POST['image_url']);
            $this->connection->query("UPDATE Works SET Image = '$imageUrl' WHERE ID_Work = $idWork");
        }
        if ($hasImage) {
            $uploadResult = (new UploadController())->uploadWork($idWork, $type, $_FILES['image_file']);
            if (is_array($uploadResult)) {
                $mensajes = array_merge($mensajes, $uploadResult);
            } else {
                $mensajes[] = $uploadResult;
            }
        }
        if (!empty($_FILES['trailer']['name'])) {
            $uploadResult = (new UploadController())->uploadWork($idWork, $type, $_FILES['trailer']);
            if (is_array($uploadResult)) {
                $mensajes = array_merge($mensajes, $uploadResult);
            } else {
                $mensajes[] = $uploadResult;
            }
        }

        $mensajes[] = "Se ha creado correctamente la obra.";
        $this->exitMenssage($mensajes, $location);
    }

    public function updateWork()
    {
        $id = intval($_POST['id']);
        $type = $_POST['type'];

        $redirectType = strtolower($type);
        if (!in_array($redirectType, ['anime', 'manga'])) {
            $redirectType = 'anime';
        }

        $location = VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-catalog.php';

        $errors = [];

        if (empty($_POST['title']) || strlen($_POST['title']) < 5) {
            $errors[] = "El título es obligatorio y debe tener al menos 5 caracteres.";
        }
        if (strlen($_POST['subtitle']) < 5) {
            $errors[] = "El subtítulo debe tener al menos 5 caracteres.";
        }
        if (empty($_POST['premiere_date'])) {
            $errors[] = "La fecha de estreno es obligatoria.";
        }
        if (empty($_POST['studio'])) {
            $errors[] = "El estudio/plataforma es obligatorio.";
        }
        if (empty($_POST['gender'])) {
            $errors[] = "El género es obligatorio.";
        }
        if (strlen($_POST['description']) < 10) {
            $errors[] = "La descripción debe tener al menos 10 caracteres.";
        }

        $hasImage = !empty($_FILES['image_file']['name']);
        $hasUrl = !empty($_POST['image_url']);

        if ($hasImage && $hasUrl) {
            $errors[] = "Solo puedes usar una opción: imagen de portada o URL, no ambas.";
        }

        if (!empty($errors)) {
            $this->exitMenssage($errors, $location);
            return;
        }

        $title = $this->connection->real_escape_string($_POST['title']);
        $subtitle = $this->connection->real_escape_string($_POST['subtitle']);
        $premiereDate = $this->connection->real_escape_string($_POST['premiere_date']);
        $studio = $this->connection->real_escape_string($_POST['studio']);
        $gender = $this->connection->real_escape_string($_POST['gender']);
        $description = $this->connection->real_escape_string($_POST['description']);
        $active = isset($_POST['active']) ? 1 : 0;

        $this->connection->query("
            UPDATE Works
            SET Title = '$title',
                Subtitle = '$subtitle',
                Date_premiere = '$premiereDate',
                Studio = '$studio',
                Gender = '$gender',
                Description = '$description',
                Active = $active
            WHERE ID_Work = $id
        ");

        $mensajes = [];

        if ($hasUrl) {
            $imageUrl = $this->connection->real_escape_string($_POST['image_url']);
            $this->connection->query("UPDATE Works SET Image = '$imageUrl' WHERE ID_Work = $id");
        }
        if ($hasImage) {
            $uploadResult = (new UploadController())->uploadWork($id, $type, $_FILES['image_file']);
            if (is_array($uploadResult)) {
                $mensajes = array_merge($mensajes, $uploadResult);
            } else {
                $mensajes[] = $uploadResult;
            }
        }
        if (!empty($_FILES['video']['name'])) {
            $uploadResult = (new UploadController())->uploadWork($id, $type, $_FILES['video']);
            if (is_array($uploadResult)) {
                $mensajes = array_merge($mensajes, $uploadResult);
            } else {
                $mensajes[] = $uploadResult;
            }
        }

        $mensajes[] = "La obra se ha actualizado correctamente.";
        $this->exitMenssage($mensajes, $location);
    }

    public function deleteWork()
    {
        $id = intval($_POST['id']);
        $type = $_POST['type'];

        $redirectType = strtolower($type);
        if (!in_array($redirectType, ['anime', 'manga'])) {
            $redirectType = 'anime';
        }

        $location = VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-catalog.php';

        // Eliminar de BD (los capítulos se borran solos por el CASCADE)
        $this->connection->query("DELETE FROM Works WHERE ID_Work = $id");

        // Eliminar los archivos del servidor
        (new UploadController())->deleteWorkUploads($id, $type);

        $this->exitMenssage(["La obra se ha eliminado correctamente."], $location);
    }

    public function returnWorkDetail($id, $type)
    {
        $id = intval($id);

        $workQuery = $this->connection->query("SELECT * FROM Works WHERE ID_Work = $id AND Type = '$type'");
        if ($workRow = $workQuery->fetch_assoc()) {

            $chapQuery = $this->connection->query("SELECT * FROM Chapters WHERE ID_Work = $id ORDER BY Chapter_Number ASC");
            $chapters = [];
            while ($ch = $chapQuery->fetch_assoc()) {
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
        if ($redirectType !== 'manga') {
            $redirectType = 'anime';
        }
        header('Location: ' . VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-catalog.php');
        exit();
    }

    public function returnChapter($id, $idChapter, $chapterNumber, $type)
    {
        $id = intval($id);
        $idChapter = intval($idChapter);
        $chapterNumber = intval($chapterNumber);

        $chapQuery = $this->connection->query("SELECT * FROM Chapters WHERE ID_Chapter = $idChapter AND ID_Work = $id AND Chapter_Number = $chapterNumber");
        if ($chapRow = $chapQuery->fetch_assoc()) {

            $prev = $this->connection->query(
                "SELECT ID_Chapter, Chapter_Number FROM Chapters WHERE ID_Work = $id AND Chapter_Number < {$chapRow['Chapter_Number']} ORDER BY Chapter_Number DESC LIMIT 1"
            )->fetch_assoc();

            $next = $this->connection->query(
                "SELECT ID_Chapter, Chapter_Number FROM Chapters WHERE ID_Work = $id AND Chapter_Number > {$chapRow['Chapter_Number']} ORDER BY Chapter_Number ASC LIMIT 1"
            )->fetch_assoc();

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
        if ($redirectType !== 'manga') {
            $redirectType = 'anime';
        }
        header('Location: ' . VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-catalog.php');
        exit();
    }

    public function addChapter()
    {
        $type = $_POST['type'];

        $redirectType = strtolower($type);
        if (!in_array($redirectType, ['anime', 'manga'])) {
            $redirectType = 'anime';
        }

        $location = VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-catalog.php';

        $errors = [];
        $idWork = intval($_POST['id_work']);

        if ($idWork <= 0) {
            $errors[] = "La obra no es válida.";
        }
        if (empty($_POST['title']) || strlen($_POST['title']) < 5) {
            $errors[] = "El título es obligatorio y debe tener al menos 5 caracteres.";
        }
        if (strlen($_POST['description']) < 10) {
            $errors[] = "La descripción debe tener al menos 10 caracteres.";
        }
        if (empty($_FILES['video']['name'])) {
            $errors[] = "Debes subir un archivo ZIP con el capítulo.";
        }

        if (!empty($errors)) {
            $this->exitMenssage($errors, $location);
            return;
        }

        $result = $this->connection->query(
            "SELECT COALESCE(MAX(Chapter_Number), 0) + 1 AS next_num 
             FROM Chapters WHERE ID_Work = $idWork"
        );
        $number = intval($result->fetch_assoc()['next_num']);

        $title = $this->connection->real_escape_string($_POST['title']);
        $description = $this->connection->real_escape_string($_POST['description']);

        // Insertar el capítulo en BD
        $result = $this->connection->query("
            INSERT INTO Chapters (Title, Description, Chapter_Number, ID_Work)
            VALUES ('$title', '$description', $number, $idWork)
        ");

        if (!$result) {
            $this->exitMenssage(["Error al crear el capítulo. Puede que el número de capítulo ya exista."], $location);
            return;
        }

        $idChapter = $this->connection->insert_id;

        // Subir el ZIP y descomprimirlo
        $mensajes = [];
        $uploadResult = (new UploadController())->uploadChapter($idWork, $idChapter, $type, $_FILES['video']);
        if (is_array($uploadResult)) {
            $mensajes = array_merge($mensajes, $uploadResult);
        } else {
            $mensajes[] = $uploadResult;
        }

        $mensajes[] = "Capítulo añadido correctamente.";
        $this->exitMenssage($mensajes, $location);
    }

    public function deleteChapter()
    {
        $idChapter = intval($_POST['id_chapter']);
        $idWork = intval($_POST['id_work']);
        $type = $_POST['type'];

        $redirectType = strtolower($type);
        if (!in_array($redirectType, ['anime', 'manga'])) {
            $redirectType = 'anime';
        }

        $location = VIEW_URL . '/catalogs/' . $redirectType . '/' . $redirectType . '-catalog.php';

        // Eliminar de BD
        $this->connection->query("DELETE FROM Chapters WHERE ID_Chapter = $idChapter");

        // Eliminar archivos del servidor
        (new UploadController())->deleteChapterUploads($idWork, $idChapter, $type);

        $this->exitMenssage(["Capítulo eliminado correctamente."], $location);
    }

    //eventos

    public function eventDetail($id)
    {
        $id = intval($id);
        $eventQuery = $this->connection->query("SELECT * FROM Events WHERE ID_Event = $id");

        if ($eventRow = $eventQuery->fetch_assoc()) {

            // Buscar la media del evento si tiene
            $mediaQuery = $this->connection->query("SELECT * FROM Event_Media WHERE ID_Event = $id LIMIT 1");
            $mediaRow = $mediaQuery->fetch_assoc();

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

        // Si no existe el evento, redirigir al catálogo
        header('Location: ' . VIEW_URL . '/catalogs/events/event-catalog.php');
        exit();
    }

    public function createEvent()
    {
        $location = VIEW_URL . '/catalogs/events/event-catalog.php';
        $errors = [];

        if (empty($_POST['title']) || strlen($_POST['title']) < 5) {
            $errors[] = "El título es obligatorio y debe tener al menos 5 caracteres.";
        }
        if (strlen($_POST['subtitle']) < 5) {
            $errors[] = "El subtítulo debe tener al menos 5 caracteres.";
        }
        if (empty($_POST['date_event'])) {
            $errors[] = "La fecha del evento es obligatoria.";
        }
        if (empty($_POST['location'])) {
            $errors[] = "El lugar es obligatorio.";
        }
        if (empty($_POST['capacity']) || intval($_POST['capacity']) < 50) {
            $errors[] = "El aforo es obligatorio y debe ser mínimo 50.";
        }
        if (strlen($_POST['description']) < 10) {
            $errors[] = "La descripción debe tener al menos 10 caracteres.";
        }

        // Imagen obligatoria, solo una opción
        $hasImageFile = !empty($_FILES['image_file']['name']);
        $hasImageUrl = !empty($_POST['image_url']);

        if (!$hasImageFile && !$hasImageUrl) {
            $errors[] = "Debes subir una imagen de portada o proporcionar una URL.";
        }
        if ($hasImageFile && $hasImageUrl) {
            $errors[] = "Solo puedes usar una opción para la imagen: archivo o URL, no ambas.";
        }

        if (!empty($errors)) {
            $this->exitMenssage($errors, $location);
            return;
        }

        $title = $this->connection->real_escape_string($_POST['title']);
        $subtitle = $this->connection->real_escape_string($_POST['subtitle']);
        $description = $this->connection->real_escape_string($_POST['description']);
        $dateEvent = $this->connection->real_escape_string($_POST['date_event']);
        $locationVal = $this->connection->real_escape_string($_POST['location']);
        $capacity = intval($_POST['capacity']);

        // Si es URL la guardamos ya; si es archivo, de momento vacío (se actualiza después)
        $image = $hasImageUrl ? $this->connection->real_escape_string($_POST['image_url']) : '';

        $this->connection->query("
            CALL sp_add_Event(
                '$title',
                '$subtitle',
                '$description',
                '$image',
                '$dateEvent',
                '$locationVal',
                $capacity
            )
        ");

        // Obtener el id del evento recién insertado
        $idResult = $this->connection->query("SELECT LAST_INSERT_ID() AS ID_Event");
        $idEvent = intval($idResult->fetch_assoc()['ID_Event']);

        $mensajes = [];

        if ($hasImageFile) {
            $uploadResult = (new UploadController())->uploadEventImage($idEvent, $_FILES['image_file']);
            if (is_array($uploadResult)) {
                $mensajes = array_merge($mensajes, $uploadResult);
            } else {
                $mensajes[] = $uploadResult;
            }
        }

        $mensajes[] = "Evento creado correctamente.";
        $this->exitMenssage($mensajes, $location);
    }

    public function updateEvent()
    {
        $id = intval($_POST['id']);
        $location = VIEW_URL . '/catalogs/events/event-catalog.php';
        $errors = [];

        if (empty($_POST['title']) || strlen($_POST['title']) < 5) {
            $errors[] = "El título es obligatorio y debe tener al menos 5 caracteres.";
        }
        if (strlen($_POST['subtitle']) < 5) {
            $errors[] = "El subtítulo debe tener al menos 5 caracteres.";
        }
        if (empty($_POST['date_event'])) {
            $errors[] = "La fecha del evento es obligatoria.";
        }
        if (empty($_POST['location'])) {
            $errors[] = "El lugar es obligatorio.";
        }
        if (empty($_POST['capacity']) || intval($_POST['capacity']) < 50) {
            $errors[] = "El aforo es obligatorio y debe ser mínimo 50.";
        }
        if (strlen($_POST['description']) < 10) {
            $errors[] = "La descripción debe tener al menos 10 caracteres.";
        }

        // Imagen: solo una opción
        $hasImageFile = !empty($_FILES['image_file']['name']);
        $hasImageUrl = !empty($_POST['image_url']);

        if ($hasImageFile && $hasImageUrl) {
            $errors[] = "Solo puedes usar una opción para la imagen: archivo o URL, no ambas.";
        }

        // Vídeo: solo una opción
        $hasVideoFile = !empty($_FILES['video_file']['name']);
        $hasVideoUrl = !empty($_POST['video_url']);

        if ($hasVideoFile && $hasVideoUrl) {
            $errors[] = "Solo puedes usar una opción para el vídeo: archivo o URL, no ambas.";
        }

        // Audio: solo una opción
        $hasAudioFile = !empty($_FILES['audio_file']['name']);
        $hasAudioUrl = !empty($_POST['audio_url']);

        if ($hasAudioFile && $hasAudioUrl) {
            $errors[] = "Solo puedes usar una opción para el audio: archivo o URL, no ambas.";
        }

        if (!empty($errors)) {
            $this->exitMenssage($errors, $location);
            return;
        }

        $title = $this->connection->real_escape_string($_POST['title']);
        $subtitle = $this->connection->real_escape_string($_POST['subtitle']);
        $description = $this->connection->real_escape_string($_POST['description']);
        $dateEvent = $this->connection->real_escape_string($_POST['date_event']);
        $locationVal = $this->connection->real_escape_string($_POST['location']);
        $capacity = intval($_POST['capacity']);
        $active = isset($_POST['active']) ? 1 : 0;

        // Actualizar los campos de texto del evento
        $this->connection->query("
            UPDATE Events
            SET Title = '$title',
                Subtitle = '$subtitle',
                Description = '$description',
                Date_event = '$dateEvent',
                Location = '$locationVal',
                Capacity = $capacity,
                Active = $active
            WHERE ID_Event = $id
        ");

        $mensajes = [];

        //imagen
        if ($hasImageUrl) {
            $imageUrl = $this->connection->real_escape_string($_POST['image_url']);
            $this->connection->query("UPDATE Events SET Image = '$imageUrl' WHERE ID_Event = $id");
        }
        if ($hasImageFile) {
            $uploadResult = (new UploadController())->uploadEventImage($id, $_FILES['image_file']);
            if (is_array($uploadResult)) {
                $mensajes = array_merge($mensajes, $uploadResult);
            } else {
                $mensajes[] = $uploadResult;
            }
        }

        // video o audio
        // Miramos si ya existe un registro de media para este evento
        $mediaQuery = $this->connection->query("SELECT ID_Media FROM Event_Media WHERE ID_Event = $id LIMIT 1");
        $mediaRow = $mediaQuery->fetch_assoc();

        $videoValue = $hasVideoUrl ? $this->connection->real_escape_string($_POST['video_url']) : '';
        $audioValue = $hasAudioUrl ? $this->connection->real_escape_string($_POST['audio_url']) : '';
        $tVideo = $this->connection->real_escape_string($_POST['t_video'] ?? '');
        $tAudio = $this->connection->real_escape_string($_POST['t_audio'] ?? '');

        if ($mediaRow) {
            // Ya existe → actualizar
            $idMedia = $mediaRow['ID_Media'];
            $this->connection->query("
                UPDATE Event_Media
                SET Transcription_Video = '$tVideo',
                    Transcription_Audio = '$tAudio'
                WHERE ID_Media = $idMedia
            ");
        } else {
            // No existe → insertar
            $this->connection->query("
                INSERT INTO Event_Media (ID_Event, Video, Audio, Transcription_Video, Transcription_Audio)
                VALUES ($id, '$videoValue', '$audioValue', '$tVideo', '$tAudio')
            ");
            $idMedia = $this->connection->insert_id;
        }

        // Subir archivo de vídeo si se proporcionó
        if ($hasVideoFile) {
            $uploadResult = (new UploadController())->uploadEventVideo($id, $idMedia, $_FILES['video_file']);
            if (is_array($uploadResult)) {
                $mensajes = array_merge($mensajes, $uploadResult);
            } else {
                $mensajes[] = $uploadResult;
            }
        }

        // Subir archivo de audio si se proporcionó
        if ($hasAudioFile) {
            $uploadResult = (new UploadController())->uploadEventAudio($id, $idMedia, $_FILES['audio_file']);
            if (is_array($uploadResult)) {
                $mensajes = array_merge($mensajes, $uploadResult);
            } else {
                $mensajes[] = $uploadResult;
            }
        }

        $mensajes[] = "El evento se ha actualizado correctamente.";
        $this->exitMenssage($mensajes, $location);
    }

    public function deleteEvent()
    {
        $id = intval($_POST['id']);
        $location = VIEW_URL . '/catalogs/events/event-catalog.php';

        // Eliminar de BD (Event_Media se borra solo por CASCADE)
        $this->connection->query("DELETE FROM Events WHERE ID_Event = $id");

        // Eliminar los archivos del servidor
        (new UploadController())->deleteEventUploads($id);

        $this->exitMenssage(["Evento eliminado correctamente."], $location);
    }

    //salir con mensaje
    public function exitMenssage($message, $location)
    {
        if (!isset($_SESSION['login_error']) || !is_array($_SESSION['login_error'])) {
            $_SESSION['login_error'] = [];
        }
        if (is_array($message)) {
            $_SESSION['login_error'] = array_merge($_SESSION['login_error'], $message);
        } else {
            $_SESSION['login_error'][] = $message;
        }
        header("Location: " . $location);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $catalog = new Catalog();

    if (isset($_POST['create_work'])) {
        $catalog->createWork();
    }
    if (isset($_POST['edit_work'])) {
        $catalog->updateWork();
    }
    if (isset($_POST['delete_work'])) {
        $catalog->deleteWork();
    }
    if (isset($_POST['add_chapter'])) {
        $catalog->addChapter();
    }
    if (isset($_POST['delete_chapter'])) {
        $catalog->deleteChapter();
    }
    if (isset($_POST['create_event'])) {
        $catalog->createEvent();
    }
    if (isset($_POST['edit_event'])) {
        $catalog->updateEvent();
    }
    if (isset($_POST['delete_event'])) {
        $catalog->deleteEvent();
    }
}
?>