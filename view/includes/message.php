<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

//Mensajes de exito
function setSuccess($msg) {
    $_SESSION['flash_success'][] = $msg;
}

//Mensajes de error
function setError($msg, $location = null) {
    if (!isset($_SESSION['flash_error']) || !is_array($_SESSION['flash_error'])) {
        $_SESSION['flash_error'] = [];
    }
    if (is_array($msg)) {
        $_SESSION['flash_error'] = array_merge($_SESSION['flash_error'], $msg);
    } else {
        $_SESSION['flash_error'][] = $msg;
    }
    if ($location) {
        header("Location: " . $location);
        exit();
    }
}

$messages = [];
if (!empty($_SESSION['flash_error']) && is_array($_SESSION['flash_error'])) {
    $messages = array_merge($messages, $_SESSION['flash_error']);
    unset($_SESSION['flash_error']);
}
if (!empty($_SESSION['flash_success']) && is_array($_SESSION['flash_success'])) {
    $messages = array_merge($messages, $_SESSION['flash_success']);
    unset($_SESSION['flash_success']);
}

if (!empty($messages)) {
    echo '<script>';
    foreach ($messages as $message) {
        $safeMessage = addslashes(htmlspecialchars($message));
        echo "alert('$safeMessage');";
    }
    echo '</script>';
}
?>