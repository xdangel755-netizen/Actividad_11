<?php
$host = 'localhost';
$db = 'MATRICULA';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    echo "Conexión exitosa a la base de datos '$db'.\n";
    
    $stmt = $p=$pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas encontradas: " . implode(", ", $tables) . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM Usuarios");
    echo "Número de usuarios registrados: " . $stmt->fetchColumn() . "\n";
    
} catch (PDOException $e) {
    echo "ERROR DE CONEXIÓN: " . $e->getMessage() . "\n";
}
