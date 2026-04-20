<?php
// php/api_dashboard.php - API central para obtención de KPIs y estadísticas de gráficos
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

try {
    $res = ["exito" => true, "data" => []];

    // 1. CONTEOS PARA KPIs
    // Total Alumnos
    $res['data']['total_alumnos'] = $pdo->query("SELECT COUNT(*) FROM alumnos")->fetchColumn();
    // Total Aulas
    $res['data']['total_aulas'] = $pdo->query("SELECT COUNT(*) FROM aula")->fetchColumn();
    // Total Cursos
    $res['data']['total_cursos'] = $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
    // Alumnos Activos
    $res['data']['alumnos_activos'] = $pdo->query("SELECT COUNT(*) FROM alumnos WHERE ESTADO = 'Activo'")->fetchColumn();

    // 2. DISTRIBUCIÓN POR GÉNERO
    $stmtG = $pdo->query("SELECT GENERO, COUNT(*) as total FROM alumnos GROUP BY GENERO");
    $generoLabels = [];
    $generoData = [];
    $generoColors = [];

    while ($row = $stmtG->fetch(PDO::FETCH_ASSOC)) {
        $generoLabels[] = ($row['GENERO'] == 'M' ? 'Masculino' : 'Femenino');
        $generoData[] = (int)$row['total'];
        $generoColors[] = ($row['GENERO'] == 'M' ? '#4e73df' : '#ff85a1'); // Azul y Rosado
    }
    $res['data']['genero_dist'] = ["labels" => $generoLabels, "data" => $generoData, "colors" => $generoColors];

    // 3. ESTADO DE MATRÍCULA
    $stmtE = $pdo->query("SELECT ESTADO, COUNT(*) as total FROM alumnos GROUP BY ESTADO");
    $estadoLabels = [];
    $estadoData = [];
    $estadoColors = [];

    $colorMap = [
        'Activo' => '#1cc88a',
        'Inactivo' => '#e74a3b',
        'En Proceso' => '#f6c23e',
        'Suspendido' => '#d10000'
    ];

    while ($row = $stmtE->fetch(PDO::FETCH_ASSOC)) {
        $estadoLabels[] = $row['ESTADO'];
        $estadoData[] = (int)$row['total'];
        $estadoColors[] = $colorMap[$row['ESTADO']] ?? '#2c3e50';
    }
    $res['data']['estado_dist'] = ["labels" => $estadoLabels, "data" => $estadoData, "colors" => $estadoColors];

    echo json_encode($res);

} catch (PDOException $e) {
    echo json_encode(["exito" => false, "mensaje" => "Error al obtener estadísticas: " . $e->getMessage()]);
}
?>
