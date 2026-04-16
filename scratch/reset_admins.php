<?php
// reset_admins.php - Restaurar los 3 administradores con la contraseña original
require_once dirname(__DIR__) . '/php/conexion.php';

try {
    $pass = 'admin123';
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    
    // Limpiar tabla de usuarios (opcional, pero asegura que estén los 3 limpios)
    // O simplemente usar INSERT IGNORE / REPLACE
    $pdo->exec("DELETE FROM Usuarios WHERE USERNAME IN ('ADMIN', 'ARIANA', 'ANGEL')");
    
    $admins = [
        ['ADMIN', $hash],
        ['ARIANA', $hash],
        ['ANGEL', $hash]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO Usuarios (USERNAME, PASSWORD_HASH, ESTADO) VALUES (?, ?, 1)");
    
    foreach ($admins as $admin) {
        $stmt->execute($admin);
        echo "Usuario '{$admin[0]}' restaurado con éxito (Pass: $pass).\n";
    }
    
    echo "\n>>> Proceso completado. Ya puedes iniciar sesión.\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
