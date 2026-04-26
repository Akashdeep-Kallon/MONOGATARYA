// Mensajes de exito que orientan al usuario sobre acciones

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function setSuccess($msg) {
    $_SESSION['flash_success'][] = $msg;
}