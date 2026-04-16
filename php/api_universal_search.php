<?php
// api_universal_search.php - Búsqueda en múltiples tablas
header('Content-Type: application/json; charset=utf-8');

require_once 'conexion.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(["exito" => true, "resultados" => []]);
    exit;
}

$resultados = [];
$searchTerm = "%$query%";

// 1. BUSCAR EN ESTUDIANTES
$sqlAlumnos = "SELECT ID_ALUMNO, NOMBRES, APELLIDO, DNI_ALUMNO 
               FROM Alumnos 
               WHERE NOMBRES LIKE :q OR APELLIDO LIKE :q OR DNI_ALUMNO LIKE :q 
               LIMIT 5";
$stmt = $pdo->prepare($sqlAlumnos);
$stmt->execute([':q' => $searchTerm]);
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($alumnos as $a) {
    $resultados[] = [
        "tipo" => "estudiante",
        "titulo" => $a['NOMBRES'] . ' ' . $a['APELLIDO'],
        "subtitulo" => "DNI: " . $a['DNI_ALUMNO'],
        "id" => $a['ID_ALUMNO'],
        "icon" => "fa-user-graduate"
    ];
}

// 2. BUSCAR EN AULAS
$sqlAulas = "SELECT ID_AULA, NIVEL, GRADO, SECCION 
             FROM Aula 
             WHERE NIVEL LIKE :q OR GRADO LIKE :q OR SECCION LIKE :q 
             LIMIT 5";
$stmt = $pdo->prepare($sqlAulas);
$stmt->execute([':q' => $searchTerm]);
$aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($aulas as $au) {
    $resultados[] = [
        "tipo" => "aula",
        "titulo" => $au['NIVEL'] . ' ' . $au['GRADO'] . ' "' . $au['SECCION'] . '"',
        "subtitulo" => "Grado y Sección",
        "id" => $au['ID_AULA'],
        "icon" => "fa-chalkboard"
    ];
}

// 3. BUSCAR EN CURSOS
$sqlCursos = "SELECT ID_CURSO, NOMBRE_CURSO, DOCENTE 
              FROM Cursos 
              WHERE NOMBRE_CURSO LIKE :q OR DOCENTE LIKE :q 
              LIMIT 5";
$stmt = $pdo->prepare($sqlCursos);
$stmt->execute([':q' => $searchTerm]);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($cursos as $c) {
    $resultados[] = [
        "tipo" => "curso",
        "titulo" => $c['NOMBRE_CURSO'],
        "subtitulo" => "Docente: " . $c['DOCENTE'],
        "id" => $c['ID_CURSO'],
        "icon" => "fa-book"
    ];
}

echo json_encode(["exito" => true, "resultados" => $resultados]);
