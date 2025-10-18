<?php
// actions/visita_action.php (Versión con subida de múltiples evidencias)

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

// --- Directorio para guardar las evidencias ---
$upload_dir = dirname(__DIR__) . '/uploads/checklist_evidence/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0775, true);
}

$pdo->beginTransaction();
try {
    // 1. Validar y recoger datos principales (como en la versión anterior)
    $id_supervisor = $_SESSION['user_id'];
    $id_cliente = $_POST['id_cliente'];
    $cedula_auditado = $_POST['cedula_auditado'];
    $id_checklist = $_POST['id_checklist'];

    // Lógica de búsqueda flexible (sin cambios)
    $stmt_auditado = $pdo->prepare("SELECT ID_Usuario, DocumentoIdentidad FROM Usuarios WHERE DocumentoIdentidad = ?");
    $stmt_auditado->execute([$cedula_auditado]);
    $usuario_auditado = $stmt_auditado->fetch(PDO::FETCH_ASSOC);
    $id_usuario_auditado = $usuario_auditado['ID_Usuario'] ?? null;
    $documento_auditado = $usuario_auditado['DocumentoIdentidad'] ?? $cedula_auditado;
    
    // 2. Insertar el registro principal de la visita
    $stmt_visita = $pdo->prepare(
        "INSERT INTO Visitas (ID_Usuario_Supervisor, ID_Usuario_Auditado, Documento_Auditado, ID_Cliente, ID_Checklist, Hallazgos, Recomendaciones, FechaVisita) 
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt_visita->execute([$id_supervisor, $id_usuario_auditado, $documento_auditado, $id_cliente, $id_checklist, $_POST['hallazgos_generales'], $_POST['recomendaciones']]);
    $id_visita = $pdo->lastInsertId();

    // 3. Procesar respuestas y EVIDENCIAS FOTOGRÁFICAS
    $respuestas = $_POST['respuestas'] ?? [];
    $stmt_respuesta = $pdo->prepare(
        "INSERT INTO RespuestasChecklist (ID_Visita, ID_Item, Respuesta, RutaEvidencia) VALUES (?, ?, ?, ?)"
    );

    foreach ($respuestas as $id_item => $respuesta) {
        $ruta_evidencia = null;

        // Verificar si se subió una foto para este ítem
        if (isset($_FILES['evidencias']['name'][$id_item]) && $_FILES['evidencias']['error'][$id_item] === UPLOAD_ERR_OK) {
            $file = [
                'name' => $_FILES['evidencias']['name'][$id_item],
                'tmp_name' => $_FILES['evidencias']['tmp_name'][$id_item],
                'size' => $_FILES['evidencias']['size'][$id_item],
            ];

            // Validar tamaño (máx 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception("El archivo para el ítem $id_item es demasiado grande.");
            }

            // Crear un nombre de archivo único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'visita_' . $id_visita . '_item_' . $id_item . '_' . time() . '.' . $extension;
            $destination = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $ruta_evidencia = 'uploads/checklist_evidence/' . $new_filename;
            } else {
                throw new Exception("Error al mover el archivo para el ítem $id_item.");
            }
        }

        // Guardar la respuesta con o sin la ruta de la evidencia
        $stmt_respuesta->execute([$id_visita, $id_item, $respuesta, $ruta_evidencia]);
    }

    $pdo->commit();
    header('Location: ../index.php?page=visitas&success=' . urlencode('Visita y evidencias guardadas exitosamente.'));
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: ../index.php?page=visitas&error=' . urlencode($e->getMessage()));
    exit();
}
?>