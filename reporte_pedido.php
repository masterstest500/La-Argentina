<?php
session_start();
// 1. Control de acceso de seguridad
if (!isset($_SESSION['user']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    die("Acceso no autorizado.");
}

if (!isset($_GET['id'])) {
    die("Falta el identificador del pedido.");
}

require_once 'conexion.php';
require_once 'libs/fpdf/fpdf.php';

$id_pedido = mysqli_real_escape_string($conexion, $_GET['id']);

// 2. CONSULTA PRINCIPAL: Sincronizada con tu estructura de BD
$sql_pedido = "SELECT p.id, c.nombre_negocio AS cliente, p.vendedor AS preventista, p.total, p.fecha_pedido AS fecha 
               FROM pedidos p
               INNER JOIN clientes c ON p.cliente_id = c.id
               WHERE p.id = '$id_pedido'";
               
$res_pedido = mysqli_query($conexion, $sql_pedido);
$pedido = mysqli_fetch_assoc($res_pedido);

if (!$pedido) {
    die("El pedido solicitado no existe.");
}

// 3. Clase FPDF optimizada para reportes institucionales
class PDF extends FPDF {
    function Header() {
        // Encabezado Corporativo
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(180, 0, 0); // Rojo Prolacteca
        $this->Cell(100, 10, iconv('UTF-8', 'windows-1252', 'HELADOS LA ARGENTINA'), 0, 0, 'L');
        
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(90, 10, iconv('UTF-8', 'windows-1252', 'NOTA DE ENTREGA'), 0, 1, 'R');
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(100, 5, iconv('UTF-8', 'windows-1252', 'Sistema de Auditoría y Control Prolacteca'), 0, 0, 'L');
        $this->Cell(90, 5, iconv('UTF-8', 'windows-1252', 'Rif: J-12345678-9'), 0, 1, 'R');
        
        $this->SetDrawColor(180, 0, 0);
        $this->SetLineWidth(0.8);
        $this->Line(10, 27, 200, 27);
        $this->Ln(12);
    }

    function Footer() {
        // Pie de página con control de folios
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Documento generado de forma automatizada por el Sistema Prolacteca. Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// 4. Inicializar el documento PDF
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();

// 5. Bloque de Información del Pedido
$pdf->SetFillColor(245, 245, 245);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 7, iconv('UTF-8', 'windows-1252', ' DATOS DEL CLIENTE'), 0, 0, 'L', true);
$pdf->Cell(5, 7, '', 0, 0);
$pdf->Cell(90, 7, iconv('UTF-8', 'windows-1252', ' DETALLES DEL COMPROBANTE'), 0, 1, 'L', true);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 6, iconv('UTF-8', 'windows-1252', 'Cliente: ' . $pedido['cliente']), 0, 0, 'L');
$pdf->Cell(5, 6, '', 0, 0); 
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(90, 6, iconv('UTF-8', 'windows-1252', 'Nro. Pedido: #' . str_pad($pedido['id'], 5, "0", STR_PAD_LEFT)), 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 6, iconv('UTF-8', 'windows-1252', 'Preventista: ' . $pedido['preventista']), 0, 0, 'L');
$pdf->Cell(5, 6, '', 0, 0);
$pdf->Cell(90, 6, iconv('UTF-8', 'windows-1252', 'Fecha de Emisión: ' . date("d/m/Y h:i A", strtotime($pedido['fecha']))), 0, 1, 'L');
$pdf->Ln(8);

// 6. ENCABEZADO DE LA TABLA
$pdf->SetFillColor(180, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 10);

$pdf->Cell(25, 8, iconv('UTF-8', 'windows-1252', 'CANT.'), 1, 0, 'C', true);
$pdf->Cell(90, 8, iconv('UTF-8', 'windows-1252', 'PRODUCTO / SABOR'), 1, 0, 'L', true);
$pdf->Cell(35, 8, iconv('UTF-8', 'windows-1252', 'P. UNITARIO'), 1, 0, 'R', true);
$pdf->Cell(40, 8, iconv('UTF-8', 'windows-1252', 'SUBTOTAL'), 1, 1, 'R', true);

// 7. CUERPO DE LA TABLA
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);

$sql_detalle = "SELECT d.cantidad, p.sabor AS producto, d.precio_unitario, d.subtotal 
                FROM detalle_pedidos d
                INNER JOIN productos p ON d.producto_id = p.id
                WHERE d.pedido_id = '$id_pedido'";
$res_detalle = mysqli_query($conexion, $sql_detalle);

$item = 0;
while ($fila = mysqli_fetch_assoc($res_detalle)) {
    $fondo = ($item % 2 === 0) ? false : true;
    $pdf->SetFillColor(248, 248, 248);

    $pdf->Cell(25, 7, $fila['cantidad'], 1, 0, 'C', $fondo);
    $pdf->Cell(90, 7, iconv('UTF-8', 'windows-1252', ' ' . $fila['producto']), 1, 0, 'L', $fondo);
    $pdf->Cell(35, 7, '$' . number_format($fila['precio_unitario'], 2), 1, 0, 'R', $fondo);
    $pdf->Cell(40, 7, '$' . number_format($fila['subtotal'], 2), 1, 1, 'R', $fondo);
    $item++;
}

// 8. TOTAL NETO
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(115, 8, '', 0, 0);
$pdf->Cell(35, 8, 'TOTAL NETO:', 1, 0, 'R');
$pdf->SetTextColor(180, 0, 0);
$pdf->Cell(40, 8, '$' . number_format($pedido['total'], 2), 1, 1, 'R');
$pdf->Ln(15);

// ==========================================
// NUEVOS ELEMENTOS CORPORATIVOS E INSTITUCIONALES
// ==========================================

// 9. Cláusula de Control de Calidad y Cadena de Frío
$pdf->SetFont('Arial', 'I', 8.5);
$pdf->SetTextColor(120, 120, 120);
$nota_calidad = "Nota obligatoria de calidad: Mercancía alimentaria sujeta a cadena de frío estricta. Almacenar inmediatamente en cava de congelación a -18°C o menos. Por favor verifique sus renglones y cantidades al momento de la recepción física.";
$pdf->MultiCell(190, 4.5, iconv('UTF-8', 'windows-1252', $nota_calidad), 0, 'C');
$pdf->Ln(20);

// 10. Bloque Simétrico de Firmas de Logística
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);

// Fila para las líneas de firmas
$pdf->Cell(85, 4, '__________________________________', 0, 0, 'C');
$pdf->Cell(20, 4, '', 0, 0); // Espacio de separación central
$pdf->Cell(85, 4, '__________________________________', 0, 1, 'C');

// Fila para los textos aclaratorios
$pdf->SetFont('Arial', 'B', 9.5);
$pdf->Cell(85, 5, iconv('UTF-8', 'windows-1252', 'DESPACHADO POR (PREVENTISTA)'), 0, 0, 'C');
$pdf->Cell(20, 5, '', 0, 0);
$pdf->Cell(85, 5, iconv('UTF-8', 'windows-1252', 'RECIBIDO CONFORME (CLIENTE)'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(85, 5, iconv('UTF-8', 'windows-1252', $pedido['preventista']), 0, 0, 'C');
$pdf->Cell(20, 5, '', 0, 0);
$pdf->Cell(85, 5, iconv('UTF-8', 'windows-1252', 'Firma, Sello y C.I. / R.I.F.'), 0, 1, 'C');

// 11. Output definitivo
$pdf->Output('I', 'Pedido_' . str_pad($pedido['id'], 5, "0", STR_PAD_LEFT) . '.pdf');
?>