<?php
// test_api.php - Verificar que todas las APIs devuelven JSON correcto
header('Content-Type: text/plain');

$apis = [
    'crud-dashboard.php',
    'api_estudiante.php',
    'api_aula.php',
    'api_curso.php',
    'api_inscripcion.php',
    'api_pago.php',
    'api_usuario.php',
];

foreach ($apis as $api) {
    echo "=== $api ===\n";
    ob_start();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET = [];
    include "php/$api";
    $output = ob_get_clean();
    $decoded = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "OK - JSON valido\n";
        echo "Claves: " . implode(', ', array_keys($decoded)) . "\n";
        if (isset($decoded['exito'])) echo "exito: " . ($decoded['exito'] ? 'true' : 'false') . "\n";
        // Show data count
        foreach (['alumnos','aulas','cursos','data','usuarios','datos'] as $k) {
            if (isset($decoded[$k]) && is_array($decoded[$k])) {
                echo "[$k]: " . count($decoded[$k]) . " registros\n";
            }
        }
    } else {
        echo "ERROR - JSON invalido: " . json_last_error_msg() . "\n";
        echo "Output: " . substr($output, 0, 200) . "\n";
    }
    echo "\n";
}
