<?php
/**
 * AUTH_CHECK.PHP
 * Incluir al inicio de cada página protegida.
 * Verifica si el usuario tiene una sesión activa.
 * Si no, redirige al login y detiene la ejecución.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    // Redirigir al login y bloquear caché del navegador
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header('Location: login.php');
    exit();
}

// Cabeceras anti-caché para que el botón "Atrás" no muestre la página
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
