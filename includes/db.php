<?php
// Lee las variables de entorno de Render
$host = getenv('DB_HOST');        // Host de PostgreSQL
$db_name = getenv('DB_NAME');     // Nombre de la base de datos PostgreSQL
$username = getenv('DB_USER');    // Usuario de PostgreSQL
$password = getenv('DB_PASS');    // Contraseña de PostgreSQL
$port = getenv('DB_PORT') ?: 5432; // Puerto (5432 por defecto para PostgreSQL)

// DSN para PostgreSQL
$dsn = "pgsql:host={$host};port={$port};dbname={$db_name}";

try {
    // La conexión PDO usa el DSN de pgsql
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
       http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => "Error de base de datos: No se pudo conectar. Por favor, revisa la configuración.",
        'detail' => $e->getMessage() // Este mensaje te dará más pistas si sigue fallando
    ]);
    exit();
}
?>