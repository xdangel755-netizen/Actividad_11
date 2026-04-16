<?php
// api_inscripcion.php - Gestión de Inscripciones RESTful
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
require_once 'conexion.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$datos = json_decode(file_get_contents('php://input'), true);

try {
    switch ($metodo) {
        case 'GET':
            $sql = "SELECT i.ID_INSCRIPCION, a.NOMBRES, a.APELLIDO, c.NOMBRE_CURSO, i.FECHA_INSCRIPCION 
                    FROM Inscripcion_Curso i 
                    JOIN Alumnos a ON i.ID_ALUMNO = a.ID_ALUMNO 
                    JOIN Cursos c ON i.ID_CURSO = c.ID_CURSO 
                    ORDER BY i.ID_INSCRIPCION DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["exito" => true, "data" => $inscripciones]);
            break;

        case 'POST':
            if (empty($datos['ID_ALUMNO']) || empty($datos['ID_CURSO'])) {
                echo json_encode(["exito" => false, "mensaje" => "Faltan parámetros."]);
                break;
            }
            
            // Regla: Solo alumnos activos
            $stmt = $pdo->prepare("SELECT NOMBRES, ESTADO FROM alumnos WHERE ID_ALUMNO = ?");
            $stmt->execute([$datos['ID_ALUMNO']]);
            $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$alumno || $alumno['ESTADO'] !== 'Activo') {
                echo json_encode(["exito" => false, "mensaje" => "El alumno debe estar ACTIVO para inscribirse."]);
                break;
            }

            $sql = "INSERT INTO inscripcion_curso (ID_ALUMNO, ID_CURSO) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$datos['ID_ALUMNO'], $datos['ID_CURSO']]);
            echo json_encode(["exito" => true, "mensaje" => "Inscripción realizada con éxito."]);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                echo json_encode(["exito" => false, "mensaje" => "ID requerido."]);
                break;
            }
            $sql = "DELETE FROM inscripcion_curso WHERE ID_INSCRIPCION = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_GET['id']]);
            echo json_encode(["exito" => true, "mensaje" => "Inscripción eliminada."]);
            break;

        default:
            echo json_encode(["exito" => false, "mensaje" => "Método no soportado."]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["exito" => false, "mensaje" => $e->getMessage()]);
}
