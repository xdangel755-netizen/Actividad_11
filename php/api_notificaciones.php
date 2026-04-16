<?php
// api_notificaciones.php - Sistema de Notificaciones Dinámicas
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

try {
    $notificaciones = [];

    // 1. NUEVOS ALUMNOS (Top 2)
    $stmt = $pdo->prepare("SELECT NOMBRES, APELLIDO FROM alumnos ORDER BY ID_ALUMNO DESC LIMIT 2");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notificaciones[] = [
            "texto" => "Nuevo alumno: " . $row['NOMBRES'] . ' ' . $row['APELLIDO'],
            "icon" => "fa-user-graduate",
            "color" => "#6441a5"
        ];
    }

    // 2. NUEVOS CURSOS (Top 2)
    $stmt = $pdo->prepare("SELECT NOMBRE_CURSO FROM cursos ORDER BY ID_CURSO DESC LIMIT 2");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notificaciones[] = [
            "texto" => "Nuevo curso: " . $row['NOMBRE_CURSO'],
            "icon" => "fa-book",
            "color" => "#f6c23e"
        ];
    }

    // 3. PAGOS PENDIENTES (Top 2 Reservas)
    $stmt = $pdo->prepare("SELECT COD_PAGO FROM reserva WHERE ESTADO_PAGO = 'PENDIENTE' ORDER BY ID_RESERVA DESC LIMIT 2");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notificaciones[] = [
            "texto" => "Pago pendiente: #" . $row['COD_PAGO'],
            "icon" => "fa-money-bill-wave",
            "color" => "#e74a3b"
        ];
    }

    // 4. NUEVAS AULAS (Top 2)
    $stmt = $pdo->prepare("SELECT NIVEL, GRADO, SECCION FROM aula ORDER BY ID_AULA DESC LIMIT 2");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notificaciones[] = [
            "texto" => "Nueva aula: " . $row['NIVEL'] . ' ' . $row['GRADO'] . ' ' . $row['SECCION'],
            "icon" => "fa-chalkboard",
            "color" => "#4e73df"
        ];
    }

    // 5. NUEVAS INSCRIPCIONES (Top 2)
    $stmt = $pdo->prepare("SELECT a.NOMBRES, c.NOMBRE_CURSO 
                           FROM inscripcion_curso i
                           JOIN alumnos a ON i.ID_ALUMNO = a.ID_ALUMNO
                           JOIN cursos c ON i.ID_CURSO = c.ID_CURSO
                           ORDER BY i.ID_INSCRIPCION DESC LIMIT 2");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notificaciones[] = [
            "texto" => $row['NOMBRES'] . " se inscribió en " . $row['NOMBRE_CURSO'],
            "icon" => "fa-file-signature",
            "color" => "#1cc88a"
        ];
    }

    echo json_encode([
        "exito" => true,
        "notificaciones" => $notificaciones,
        "total" => count($notificaciones)
    ]);

} catch (PDOException $e) {
    echo json_encode(["exito" => false, "mensaje" => $e->getMessage()]);
}
