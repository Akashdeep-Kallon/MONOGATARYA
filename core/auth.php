<?php

function requireLogin()
{
    if ($_SESSION['status'] === 'guest') {
        header("Location: " . AUTH_URL . "/login.php");
        exit();
    }
}

function requireRole($role)
{
    if ($_SESSION['status'] !== $role) {
        http_response_code(403);
        exit("No autorizado");
    }
}

function isPromoter()
{
    return isset($_SESSION['status']) && $_SESSION['status'] === 'promoter';
}

function isLogged()
{
    return isset($_SESSION['status']) && $_SESSION['status'] !== 'guest';
}

function isRole($role)
{
    return isset($_SESSION['status']) && $_SESSION['status'] === $role;
}

function requiereActive($active)
{
    if (!$active) {
        http_response_code(403);
        exit("No autorizado");
    }
}

?>