<?php
// Lee las variables de entorno de Render
$host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$dsn = "mysql:host={$host};dbname={$db_name};charset=utf8";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
       http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => "Error de base de datos: No se pudo conectar. Por favor, revisa la configuración.",
        'detail' => $e->getMessage()
    ]);
    exit();
}
?>