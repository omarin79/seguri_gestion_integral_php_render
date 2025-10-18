<?php
// C:\xampp\htdocs\securigestion\actions\update_perfil_action.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Recoger datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$telefono = trim($_POST['telefono'] ?? null);
$direccion = trim($_POST['direccion'] ?? null);
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// 2. Construir la consulta SQL dinámicamente
$fields = [
    'Nombre' => $nombre,
    'Apellido' => $apellido,
    'Telefono' => $telefono,
    'Direccion' => $direccion,
    'CorreoElectronico' => $email
];
$query_parts = [];
$params = [];

foreach ($fields as $key => $value) {
    $query_parts[] = "$key = ?";
    $params[] = $value;
}

// 3. Manejar el cambio de contraseña (solo si se proporcionó una nueva)
if (!empty($password)) {
    if ($password !== $password_confirm) {
        header('Location: ../index.php?page=editar-perfil&status=error&message=' . urlencode('Las nuevas contraseñas no coinciden.'));
        exit();
    }
    $query_parts[] = "ContrasenaHash = ?";
    $params[] = password_hash($password, PASSWORD_BCRYPT);
}

// 4. Ejecutar la actualización en la base de datos
if (count($query_parts) > 0) {
    $sql = "UPDATE Usuarios SET " . implode(', ', $query_parts) . " WHERE ID_Usuario = ?";
    $params[] = $user_id;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Actualizar el nombre en la sesión si cambió
        $_SESSION['user_name'] = $nombre . ' ' . $apellido;

        header('Location: ../index.php?page=mi-perfil&status=success&message=' . urlencode('¡Perfil actualizado exitosamente!'));
        exit();

    } catch (PDOException $e) {
        header('Location: ../index.php?page=editar-perfil&status=error&message=' . urlencode('Error al actualizar el perfil: ' . $e->getMessage()));
        exit();
    }
} else {
    // No se envió ningún dato para actualizar
    header('Location: ../index.php?page=editar-perfil');
    exit();
}
?>