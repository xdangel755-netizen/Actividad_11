<?php
require_once 'php/conexion.php';
try {
    $r1 = $pdo->query("SELECT COUNT(*) FROM Alumnos")->fetchColumn();
    $r2 = $pdo->query("SELECT COUNT(*) FROM Aula")->fetchColumn();
    $r3 = $pdo->query("SELECT COUNT(*) FROM Cursos")->fetchColumn();
    $r4 = $pdo->query("SELECT COUNT(*) FROM Usuarios")->fetchColumn();
    echo "Conexion OK\n";
    echo "Alumnos: $r1\n";
    echo "Aulas: $r2\n";
    echo "Cursos: $r3\n";
    echo "Usuarios: $r4\n";
    echo "\n--- crud-dashboard.php test ---\n";
    $data = [];
    $data['total_alumnos'] = $pdo->query("SELECT COUNT(*) FROM Alumnos")->fetchColumn();
    $data['generos'] = $pdo->query("SELECT GENERO, COUNT(*) as CANTIDAD FROM Alumnos GROUP BY GENERO")->fetchAll(PDO::FETCH_ASSOC);
    $data['estados'] = $pdo->query("SELECT ESTADO, COUNT(*) as CANTIDAD FROM Alumnos GROUP BY ESTADO")->fetchAll(PDO::FETCH_ASSOC);
    echo "Generos: " . print_r($data['generos'], true) . "\n";
    echo "Estados: " . print_r($data['estados'], true) . "\n";
    $u = $pdo->query("SELECT USERNAME FROM Usuarios LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    echo "Usuarios (primeros 3): " . print_r($u, true) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
