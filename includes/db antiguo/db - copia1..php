<?php
// C:\xampp\htdocs\securigestion\includes\db.php

$host = 'localhost';
$dbname = 'bd_seguri_gestion_integral1'; 
$user = 'root'; 
$pass = ''; // La contraseña por defecto en XAMPP es vacía.

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si la conexión falla, este mensaje te dirá exactamente qué pasa.
    http_response_code(500); // Error interno del servidor
    echo json_encode([
        'status' => 'error',
        'message' => "Error de base de datos: No se pudo conectar. Verifica que el nombre de la BD ('$dbname') y las credenciales sean correctas.",
        'detail' => $e->getMessage() // Mensaje técnico del error
    ]);
    exit(); // Detiene la ejecución para no mostrar más errores.
}
?>