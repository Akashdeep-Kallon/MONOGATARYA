<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

//Mensajes de exito
function setSuccess($msg) {
    $_SESSION['flash_success'][] = $msg;
}

$messages = [];
if (!empty($_SESSION['login_error']) && is_array($_SESSION['login_error'])) {
    $messages = array_merge($messages, $_SESSION['login_error']);
    unset($_SESSION['login_error']);
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