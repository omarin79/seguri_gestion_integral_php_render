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
        $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE DocumentoIdentidad = ?");
        $stmt->execute([$documento]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['ContrasenaHash'])) {
            $_SESSION['user_id'] = $user['ID_Usuario'];
            $_SESSION['user_nombre'] = $user['Nombre'] . ' ' . $user['Apellido'];
            $_SESSION['user_doc'] = $user['DocumentoIdentidad'];
            // LÍNEA CLAVE: Guarda la ruta de la foto en la sesión
            $_SESSION['user_foto'] = $user['FotoPerfilRuta'];
            
            // --- LÍNEA AÑADIDA ---
            // Se guarda el ID del rol para filtrar los checklists
            $_SESSION['user_rol_id'] = $user['ID_Rol'];

            header('Location: ../index.php?page=inicio');
            exit();
        } else {
            header('Location: ../index.php?page=login&error=invalid_credentials');
            exit();
        }
    } catch (PDOException $e) {
        header('Location: ../index.php?page=login&error=db_error');
        exit();
    }
}
?>