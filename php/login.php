<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Indicar que la respuesta será en formato JSON
header('Content-Type: application/json');

require_once 'conexion.php';

try {
    // Capturamos la conexión central
    $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);

    // Recibir los datos del POST (enviados por AJAX)
    $usuarioIngresado = $_POST['user'] ?? '';
    $passwordIngresada = $_POST['pass'] ?? '';

    if (empty($usuarioIngresado) || empty($passwordIngresada)) {
        echo json_encode(["exito" => false, "mensaje" => "Faltan datos."]);
        exit;
    }

    // Preparar la consulta SQL para buscar al usuario (Tabla Usuarios en mayúscula)
    $sql = "SELECT ID_USUARIO, PASSWORD_HASH, ESTADO FROM Usuarios WHERE USERNAME = :usuario LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuario', $usuarioIngresado);
    $stmt->execute();

    $usuarioFila = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validar si el usuario existe y si la contraseña coincide con el hash
    if ($usuarioFila && password_verify($passwordIngresada, $usuarioFila['PASSWORD_HASH'])) {
        
        // Verificar si el usuario está activo
        if ($usuarioFila['ESTADO'] != 1) {
            echo json_encode(["exito" => false, "mensaje" => "Usuario suspendido."]);
            exit;
        }

        // Guardar en sesión PHP
        $_SESSION['usuario_id'] = $usuarioFila['ID_USUARIO'];
        $_SESSION['usuario_nombre'] = $usuarioIngresado;

        echo json_encode([
            "exito" => true,
            "mensaje" => "Login correcto",
            "usuario" => $usuarioIngresado,
            "redirect" => "dashboard2.php"
        ], JSON_UNESCAPED_UNICODE);

    }
    else {
        // Credenciales incorrectas
        echo json_encode([
            "exito" => false,
            "mensaje" => "Usuario o contraseña incorrectos."
        ], JSON_UNESCAPED_UNICODE);
    }

}
catch (PDOException $e) {
    echo json_encode(["exito" => false, "mensaje" => "Error de base de datos."]);
}