<?php
// Lee las variables de entorno de Render
$host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$username = getenv('DB_USER'); // <-- Asegúrate que Render tenga DB_USER = segurigestion
$password = getenv('DB_PASS'); // <-- Asegúrate que Render tenga la contraseña correcta
$port = getenv('DB_PORT') ?: 5432;

// DSN para PostgreSQL con SSL requerido
$dsn = "pgsql:host={$host};port={$port};dbname={$db_name};sslmode=require"; // <-- Cambio aquí

try {
    // La conexión PDO usa el DSN de pgsql
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
       http_response_code(500);
    // El error ahora podría ser diferente si falla la autenticación
    echo json_encode([
        'status' => 'error',
        'message' => "Error de base de datos: No se pudo conectar. Por favor, revisa la configuración.",
        'detail' => $e->getMessage()
    ]);
    exit();
}
?>