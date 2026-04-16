<?php
// api_estudiante.php - Gestión de Alumnos
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

$metodoHttp = $_SERVER['REQUEST_METHOD'];
$datos = json_decode(file_get_contents('php://input'), true);

switch ($metodoHttp) {
    case 'GET':
        if (isset($_GET['id'])) {
            $sql = 'SELECT * FROM Alumnos WHERE ID_ALUMNO = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            $alumno = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($alumno) {
                echo json_encode(["exito" => true, "alumno" => $alumno]);
            } else {
                http_response_code(404);
                echo json_encode(["exito" => false, "mensaje" => "Alumno no encontrado."]);
            }
        } else {
            $sql = 'SELECT * FROM Alumnos ORDER BY ID_ALUMNO DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["exito" => true, "data" => $alumnos]);
        }
        break;

    case 'POST':
        // Campos obligatorios para crear un alumno
        $required = ['DNI_ALUMNO', 'NOMBRES', 'APELLIDO', 'CORREO', 'FECHA_NACIMIENTO'];
        foreach($required as $field) {
            if(empty($datos[$field])) {
                http_response_code(400);
                echo json_encode(["exito" => false, "mensaje" => "Campo obligatorio faltante: $field"]);
                exit;
            }
        }

        try {
            $sql = "INSERT INTO Alumnos (DNI_ALUMNO, NOMBRES, APELLIDO, FECHA_NACIMIENTO, EDAD, GENERO, DIRECCION, CELULAR, CORREO, NOMBRE_APODERADO, CELULAR_APODERADO, USERNAME, PASSWORD_HASH) 
                    VALUES (:dni, :nom, :ape, :fecha, :edad, :gen, :dir, :cel, :cor, :apod, :cel_apod, :user, :pass)";
            $stmt = $pdo->prepare($sql);
            
            // Si no se envía username, usamos el DNI por defecto
            $username = !empty($datos['USERNAME']) ? $datos['USERNAME'] : $datos['DNI_ALUMNO'];
            $pass = password_hash($datos['DNI_ALUMNO'], PASSWORD_DEFAULT);

            $stmt->execute([
                ':dni'      => $datos['DNI_ALUMNO'],
                ':nom'      => $datos['NOMBRES'],
                ':ape'      => $datos['APELLIDO'],
                ':fecha'    => $datos['FECHA_NACIMIENTO'],
                ':edad'     => isset($datos['EDAD']) ? $datos['EDAD'] : 0,
                ':gen'      => isset($datos['GENERO']) ? $datos['GENERO'] : 'M',
                ':dir'      => isset($datos['DIRECCION']) ? $datos['DIRECCION'] : '',
                ':cel'      => isset($datos['CELULAR']) ? $datos['CELULAR'] : '',
                ':cor'      => $datos['CORREO'],
                ':apod'     => isset($datos['NOMBRE_APODERADO']) ? $datos['NOMBRE_APODERADO'] : '',
                ':cel_apod' => isset($datos['CELULAR_APODERADO']) ? $datos['CELULAR_APODERADO'] : '',
                ':user'     => $username,
                ':pass'     => $pass
            ]);

            http_response_code(201);
            echo json_encode(["exito" => true, "mensaje" => "Alumno registrado correctamente.", "id" => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["exito" => false, "mensaje" => "Error al registrar: " . $e->getMessage()]);
        }
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["exito" => false, "mensaje" => "Se requiere el ID del alumno."]);
            break;
        }

        $sql = "UPDATE Alumnos SET DNI_ALUMNO=:dni, NOMBRES=:nom, APELLIDO=:ape, CORREO=:cor, CELULAR=:cel, ESTADO=:est 
                WHERE ID_ALUMNO=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':dni' => $datos['DNI_ALUMNO'],
            ':nom' => $datos['NOMBRES'],
            ':ape' => $datos['APELLIDO'],
            ':cor' => $datos['CORREO'],
            ':cel' => $datos['CELULAR'],
            ':est' => $datos['ESTADO'],
            ':id'  => $_GET['id']
        ]);

        echo json_encode(["exito" => true, "mensaje" => "Datos del alumno actualizados."]);
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $sql = "DELETE FROM Alumnos WHERE ID_ALUMNO = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["exito" => true, "mensaje" => "Alumno eliminado correctamente."]);
            } else {
                http_response_code(404);
                echo json_encode(["exito" => false, "mensaje" => "No se encontró el alumno."]);
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["exito" => false, "mensaje" => "Método no permitido."]);
}
?>
