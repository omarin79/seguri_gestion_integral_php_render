<?php
// Indica al cliente (la app) que la respuesta será en formato JSON.
header('Content-Type: application/json');

// Incluimos los archivos necesarios para la conexión a la BD y funciones.
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Creamos un array para la respuesta.
$response = ['status' => 'error', 'message' => 'Solicitud incorrecta.'];

// Solo procesamos si el método es POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtenemos el usuario y la contraseña enviados desde la app.
    $documento = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($documento) && !empty($password)) {
        try {
            // Buscamos al usuario por su documento.
            $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE DocumentoIdentidad = ?");
            $stmt->execute([$documento]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificamos si el usuario existe y si la contraseña es correcta.
            if ($user && password_verify($password, $user['ContrasenaHash'])) {
                // Si todo es correcto, preparamos una respuesta de éxito.
                $response['status'] = 'success';
                $response['message'] = '¡Bienvenido!';
                $response['userData'] = [
                    'id' => $user['ID_Usuario'],
                    'nombre' => $user['Nombre'],
                    'apellido' => $user['Apellido'],
                    'rolId' => $user['ID_Rol']
                ];
            } else {
                // Si las credenciales son incorrectas.
                $response['message'] = 'La cédula o la contraseña son incorrectas.';
            }
        } catch (PDOException $e) {
            // Si hay un error en la base de datos.
            $response['message'] = 'Error en el servidor. Inténtalo más tarde.';
        }
    } else {
        $response['message'] = 'Por favor, ingrese su cédula y contraseña.';
    }
}

// Convertimos el array de respuesta a formato JSON y lo enviamos.
echo json_encode($response);
?>