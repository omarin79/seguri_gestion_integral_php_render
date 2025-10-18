<?php
// C:\xampp\htdocs\securigestion\actions\reset_password_action.php

require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_new_password'] ?? '';

    if ($new_password !== $confirm_password) {
        header('Location: ../index.php?page=reset-password&token=' . $token . '&error=passwords_do_not_match');
        exit();
    }

    try {
        // 1. Buscar el usuario por el token y verificar que no haya expirado
        $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE reset_token = ? AND reset_token_expires_at > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 2. Si el token es válido, actualizar la contraseña
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // 3. Limpiar el token para que no se pueda volver a usar
            $stmt_update = $pdo->prepare("UPDATE Usuarios SET ContrasenaHash = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE ID_Usuario = ?");
            $stmt_update->execute([$hashed_password, $user['ID_Usuario']]);

            header('Location: ../index.php?page=login&success=Su contraseña ha sido actualizada exitosamente.');
            exit();
        } else {
            // Si el token es inválido o ha expirado
            header('Location: ../index.php?page=login&error=invalid_or_expired_token');
            exit();
        }

    } catch (PDOException $e) {
        header('Location: ../index.php?page=login&error=db_error');
        exit();
    }
}
?>