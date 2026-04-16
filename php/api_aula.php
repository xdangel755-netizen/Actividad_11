<?php
// Paso 1: VAMOS A CREAR LAS CABECERAS HTTP ESTRICTAS PARA UNA API RESTFUL
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

// Paso 3: Capturamos el metodo HTTP y los datos de entrada
$metodoHttp = $_SERVER['REQUEST_METHOD'];
$datos = json_decode(file_get_contents('php://input'), true);

// Paso 4: Creamos las peticiones con switch
switch ($metodoHttp) {
    case 'GET':
        if (isset($_GET['id'])) {
            $sql = 'SELECT * FROM Aula WHERE ID_AULA = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->execute();
            $aula = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($aula) {
                echo json_encode(["exito" => true, "aula" => $aula]);
            } else {
                http_response_code(404);
                echo json_encode(["exito" => false, "mensaje" => "Aula no encontrada."]);
            }
        } else {
            $sql = 'SELECT * FROM Aula ORDER BY ID_AULA DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["exito" => true, "data" => $aulas]);
        }
        break;

    case 'POST':
        if (!empty($datos['NIVEL']) && !empty($datos['GRADO']) && !empty($datos['SECCION'])) {
            $sql = "INSERT INTO Aula (NIVEL, GRADO, SECCION, VACANTES_TOTALES, VACANTES_DISPONIBLES) 
                    VALUES (:niv, :gra, :sec, :vt, :vd)";
            $stmt = $pdo->prepare($sql);
            
            // Si no envían vacantes, ponemos 30 por defecto
            $vacantes = isset($datos['VACANTES_TOTALES']) ? $datos['VACANTES_TOTALES'] : 30;
            
            $stmt->execute([
                ':niv' => $datos['NIVEL'],
                ':gra' => $datos['GRADO'],
                ':sec' => $datos['SECCION'],
                ':vt'  => $vacantes,
                ':vd'  => $vacantes // Al inicio disponibles = totales
            ]);

            http_response_code(201);
            echo json_encode(["exito" => true, "mensaje" => "Aula creada correctamente.", "id" => $pdo->lastInsertId()]);
        } else {
            http_response_code(400);
            echo json_encode(["exito" => false, "mensaje" => "Datos incompletos (Nivel, Grado, Sección son obligatorios)."]);
        }
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["exito" => false, "mensaje" => "Se requiere el ID del aula para actualizar."]);
            break;
        }

        if (!empty($datos)) {
            $sql = "UPDATE Aula SET NIVEL=:niv, GRADO=:gra, SECCION=:sec, VACANTES_TOTALES=:vt, VACANTES_DISPONIBLES=:vd 
                    WHERE ID_AULA=:id";
            $stmt = $pdo->prepare($sql);
            $res = $stmt->execute([
                ':niv' => $datos['NIVEL'],
                ':gra' => $datos['GRADO'],
                ':sec' => $datos['SECCION'],
                ':vt'  => $datos['VACANTES_TOTALES'],
                ':vd'  => $datos['VACANTES_DISPONIBLES'],
                ':id'  => $_GET['id']
            ]);

            echo json_encode(["exito" => true, "mensaje" => "Aula actualizada correctamente."]);
        } else {
            http_response_code(400);
            echo json_encode(["exito" => false, "mensaje" => "No se enviaron datos para actualizar."]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $sql = "DELETE FROM Aula WHERE ID_AULA = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["exito" => true, "mensaje" => "Aula eliminada correctamente."]);
            } else {
                http_response_code(404);
                echo json_encode(["exito" => false, "mensaje" => "No se encontró el aula con ese ID."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["exito" => false, "mensaje" => "ID no proporcionado."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["exito" => false, "mensaje" => "Método no permitido."]);
        break;
}