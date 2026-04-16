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

    $id_alumno = $_POST['id_alumno'] ?? null;
    $id_curso = $_POST['id_curso'] ?? null;
    $opcion = $_POST['opcion'] ?? '1';

    switch ($opcion) {
        case '1': // INSCRIBIR
            if (!$id_alumno || !$id_curso) {
                echo json_encode(["exito" => false, "mensaje" => "Faltan parámetros (id_alumno o id_curso)."]);
                exit;
            }

            // 1. Verificar el estado del alumno
            $stmt = $pdo->prepare("SELECT NOMBRES, ESTADO FROM alumnos WHERE ID_ALUMNO = ?");
            $stmt->execute([$id_alumno]);
            $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$alumno) {
                echo json_encode(["exito" => false, "mensaje" => "Alumno no encontrado."]);
                exit;
            }

            // REGLA DE NEGOCIO: Solo inscripciones si el estado es 'Activo'
            if ($alumno['ESTADO'] !== 'Activo') {
                echo json_encode([
                    "exito" => false, 
                    "mensaje" => "ERROR: El alumno '{$alumno['NOMBRES']}' no se puede inscribir porque su estado actual es '{$alumno['ESTADO']}'. Solo se permiten inscripciones para alumnos con estado 'Activo'."
                ]);
                exit;
            }

            // 2. Verificar que no esté inscrito ya
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM inscripcion_curso WHERE ID_ALUMNO = ? AND ID_CURSO = ?");
            $stmtCheck->execute([$id_alumno, $id_curso]);
            if ($stmtCheck->fetchColumn() > 0) {
                echo json_encode(["exito" => false, "mensaje" => "El alumno ya está inscrito en este curso."]);
                exit;
            }

            // 3. Proceder con la inscripción
            $sql = "INSERT INTO inscripcion_curso (ID_ALUMNO, ID_CURSO) VALUES (?, ?)";
            $stmtInsert = $pdo->prepare($sql);
            $stmtInsert->execute([$id_alumno, $id_curso]);

            echo json_encode([
                "exito" => true, 
                "mensaje" => "¡Éxito! El alumno '{$alumno['NOMBRES']}' ha sido inscrito correctamente en el curso."
            ]);
            break;

        case '4': // LISTAR INSCRIPCIONES
            $sql = "SELECT i.ID_INSCRIPCION, a.NOMBRES, a.APELLIDO, c.NOMBRE_CURSO, i.FECHA_INSCRIPCION 
                    FROM inscripcion_curso i 
                    JOIN alumnos a ON i.ID_ALUMNO = a.ID_ALUMNO 
                    JOIN cursos c ON i.ID_CURSO = c.ID_CURSO 
                    ORDER BY i.ID_INSCRIPCION DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($inscripciones);
            break;

        case '5': // ELIMINAR INSCRIPCIÓN
            $id = $_POST['id_inscripcion'] ?? null;
            $sql = "DELETE FROM inscripcion_curso WHERE ID_INSCRIPCION = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            echo json_encode(["exito" => true, "mensaje" => "Inscripción eliminada."]);
            break;

        default:
            echo json_encode(["exito" => false, "mensaje" => "Opción no válida."]);
            break;
    }

} catch (PDOException $e) {
    echo json_encode(["exito" => false, "mensaje" => "Error de Base de Datos: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["exito" => false, "mensaje" => "Error general: " . $e->getMessage()]);
}
?>
