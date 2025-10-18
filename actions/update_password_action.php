<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // 1. Validar que las contraseñas coincidan
    if ($new_password !== $confirm_new_password) {
        header('Location: ../index.php?page=reset_password&token=' . $token . '&error=' . urlencode('Las contraseñas no coinciden.'));
        exit();
    }

    // 2. Verificar el token en la base de datos
    $stmt = $pdo->prepare("SELECT ID_Usuario, reset_token_expires_at FROM Usuarios WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: ../index.php?page=login&error=' . urlencode('Token inválido.'));
        exit();
    }

    // 3. Verificar si el token ha expirado
    $now = new DateTime();
    $expires_at = new DateTime($user['reset_token_expires_at']);

    if ($now > $expires_at) {
        header('Location: ../index.php?page=login&error=' . urlencode('El token ha expirado.'));
        exit();
    }

    // 4. Actualizar la contraseña y limpiar el token
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE Usuarios SET ContrasenaHash = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE ID_Usuario = ?");
    $stmt->execute([$hashed_password, $user['ID_Usuario']]);

    header('Location: ../index.php?page=login&success=' . urlencode('Tu contraseña ha sido actualizada exitosamente.'));
    exit();
}
?>