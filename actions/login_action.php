<?php
session_start();
// Log: Inicio del script
error_log("login_action.php: Script iniciado.");

require_once '../includes/db.php'; // Asegúrate que la ruta sea correcta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // Log: Datos recibidos
    error_log("login_action.php: Documento recibido: " . $documento);

    // Verifica campos vacíos
    if (empty($documento) || empty($password)) {
        error_log("login_action.php: Error - Campos vacíos.");
        header('Location: ../index.php?page=login&error=empty_fields');
        exit();
    }

    try {
        // Log: Intentando preparar la consulta
        // Usar "public"."Usuarios", pero columna WHERE en minúsculas
        $sql = "SELECT * FROM \"public\".\"Usuarios\" WHERE documentoidentidad = ?";
        error_log("login_action.php: Preparando SQL: " . $sql);

        // Preparar la consulta
        $stmt = $pdo->prepare($sql);

        // Log: Ejecutando consulta
        error_log("login_action.php: Ejecutando consulta para documento: " . $documento);
        $stmt->execute([$documento]);

        // Log: Consulta ejecutada, buscando usuario
        error_log("login_action.php: Consulta ejecutada. Fetching user...");
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica usuario y contraseña (usando nombres de columna con Mayúsculas)
        // *** ¡CAMBIOS CLAVE AQUÍ! ***
        if ($user && password_verify($password, $user['ContrasenaHash'])) {
            // Log: Usuario encontrado y contraseña válida
            error_log("login_action.php: Usuario encontrado y contraseña válida para ID: " . $user['ID_Usuario']);

            // Guardar en sesión (usando nombres de columna con Mayúsculas)
             // *** ¡CAMBIOS CLAVE AQUÍ! ***
            $_SESSION['user_id'] = $user['ID_Usuario'];
            $_SESSION['user_nombre'] = $user['Nombre'] . ' ' . $user['Apellido'];
            $_SESSION['user_doc'] = $user['DocumentoIdentidad'];
            $_SESSION['user_foto'] = $user['FotoPerfilRuta'];
            $_SESSION['user_rol_id'] = $user['ID_Rol'];

            // Log: Redirigiendo a inicio
            error_log("login_action.php: Redirigiendo a inicio.");
            header('Location: ../index.php?page=inicio');
            exit();
        } else {
            // Log: Credenciales inválidas o usuario no encontrado
            if (!$user) {
                error_log("login_action.php: Usuario no encontrado para documento: " . $documento);
            } else {
                error_log("login_action.php: Contraseña incorrecta para documento: " . $documento);
            }
            header('Location: ../index.php?page=login&error=invalid_credentials');
            exit();
        }
    } catch (PDOException $e) {
        // Error de base de datos
        // Log: PDOException capturada
        error_log("login_action.php: *** PDOException *** : " . $e->getMessage()); // Error detallado
        header('Location: ../index.php?page=login&error=db_error');
        exit();
    }
} else {
    // Acceso no POST
    // Log: Acceso no POST
    error_log("login_action.php: Acceso denegado (no es POST).");
    header('Location: ../index.php?page=login');
    exit();
}
?>