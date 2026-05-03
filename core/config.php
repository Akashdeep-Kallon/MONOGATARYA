<?php
// Iniciar sesión
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Estado por defecto SOLO si no inicia sesión
if (!isset($_SESSION['status'])) {
    $_SESSION['status'] = 'guest';
}

// Anti-caché
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Constantes de URLs
define('BASE_URL', '/DAM-Transversal');
define('ASSETS_URL', BASE_URL . '/view/assets');
define('VIEW_URL', BASE_URL . '/view');
define('CONTROLLER_URL', BASE_URL . '/controller');
define('AUTH_URL', BASE_URL . '/view/auth');

define('ANIME_URL', '/uploads/Anime/');
define('EVENT_URL', '/uploads/Event/');
define('MANGA_URL', '/uploads/Manga/');
define('USER_URL', '/uploads/User/');


function getAssetVersion()
{
    if (!isset($_SESSION['asset_version'])) {
        $gitFile = $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/.git/HEAD';
        if (file_exists($gitFile)) {
            $head = trim(file_get_contents($gitFile));
            $refPath = $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/.git/' . str_replace('ref: ', '', $head);
            if (file_exists($refPath)) {
                $_SESSION['asset_version'] = substr(trim(file_get_contents($refPath)), 0, 8);
            } else {
                $_SESSION['asset_version'] = substr($head, 0, 8);
            }
        } else {
            $_SESSION['asset_version'] = '1';
        }
    }
    return $_SESSION['asset_version'];
}

function getCoverImageUrl($image, $type)
{
    if (empty($image)) {
        return ASSETS_URL . '/img/background-image.webp';
    }

    $image = trim($image);

    $isExternal = str_starts_with($image, 'http://') || str_starts_with($image, 'https://');
    if ($isExternal) {
        return $image;
    }

    $base = strtolower($type) === 'manga' ? MANGA_URL : ANIME_URL;
    $url = $base . $image;

    $diskBase = strtolower($type) === 'manga' ? '/var/www/uploads/Manga/' : '/var/www/uploads/Anime/';
    $filePath = $diskBase . $image;
    $v = file_exists($filePath) ? filemtime($filePath) : 1;

    return $url . '?v=' . $v;
}

?>