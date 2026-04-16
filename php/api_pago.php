<?php
// api_pago.php - Gestión de Pagos/Reservas RESTful
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
require_once 'conexion.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$datos = json_decode(file_get_contents('php://input'), true);

try {
    switch ($metodo) {
        case 'GET':
            $sql = "SELECT r.ID_RESERVA, a.NOMBRES, a.APELLIDO, au.NIVEL, au.GRADO, au.SECCION, r.COD_PAGO, r.FECHA_RESERVA, r.ESTADO_PAGO 
                    FROM Reserva r 
                    JOIN Alumnos a ON r.ID_ALUMNO = a.ID_ALUMNO 
                    JOIN Aula au ON r.ID_AULA = au.ID_AULA 
                    ORDER BY r.ID_RESERVA DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["exito" => true, "data" => $pagos]);
            break;

        case 'POST':
            $sql = "INSERT INTO reserva (ID_ALUMNO, ID_AULA, COD_PAGO, ESTADO_PAGO) VALUES (?, ?, ?, 'PENDIENTE')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$datos['ID_ALUMNO'], $datos['ID_AULA'], $datos['COD_PAGO']]);
            echo json_encode(["exito" => true, "mensaje" => "Reserva creada."]);
            break;

        case 'PUT':
            if (!isset($_GET['id'])) break;
            $sql = "UPDATE reserva SET ESTADO_PAGO = 'PAGADO' WHERE ID_RESERVA = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_GET['id']]);
            echo json_encode(["exito" => true, "mensaje" => "Pago aprobado."]);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) break;
            $sql = "DELETE FROM reserva WHERE ID_RESERVA = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_GET['id']]);
            echo json_encode(["exito" => true, "mensaje" => "Registro eliminado."]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["exito" => false, "mensaje" => $e->getMessage()]);
}
