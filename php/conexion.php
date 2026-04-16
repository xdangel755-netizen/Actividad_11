<?php
// conexion.php - Centralización de la conexión PDO
$host = 'localhost';
$db = 'MATRICULA';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Usamos Case Upper para consistencia con el código existente si es necesario
    // $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);
} catch (PDOException $e) {
    // Si es una petición de API, devolvemos JSON
    if (strpos($_SERVER['REQUEST_URI'], 'api_') !== false) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["exito" => false, "mensaje" => "Error de conexión a la BD."]);
        exit;
    }
    die("Error de conexión: " . $e->getMessage());
}
?>
