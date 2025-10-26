<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($documento) || empty($password)) {
        header('Location: ../index.php?page=login&error=empty_fields');
        exit();
    }

    try {
        // Cambiado a minúsculas: "usuarios" y "documentoidentidad"
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE documentoidentidad = ?");
        $stmt->execute([$documento]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cambiado a minúsculas: "contrasenahash"
        if ($user && password_verify($password, $user['contrasenahash'])) {
            // Cambiado a minúsculas las claves de sesión si es necesario (mejor mantenerlas consistentes)
            // y los nombres de columnas al leer: 'id_usuario', 'nombre', 'apellido', etc.
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_nombre'] = $user['nombre'] . ' ' . $user['apellido'];
            $_SESSION['user_doc'] = $user['documentoidentidad'];
            $_SESSION['user_foto'] = $user['fotoperfilruta']; // Asegúrate que esta columna exista y esté en minúsculas
            $_SESSION['user_rol_id'] = $user['id_rol']; // Asegúrate que esta columna exista y esté en minúsculas

            header('Location: ../index.php?page=inicio');
            exit();
        } else {
            header('Location: ../index.php?page=login&error=invalid_credentials');
            exit();
        }
    } catch (PDOException $e) {
        // Podríamos añadir un log del error real para depurar mejor
         error_log("Login PDOException: " . $e->getMessage()); // Añade esto si quieres ver el error exacto en los logs de Render
        header('Location: ../index.php?page=login&error=db_error');
        exit();
    }
} else {
    header('Location: ../index.php?page=login');
    exit();
}
?>