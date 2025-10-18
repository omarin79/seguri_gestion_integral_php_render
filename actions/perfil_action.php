<?php
// C:\xampp\htdocs\securigestion\actions\perfil_action.php (Versión con Debug Mejorado)

session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'upload_photo') {
    $user_id = $_SESSION['user_id'];

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto_perfil'];

        // Validación de tamaño (hasta 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            header('Location: ../index.php?page=mi-perfil&error=' . urlencode('Error: El archivo no debe superar los 5MB.'));
            exit();
        }

        // Validación de tipo de archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            header('Location: ../index.php?page=mi-perfil&error=' . urlencode('Error: Tipo de archivo no permitido (solo JPG, PNG, GIF).'));
            exit();
        }

        // Crear nombre de archivo único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
        $upload_path = '../uploads/' . $new_filename;

        // Mover el archivo
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $db_path = 'uploads/' . $new_filename;
            $stmt = $pdo->prepare("UPDATE Usuarios SET FotoPerfilRuta = ? WHERE ID_Usuario = ?");
            $stmt->execute([$db_path, $user_id]);
            $_SESSION['user_foto'] = $db_path;

            header('Location: ../index.php?page=mi-perfil&success=' . urlencode('¡Foto de perfil actualizada con éxito!'));
            exit();
        } else {
            header('Location: ../index.php?page=mi-perfil&error=' . urlencode('Error del servidor: No se pudo guardar la imagen.'));
            exit();
        }
    } else {
        header('Location: ../index.php?page=mi-perfil&error=' . urlencode('Error: No se seleccionó un archivo o hubo un problema con la subida.'));
        exit();
    }
}
?>