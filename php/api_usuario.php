<?php
// api_usuario.php - Gestión de Administradores RESTful
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
require_once 'conexion.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$datos = json_decode(file_get_contents('php://input'), true);

try {
    switch ($metodo) {
        case 'GET':
            if (isset($_GET['id'])) {
                $sql = "SELECT ID_USUARIO, USERNAME, ESTADO FROM usuarios WHERE ID_USUARIO = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['id']]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(["exito" => true, "usuario" => $usuario]);
            } else {
                $sql = "SELECT ID_USUARIO, USERNAME, ESTADO FROM usuarios ORDER BY ID_USUARIO DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(["exito" => true, "data" => $usuarios]);
            }
            break;

        case 'POST':
            $hash = password_hash($datos['PASSWORD'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (USERNAME, PASSWORD_HASH, ESTADO) VALUES (?, ?, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$datos['USERNAME'], $hash]);
            echo json_encode(["exito" => true, "mensaje" => "Administrador registrado."]);
            break;

        case 'PUT':
            if (!isset($_GET['id'])) break;
            
            if (isset($datos['toggle'])) {
                // Lógica de cambio de estado
                $nuevo_estado = $datos['estado'] == '1' ? '0' : '1';
                $sql = "UPDATE usuarios SET ESTADO = ? WHERE ID_USUARIO = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nuevo_estado, $_GET['id']]);
                echo json_encode(["exito" => true, "mensaje" => "Estado actualizado."]);
            } else {
                // Lógica de edición normal
                if (!empty($datos['PASSWORD'])) {
                    $hash = password_hash($datos['PASSWORD'], PASSWORD_DEFAULT);
                    $sql = "UPDATE usuarios SET USERNAME = ?, PASSWORD_HASH = ? WHERE ID_USUARIO = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$datos['USERNAME'], $hash, $_GET['id']]);
                } else {
                    $sql = "UPDATE usuarios SET USERNAME = ? WHERE ID_USUARIO = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$datos['USERNAME'], $_GET['id']]);
                }
                echo json_encode(["exito" => true, "mensaje" => "Usuario actualizado."]);
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) break;
            $sql = "DELETE FROM usuarios WHERE ID_USUARIO = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_GET['id']]);
            echo json_encode(["exito" => true, "mensaje" => "Administrador eliminado."]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["exito" => false, "mensaje" => $e->getMessage()]);
}
