<?php
// Habilitar la visualización de todos los errores para el diagnóstico.
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Usamos @ para suprimir warnings si los archivos ya están incluidos, aunque require_once lo maneja bien.
@require_once dirname(__DIR__) . '/includes/db.php';
@require_once dirname(__DIR__) . '/includes/functions.php';

$response = ['status' => 'error', 'message' => 'Solicitud incorrecta.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($documento) && !empty($password)) {
        try {
            // El objeto $pdo viene del archivo db.php
            if (!isset($pdo)) {
                throw new Exception("El objeto de conexión PDO no está disponible.");
            }
            
            $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE DocumentoIdentidad = ?");
            $stmt->execute([$documento]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['ContrasenaHash'])) {
                $response['status'] = 'success';
                $response['message'] = '¡Bienvenido!';
                $response['userData'] = [
                    'id' => $user['ID_Usuario'],
                    'nombre' => $user['Nombre'],
                    'apellido' => $user['Apellido'],
                    'rolId' => $user['ID_Rol']
                ];
            } else {
                $response['message'] = 'La cédula o la contraseña son incorrectas.';
            }
        } catch (Exception $e) {
            // Captura cualquier tipo de error (de la BD o del código).
            http_response_code(500);
            $response['message'] = 'Error en el servidor.';
            $response['detail'] = $e->getMessage();
        }
    } else {
        $response['message'] = 'Por favor, ingrese su cédula y contraseña.';
    }
}

echo json_encode($response);
?>