<?php
require_once 'php/conexion.php';
header('Content-Type: application/json');
try {
    $stmt = $pdo->query("SELECT USERNAME FROM usuarios");
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($users);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
