<?php
// actions/preoperacional_action.php (Versión con nuevos campos)

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

$pdo->beginTransaction();
try {
    // 1. Recoger datos del formulario
    $id_usuario = $_SESSION['user_id'];
    $tipo_vehiculo = $_POST['tipo_vehiculo'];
    $placa = $_POST['placa'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $cilindraje = $_POST['cilindraje'];
    $observaciones = $_POST['observaciones'] ?? '';
    $items_chequeados_json = json_encode($_POST['items_' . strtolower($tipo_vehiculo)] ?? []);

    // 2. Procesar la FOTO del vehículo
    $ruta_foto = null;
    if (isset($_FILES['foto_vehiculo']) && $_FILES['foto_vehiculo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir_fotos = dirname(__DIR__) . '/uploads/preoperacional/';
        if (!is_dir($upload_dir_fotos)) mkdir($upload_dir_fotos, 0777, true);
        
        $file = $_FILES['foto_vehiculo'];
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = "vehiculo_{$id_usuario}_" . time() . "." . $extension;
        $destination = $upload_dir_fotos . $new_filename;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $ruta_foto = 'uploads/preoperacional/' . $new_filename;
        }
    }

    // 3. Procesar la FIRMA digital
    $ruta_firma = null;
    if (!empty($_POST['firma_base64'])) {
        $upload_dir_firmas = dirname(__DIR__) . '/uploads/firmas/';
        if (!is_dir($upload_dir_firmas)) mkdir($upload_dir_firmas, 0777, true);

        $data_uri = $_POST['firma_base64'];
        $encoded_image = explode(",", $data_uri)[1];
        $decoded_image = base64_decode($encoded_image);
        
        $firma_filename = "firma_{$id_usuario}_" . time() . ".png";
        file_put_contents($upload_dir_firmas . $firma_filename, $decoded_image);
        $ruta_firma = 'uploads/firmas/' . $firma_filename;
    } else {
        throw new Exception("La firma es obligatoria.");
    }

    // 4. Guardar todo en la base de datos
    $stmt = $pdo->prepare(
        "INSERT INTO preoperacional (ID_Usuario, TipoVehiculo, Placa, Marca, Modelo, Cilindraje, ItemsChequeados, Observaciones, RutaFotoVehiculo, RutaFirma) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$id_usuario, $tipo_vehiculo, $placa, $marca, $modelo, $cilindraje, $items_chequeados_json, $observaciones, $ruta_foto, $ruta_firma]);

    $pdo->commit();
    header('Location: ../index.php?page=preoperacional-vehiculos&success=' . urlencode('Registro pre-operacional guardado exitosamente.'));
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: ../index.php?page=preoperacional-vehiculos&error=' . urlencode('Error al guardar: ' . $e->getMessage()));
    exit();
}
?>