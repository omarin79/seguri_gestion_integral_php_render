<?php
// Intenta leer las variables de entorno de Render de forma robusta
$host     = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST');
$db_name  = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? getenv('DB_NAME');
$username = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER'); // Importante para leer el usuario correcto
$password = $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? getenv('DB_PASS');
$port     = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: 5432; // Puerto 5432 por defecto para PostgreSQL

// Verifica si las variables esenciales se leyeron correctamente
if (!$host || !$db_name || !$username || !$password) {
    http_response_code(500);
    // Un mensaje más específico si faltan variables
    echo json_encode([
        'status' => 'error',
        'message' => "Error de configuración: Faltan una o más variables de entorno de base de datos (DB_HOST, DB_NAME, DB_USER, DB_PASS).",
        'detail' => 'Por favor, verifica la configuración de entorno en Render.'
    ]);
    exit();
}

// DSN para PostgreSQL con SSL requerido
$dsn = "pgsql:host={$host};port={$port};dbname={$db_name};sslmode=require";

try {
    // Opciones de PDO (opcional pero recomendado)
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve resultados como array asociativo
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa preparaciones nativas
    ];

    // Intenta conectar a la base de datos
    $pdo = new PDO($dsn, $username, $password, $options);

} catch (PDOException $e) {
    // Error al conectar
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => "Error de base de datos: No se pudo conectar. Por favor, revisa la configuración y las credenciales.",
        // Muestra el detalle del error de PDO, que ahora podría ser diferente
        'detail' => $e->getMessage()
    ]);
    exit();
}

// Si llegas aquí, la conexión fue exitosa.
// La variable $pdo está lista para ser usada en otros archivos que incluyan este.
?>