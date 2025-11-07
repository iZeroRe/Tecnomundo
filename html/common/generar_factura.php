<?php
session_start();
// 1. Incluir la biblioteca FPDF y la conexión
require '../lib/fpdf.php';
require '../config/conexion.php';

// 2. ----- VERIFICACIÓN DE SEGURIDAD -----
// (Exactamente la misma que en tus otros dashboards)
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Acceso denegado. Por favor, inicie sesión.");
}

// -----------------------------------------

// 3. Obtener y validar el ID de la reparación
$id_reparacion = $_GET['id'] ?? 0;
if (!filter_var($id_reparacion, FILTER_VALIDATE_INT) || $id_reparacion <= 0) {
    die("ID de reparación no válido.");
}

// 4. ----- OBTENER DATOS DE LA BASE DE DATOS -----

// Query 1: Obtener datos principales de la reparación, cliente y empresa
$stmt_main = $pdo->prepare("
    SELECT
        r.id_reparacion, r.fecha_ingreso, r.fecha_terminado, r.costo,
        c.nombre AS c_nombre, c.apellido AS c_apellido, c.direccion AS c_dir, c.num_direccion AS c_num, c.correo AS c_correo,
        e.marca, e.modelo,
        emp.nombre_empresa, emp.direccion AS emp_dir, emp.telefono AS emp_tel, emp.correo AS emp_correo
    FROM reparacion AS r
    JOIN equipo AS e ON r.id_equipo = e.id_equipo
    JOIN cliente AS c ON e.id_cliente = c.id_cliente
    JOIN trabajador AS t ON r.id_trabajador = t.id_trabajador
    JOIN empresa AS emp ON t.id_empresa = emp.id_empresa
    WHERE r.id_reparacion = ?
");
$stmt_main->execute([$id_reparacion]);
$data = $stmt_main->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("No se encontró la reparación.");
}

// Query 2: Obtener los detalles (productos/servicios) de la reparación
$stmt_details = $pdo->prepare("
    SELECT
        p.nombre,
        dr.cantidad,
        p.precio AS precio_unitario,
        dr.subtotal
    FROM detalle_reparacion AS dr
    JOIN producto AS p ON dr.id_producto = p.id_producto
    WHERE dr.id_reparacion = ?
");
$stmt_details->execute([$id_reparacion]);
$detalles = $stmt_details->fetchAll(PDO::FETCH_ASSOC);


// 5. ----- GENERAR EL PDF -----

class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo (opcional)
        // $this->Image('logo.png',10,6,30);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80); // Mover a la derecha
        $this->Cell(30, 10, 'Factura de Reparacion', 0, 0, 'C');
        $this->Ln(20); // Salto de línea
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15); // Posición a 1.5 cm del final
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Crear instancia de PDF
$pdf = new PDF();
$pdf->AliasNbPages(); // Para el número de página total
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// --- Sección de la Empresa (Nuestra Info) ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, utf8_decode($data['nombre_empresa']), 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, utf8_decode($data['emp_dir']), 0, 1, 'L');
$pdf->Cell(0, 5, 'Telefono: ' . $data['emp_tel'], 0, 1, 'L');
$pdf->Cell(0, 5, 'Email: ' . $data['emp_correo'], 0, 1, 'L');
$pdf->Ln(10); // Espacio

// --- Sección del Cliente ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Facturar a:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, utf8_decode($data['c_nombre'] . ' ' . $data['c_apellido']), 0, 1, 'L');
$pdf->Cell(0, 5, utf8_decode($data['c_dir'] . ' ' . $data['c_num']), 0, 1, 'L');
$pdf->Cell(0, 5, 'Email: ' . $data['c_correo'], 0, 1, 'L');
$pdf->Ln(10); // Espacio

// --- Detalles de la Factura (Folio y Fechas) ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 7, 'Folio Reparacion:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 7, $data['id_reparacion'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 7, 'Fecha Ingreso:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 7, $data['fecha_ingreso'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 7, 'Fecha Terminado:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 7, $data['fecha_terminado'], 0, 1);
$pdf->Ln(15); // Espacio

// --- Tabla de Detalles/Productos ---
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(230, 230, 230); // Color de fondo gris claro
$pdf->Cell(95, 10, 'Descripcion', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Cant.', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'P. Unitario', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Subtotal', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
foreach ($detalles as $detalle) {
    // Usamos utf8_decode para manejar acentos y caracteres especiales
    $pdf->Cell(95, 8, utf8_decode($detalle['nombre']), 1, 0, 'L');
    $pdf->Cell(20, 8, $detalle['cantidad'], 1, 0, 'C');
    $pdf->Cell(35, 8, '$' . number_format($detalle['precio_unitario'], 2), 1, 0, 'R');
    $pdf->Cell(40, 8, '$' . number_format($detalle['subtotal'], 2), 1, 1, 'R');
}
$pdf->Ln(10); // Espacio

// --- Total ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(115, 10, '', 0, 0); // Espacio en blanco
$pdf->Cell(35, 10, 'TOTAL:', 1, 0, 'C');
$pdf->Cell(40, 10, '$' . number_format($data['costo'], 2), 1, 1, 'R');


// 6. ----- ENVIAR EL PDF AL NAVEGADOR -----
// 'I' envía el archivo al navegador (inline)
// 'D' fuerza la descarga
// 'F' lo guarda en el servidor
$pdf->Output('I', 'Factura_Reparacion_' . $data['id_reparacion'] . '.pdf');
?>