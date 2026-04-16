<?php
// api_curso.php - Gestión de Cursos
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
            $sql = 'SELECT c.*, a.NIVEL, a.GRADO, a.SECCION 
                    FROM Cursos c 
                    JOIN Aula a ON c.ID_AULA = a.ID_AULA 
                    WHERE c.ID_CURSO = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            $curso = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($curso) {
                echo json_encode(["exito" => true, "curso" => $curso]);
            } else {
                http_response_code(404);
                echo json_encode(["exito" => false, "mensaje" => "Curso no encontrado."]);
            }
        } else {
            $sql = 'SELECT c.*, a.NIVEL, a.GRADO, a.SECCION 
                    FROM Cursos c 
                    JOIN Aula a ON c.ID_AULA = a.ID_AULA
                    ORDER BY c.ID_CURSO DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["exito" => true, "data" => $cursos]);
        }
        break;

    case 'POST':
        if (!empty($datos['NOMBRE_CURSO']) && !empty($datos['ID_AULA'])) {
            $sql = "INSERT INTO Cursos (NOMBRE_CURSO, ID_AULA, DOCENTE, HORAS_SEMANALES, CREDITOS) 
                    VALUES (:nom, :aula, :doc, :hrs, :cre)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom'  => $datos['NOMBRE_CURSO'],
                ':aula' => $datos['ID_AULA'],
                ':doc'  => isset($datos['DOCENTE']) ? $datos['DOCENTE'] : 'Por asignar',
                ':hrs'  => isset($datos['HORAS_SEMANALES']) ? $datos['HORAS_SEMANALES'] : 0,
                ':cre'  => isset($datos['CREDITOS']) ? $datos['CREDITOS'] : 0
            ]);

            http_response_code(201);
            echo json_encode(["exito" => true, "mensaje" => "Curso registrado correctamente.", "id" => $pdo->lastInsertId()]);
        } else {
            http_response_code(400);
            echo json_encode(["exito" => false, "mensaje" => "Datos incompletos (Nombre y Aula son obligatorios)."]);
        }
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["exito" => false, "mensaje" => "Se requiere el ID del curso."]);
            break;
        }

        $sql = "UPDATE Cursos SET NOMBRE_CURSO=:nom, ID_AULA=:aula, DOCENTE=:doc, HORAS_SEMANALES=:hrs, CREDITOS=:cre 
                WHERE ID_CURSO=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom'  => $datos['NOMBRE_CURSO'],
            ':aula' => $datos['ID_AULA'],
            ':doc'  => $datos['DOCENTE'],
            ':hrs'  => $datos['HORAS_SEMANALES'],
            ':cre'  => $datos['CREDITOS'],
            ':id'   => $_GET['id']
        ]);

        echo json_encode(["exito" => true, "mensaje" => "Curso actualizado correctamente."]);
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $sql = "DELETE FROM Cursos WHERE ID_CURSO = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["exito" => true, "mensaje" => "Curso eliminado correctamente."]);
            } else {
                http_response_code(404);
                echo json_encode(["exito" => false, "mensaje" => "No se encontró el curso."]);
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["exito" => false, "mensaje" => "Método no permitido."]);
}
?>
