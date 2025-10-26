<?php
session_start();
require_once '../includes/db.php'; // Asegúrate que la ruta sea correcta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Verifica campos vacíos
    if (empty($documento) || empty($password)) {
        header('Location: ../index.php?page=login&error=empty_fields');
        exit();
    }

    try {
        // *** ¡LA LÍNEA CORRECTA ES ESTA! ***
        // Usar "Usuarios" con comillas dobles y U mayúscula
        $stmt = $pdo->prepare("SELECT * FROM \"Usuarios\" WHERE documentoidentidad = ?");
        $stmt->execute([$documento]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica usuario y contraseña (claves leídas en minúsculas)
        if ($user && password_verify($password, $user['contrasenahash'])) {
            // Guardar en sesión (claves leídas en minúsculas)
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_nombre'] = $user['nombre'] . ' ' . $user['apellido'];
            $_SESSION['user_doc'] = $user['documentoidentidad'];
            $_SESSION['user_foto'] = $user['fotoperfilruta'];
            $_SESSION['user_rol_id'] = $user['id_rol'];

            header('Location: ../index.php?page=inicio');
            exit();
        } else {
            // Credenciales inválidas
            header('Location: ../index.php?page=login&error=invalid_credentials');
            exit();
        }
    } catch (PDOException $e) {
        // Error de base de datos
        error_log("Login PDOException: " . $e->getMessage());
        header('Location: ../index.php?page=login&error=db_error');
        exit();
    }
} else {
    // Acceso no POST
    header('Location: ../index.php?page=login');
    exit();
}
?>