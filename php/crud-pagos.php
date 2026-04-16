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
        case '1': // CREAR RESERVA / PAGO
            if(empty($_POST['id_alumno']) || empty($_POST['id_aula']) || empty($_POST['cod_pago'])) {
                echo json_encode(["exito" => false, "mensaje" => "Faltan datos obligatorios."]);
                exit;
            }
            
            // Verificar si el cod_pago ya existe
            $check = $pdo->prepare("SELECT COUNT(*) FROM reserva WHERE COD_PAGO = ?");
            $check->execute([$_POST['cod_pago']]);
            if($check->fetchColumn() > 0) {
                echo json_encode(["exito" => false, "mensaje" => "Este código de pago ya está registrado."]);
                exit;
            }

            $sql = "INSERT INTO reserva (ID_ALUMNO, ID_AULA, COD_PAGO, ESTADO_PAGO) VALUES (?, ?, ?, 'PENDIENTE')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['id_alumno'], $_POST['id_aula'], $_POST['cod_pago']
            ]);
            echo json_encode(["exito" => true, "mensaje" => "Reserva guardada con éxito (Pendiente)."]);
            break;

        case '2': // APROBAR PAGO
            $sql = "UPDATE reserva SET ESTADO_PAGO = 'PAGADO' WHERE ID_RESERVA = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['id_reserva']]);
            echo json_encode(["exito" => true, "mensaje" => "El pago ha sido marcado como PAGADO."]);
            break;

        case '3': // ELIMINAR RESERVA
            $sql = "DELETE FROM reserva WHERE ID_RESERVA = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['id_reserva']]);
            echo json_encode(["exito" => true, "mensaje" => "Registro de reserva/pago eliminado."]);
            break;

        case '4': // LISTAR (JOIN) 
            $sql = "SELECT r.ID_RESERVA, a.NOMBRES, a.APELLIDO, au.NIVEL, au.GRADO, au.SECCION, r.COD_PAGO, r.FECHA_RESERVA, r.ESTADO_PAGO 
                    FROM reserva r 
                    JOIN alumnos a ON r.ID_ALUMNO = a.ID_ALUMNO 
                    JOIN aula au ON r.ID_AULA = au.ID_AULA 
                    ORDER BY r.ID_RESERVA DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($pagos);
            break;
            
        default:
            echo json_encode(["exito" => false, "mensaje" => "Opción no válida para Pagos."]);
    }

} catch (PDOException $e) {
    echo json_encode(["exito" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
}
?>
