<?php
// C:\xampp\htdocs\securigestion\actions\recuperar_action.php (Código Corregido)

require_once dirname(__DIR__) . '/includes/db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require dirname(__DIR__) . '/libs/PHPMailer/Exception.php';
require dirname(__DIR__) . '/libs/PHPMailer/PHPMailer.php';
require dirname(__DIR__) . '/libs/PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email_recuperar'] ?? '';

    $stmt = $pdo->prepare("SELECT ID_Usuario FROM Usuarios WHERE CorreoElectronico = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires_at = new DateTime('+1 hour');
        $expires_at_str = $expires_at->format('Y-m-d H:i:s');

        $stmt_update = $pdo->prepare("UPDATE Usuarios SET reset_token = ?, reset_token_expires_at = ? WHERE ID_Usuario = ?");
        $stmt_update->execute([$token, $expires_at_str, $user['ID_Usuario']]);

        // --- ▼▼ LÍNEA CORREGIDA ▼▼ ---
        // Aseguramos que la URL apunte a 'page=reset-password'
        $reset_link = "http://localhost/securigestion/Seguri_gestion_integral_PHP/index.php?page=reset-password&token=" . $token;
        
        $mail = new PHPMailer(true);
        try {
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'segurigestionintegral@gmail.com'; // Tus credenciales
            $mail->Password = 'kkmg gumj puzy grek'; // Tu contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Contenido del correo
            $mail->setFrom('no-reply@securigestion.com', 'SecuriGestión Integral');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Instrucciones para Restablecer tu Contraseña';
            $mail->Body = "Hola,<br><br>Para restablecer tu contraseña, por favor haz clic en el siguiente enlace:<br><a href='{$reset_link}'>Restablecer Contraseña</a><br><br>Si no solicitaste esto, ignora este mensaje.";
            
            $mail->send();
        } catch (Exception $e) {
            // Manejo de errores
        }
    }
    
    header('Location: ../index.php?page=login&success=' . urlencode('Si tu correo está registrado, hemos enviado las instrucciones de recuperación.'));
    exit();
}
?>