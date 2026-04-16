<?php
session_start();
// Destruir todos los datos de sesión
session_unset();
session_destroy();

// Borrar cookie de sesión para reforzar el cierre
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Cabeceras anti-caché para que el botón "Atrás" no muestre el dashboard
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirigir al login
header('Location: ../login.php');
exit();
?>
