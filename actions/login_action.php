<?php
session_start();
// Asegúrate de que la ruta a db.php sea correcta desde la carpeta 'actions'
require_once '../includes/db.php';

// Verifica si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene los datos del formulario (usando el operador de fusión de null ?? para evitar errores si no existen)
    $documento = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Valida que los campos no estén vacíos
    if (empty($documento) || empty($password)) {
        // Redirige con error si faltan campos
        header('Location: ../index.php?page=login&error=empty_fields');
        exit();
    }

    try {
        // Prepara la consulta SQL usando nombres en minúsculas para tabla y columna
        // ¡CAMBIOS AQUÍ! -> 'usuarios', 'documentoidentidad'
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE documentoidentidad = ?");

        // Ejecuta la consulta pasando el documento como parámetro
        $stmt->execute([$documento]);

        // Obtiene el resultado como un array asociativo
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica si se encontró un usuario y si la contraseña es correcta
        // ¡CAMBIO AQUÍ! -> 'contrasenahash'
        if ($user && password_verify($password, $user['contrasenahash'])) {
            // Inicio de sesión exitoso: Guarda la información del usuario en la sesión
            // ¡CAMBIOS AQUÍ! -> Nombres de columnas en minúsculas al leer de $user
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_nombre'] = $user['nombre'] . ' ' . $user['apellido'];
            $_SESSION['user_doc'] = $user['documentoidentidad'];
            $_SESSION['user_foto'] = $user['fotoperfilruta']; // Asegúrate que esta columna exista
            $_SESSION['user_rol_id'] = $user['id_rol'];       // Asegúrate que esta columna exista

            // Redirige a la página de inicio
            header('Location: ../index.php?page=inicio');
            exit();
        } else {
            // Si no se encontró usuario o la contraseña no coincide
            header('Location: ../index.php?page=login&error=invalid_credentials');
            exit();
        }
    } catch (PDOException $e) {
        // Si ocurre un error durante la consulta a la base de datos
        // Registra el error real en los logs del servidor (útil para depurar)
        error_log("Login PDOException: " . $e->getMessage());

        // Redirige a la página de login con un error genérico de base de datos
        header('Location: ../index.