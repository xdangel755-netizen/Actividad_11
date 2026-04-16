<?php
// crud-dashboard.php - Resumen Estadístico
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

try {
    $data = [];


    // 1. KPIs Generales
    $data['total_alumnos'] = $pdo->query("SELECT COUNT(*) FROM Alumnos")->fetchColumn();
    $data['total_aulas'] = $pdo->query("SELECT COUNT(*) FROM Aula")->fetchColumn();
    $data['total_cursos'] = $pdo->query("SELECT COUNT(*) FROM Cursos")->fetchColumn();
    $data['alumnos_activos'] = $pdo->query("SELECT COUNT(*) FROM Alumnos WHERE ESTADO = 'Activo'")->fetchColumn();

    // 2. Gráfico: Distribución por Género
    $stmtG = $pdo->query("SELECT GENERO, COUNT(*) as CANTIDAD FROM Alumnos GROUP BY GENERO");
    $data['generos'] = $stmtG->fetchAll(PDO::FETCH_ASSOC);

    // 3. Gráfico: Estado de Matrículas
    $stmtE = $pdo->query("SELECT ESTADO, COUNT(*) as CANTIDAD FROM Alumnos GROUP BY ESTADO");
    $data['estados'] = $stmtE->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["exito" => true, "datos" => $data]);

} catch (PDOException $e) {
    echo json_encode(["exito" => false, "mensaje" => $e->getMessage()]);
}
?>
