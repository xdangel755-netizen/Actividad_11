<?php
$url = "http://localhost/Actividad_9/PROYECTOWEB-JS/php/api_aula.php";

echo "--- EVIDENCE 1: GET (All Rooms) ---\n";
echo file_get_contents($url) . "\n\n";

echo "--- EVIDENCE 2: POST (Create Test Room) ---\n";
$context = stream_context_create([
    "http" => [
        "method" => "POST",
        "header" => "Content-Type: application/json",
        "content" => json_encode(["NIVEL" => "TEST", "GRADO" => "9", "SECCION" => "Z", "VACANTES_TOTALES" => 50])
    ]
]);
$res = file_get_contents($url, false, $context);
echo $res . "\n\n";
$json = json_decode($res, true);
$id = $json["id"];

echo "--- EVIDENCE 3: PUT (Update Test Room ID $id) ---\n";
$context = stream_context_create([
    "http" => [
        "method" => "PUT",
        "header" => "Content-Type: application/json",
        "content" => json_encode(["NIVEL" => "TEST-UPDATED", "GRADO" => "9", "SECCION" => "Z", "VACANTES_TOTALES" => 55, "VACANTES_DISPONIBLES" => 55])
    ]
]);
echo file_get_contents($url . "?id=" . $id, false, $context) . "\n\n";

echo "--- EVIDENCE 4: DELETE (Delete Test Room ID $id) ---\n";
$context = stream_context_create([
    "http" => [
        "method" => "DELETE"
    ]
]);
echo file_get_contents($url . "?id=" . $id, false, $context) . "\n\n";
