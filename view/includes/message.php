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
    echo '<div class="toast-container" id="toastContainer">';
    $index = 0;
    foreach ($messages as $message) {
        $type = in_array($message, $_SESSION['login_error'] ?? []) ? 'error' : 'success';
        $icon = $type === 'success' ? '✓' : '✕';
        echo '<div class="toast ' . $type . '" id="toast' . $index . '">';
        echo '<span class="toast-icon">' . $icon . '</span>';
        echo '<div class="toast-content">';
        echo '<div class="toast-title">' . ($type === 'success' ? 'Éxito' : 'Error') . '</div>';
        echo '<p class="toast-message">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '</div>';
        echo '<button class="toast-close" onclick="closeToast(' . $index . ')">×</button>';
        echo '</div>';
        $index++;
    }
    echo '</div>';
    echo '<script>';
    echo 'function closeToast(index) {';
    echo '    const toast = document.getElementById("toast" + index);';
    echo '    if (toast) {';
    echo '        toast.classList.remove("show");';
    echo '        setTimeout(() => toast.remove(), 300);';
    echo '    }';
    echo '}';
    echo 'function showToasts() {';
    echo '    const toasts = document.querySelectorAll(".toast");';
    echo '    toasts.forEach((toast, index) => {';
    echo '        setTimeout(() => {';
    echo '            toast.classList.add("show");';
    echo '            setTimeout(() => closeToast(index), 5000);';
    echo '        }, index * 200);';
    echo '    });';
    echo '}';
    echo 'document.addEventListener("DOMContentLoaded", showToasts);';
    echo '</script>';
}
?>