<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!empty($_SESSION['login_error']) && is_array($_SESSION['login_error'])) {
    echo '<div class="error-box" role="alert">';
    echo '<ul>';
    foreach ($_SESSION['login_error'] as $message) {
        echo '<li>' . htmlspecialchars($message) . '</li>';
    }
    echo '</ul>';
    echo '</div>';
    unset($_SESSION['login_error']);
}
?>