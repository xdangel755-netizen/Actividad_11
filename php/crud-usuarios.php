<?php
header('Content-Type: application/json');

$host = 'localhost';
$db   = 'MATRICULA';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);

    $opcion = $_POST['opcion'] ?? '4';

    switch ($opcion) {
        case '1': // CREAR USUARIO
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (USERNAME, PASSWORD_HASH, ESTADO) VALUES (?, ?, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['username'], $hash]);
            echo json_encode(["exito" => true, "mensaje" => "Administrador registrado correctamente."]);
            break;

        case '2': // CAMBIAR ESTADO (Activo/Inactivo)
            $nuevo_estado = $_POST['estado'] == '1' ? '0' : '1';
            $sql = "UPDATE usuarios SET ESTADO = ? WHERE ID_USUARIO = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nuevo_estado, $_POST['id_usuario']]);
            echo json_encode(["exito" => true, "mensaje" => "Estado actualizado."]);
            break;

        case '3': // ELIMINAR USUARIO
            // Prevenir borrar cuenta admin principal u otras lógicas
            $sql = "DELETE FROM usuarios WHERE ID_USUARIO = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['id_usuario']]);
            echo json_encode(["exito" => true, "mensaje" => "Administrador eliminado."]);
            break;

        case '4': // LISTAR USUARIOS
            $sql = "SELECT ID_USUARIO, USERNAME, ESTADO FROM usuarios ORDER BY ID_USUARIO ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($usuarios);
            break;

        case '5': // EDITAR USUARIO
            if (!empty($_POST['password'])) {
                // Actualiza usuario y contraseña
                $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET USERNAME = ?, PASSWORD_HASH = ? WHERE ID_USUARIO = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['username'], $hash, $_POST['id_usuario']]);
            } else {
                // Actualiza solo usuario
                $sql = "UPDATE usuarios SET USERNAME = ? WHERE ID_USUARIO = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['username'], $_POST['id_usuario']]);
            }
            echo json_encode(["exito" => true, "mensaje" => "Administrador actualizado correctamente."]);
            break;

        default:
            echo json_encode(["exito" => false, "mensaje" => "Opción no válida."]);
    }

} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Integridad de clave única
        echo json_encode(["exito" => false, "mensaje" => "Este nombre de usuario ya existe."]);
    } else {
        echo json_encode(["exito" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
    }
}
?>
