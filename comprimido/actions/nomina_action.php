<?php
// actions/nomina_action.php (Versión Segura con Certificado Corregido)

ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();
session_start();

require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/libs/GeneradorDesprendible.php';
require_once dirname(__DIR__) . '/libs/GeneradorCertificado.php';

if (!is_logged_in()) { die("Acceso no autorizado."); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Método no permitido."); }

define('ROLES_ADMIN_NOMINA', [1, 2, 3]);
$es_admin = in_array($_SESSION['user_rol_id'], ROLES_ADMIN_NOMINA);
$cedula_solicitada = $_POST['cedula'] ?? $_POST['cedula_empleado'] ?? null;
$cedula_sesion = $_SESSION['user_doc'] ?? null;
if (!$es_admin && $cedula_solicitada !== $cedula_sesion) {
    die("Acceso denegado. No tienes permiso para solicitar documentos de otros usuarios.");
}

$tipo_documento = $_POST['tipo_documento'] ?? '';
$cedula = $_POST['cedula_empleado'] ?? $_POST['cedula'] ?? '';

if (empty($cedula)) {
    die("Error: La cédula del empleado no puede estar vacía.");
}

try {
    // --- LÓGICA PARA GENERAR DESPRENDIBLE (SIN NINGÚN CAMBIO) ---
    if ($tipo_documento === 'desprendible') {
        $stmt_empleado = $pdo->prepare("SELECT u.ID_Usuario, u.Nombre, u.Apellido, u.DocumentoIdentidad, r.NombreRol AS Cargo FROM Usuarios u JOIN Roles r ON u.ID_Rol = r.ID_Rol WHERE u.DocumentoIdentidad = ?");
        $stmt_empleado->execute([$cedula]);
        $empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

        if (!$empleado) { die("Error: No se encontró al empleado con la cédula " . htmlspecialchars($cedula)); }

        $periodo = $_POST['periodo'] ?? '';
        if (empty($periodo)) { die("Error: Debes seleccionar un periodo."); }
        $fecha_periodo = $periodo . '-01';

        $stmt_pago = $pdo->prepare("SELECT * FROM PagosNomina WHERE ID_Usuario = ? AND Periodo = ?");
        $stmt_pago->execute([$empleado['ID_Usuario'], $fecha_periodo]);
        $pago = $stmt_pago->fetch(PDO::FETCH_ASSOC);

        if (!$pago) { die("No se encontraron registros de pago para el empleado en el periodo " . htmlspecialchars($periodo)); }

        $datosEmpleado = [
            'nombre' => $empleado['Nombre'] . ' ' . $empleado['Apellido'],
            'cedula' => $empleado['DocumentoIdentidad'],
            'cargo' => $empleado['Cargo'],
            'periodo_pago' => 'Periodo de ' . htmlspecialchars($periodo)
        ];
        $datosPago = [
            'devengados' => [['concepto' => 'Salario Básico', 'valor' => $pago['SalarioBase']], ['concepto' => 'Horas Extras', 'valor' => $pago['HorasExtras']], ['concepto' => 'Bonificaciones', 'valor' => $pago['Bonificaciones']]],
            'deducidos' => [['concepto' => 'Aporte Salud', 'valor' => $pago['AporteSalud']], ['concepto' => 'Aporte Pensión', 'valor' => $pago['AportePension']], ['concepto' => 'Otras Deducciones', 'valor' => $pago['OtrasDeducciones']]],
            'total_devengado' => $pago['TotalDevengado'], 'total_deducido' => $pago['TotalDeducido'], 'neto_a_pagar' => $pago['NetoAPagar'],
        ];

        $pdf = new GeneradorDesprendible();
        $pdf->crearDesprendible($datosEmpleado, $datosPago);
        
        ob_end_clean();
        $pdf->Output('D', 'Desprendible_' . $cedula . '_' . $periodo . '.pdf');
        exit();
    }
    
    // --- LÓGICA PARA GENERAR CERTIFICADO (SECCIÓN CORREGIDA) ---
    elseif ($tipo_documento === 'certificado_ingresos') {
        
        $stmt_empleado = $pdo->prepare("SELECT Nombre, Apellido, DocumentoIdentidad FROM Usuarios WHERE DocumentoIdentidad = ?");
        $stmt_empleado->execute([$cedula]);
        $empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

        if (!$empleado) { die("Error: No se encontró al empleado con la cédula " . htmlspecialchars($cedula)); }
        
        // Paquete 1: Datos del Retenedor (tu empresa)
        $datosRetenedor = [
            'nit' => '900.123.456-7',
            'razon_social' => 'SEGURI GESTION INTEGRAL S.A.S'
        ];
        
        // Paquete 2: Datos del Empleado
        $datosEmpleado = [
            'nombre_completo' => $empleado['Apellido'] . ' ' . $empleado['Nombre'],
            'documento' => $empleado['DocumentoIdentidad']
        ];
        
        // Paquete 3: Datos de Pagos (usamos ejemplo por ahora)
        $datosPagos = [
            'salarios' => 15600000, 'honorarios' => 0, 'servicios' => 0,
            'comisiones' => 500000, 'prestaciones' => 1300000, 'cesantias' => 1300000,
            'aportes_salud' => 748800, 'aportes_pension' => 748800, 'retencion' => 120000
        ];
        
        // Llamada a la función con los 3 paquetes de datos que tu archivo original espera
        $pdf = new GeneradorCertificado();
        $pdf->crearCertificado($datosRetenedor, $datosEmpleado, $datosPagos);
        
        ob_end_clean();
        $pdf->Output('D', 'Certificado_Ingresos_' . $cedula . '.pdf');
        exit();
    }

} catch (Exception $e) {
    die("Error al generar el documento: " . $e->getMessage());
}
?>