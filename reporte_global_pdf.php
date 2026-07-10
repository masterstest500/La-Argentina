<?php
session_start();
// 1. Control de acceso de seguridad
if (!isset($_SESSION['user']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    die("Acceso no autorizado.");
}

if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin'])) {
    die("Error: Rango de fechas no especificado.");
}

require_once 'conexion.php';
require_once 'libs/fpdf/fpdf.php';

$fecha_inicio = mysqli_real_escape_string($conexion, $_GET['fecha_inicio']);
$fecha_fin = mysqli_real_escape_string($conexion, $_GET['fecha_fin']);

$query_inicio = $fecha_inicio . " 00:00:00";
$query_fin    = $fecha_fin . " 23:59:59";

// 2. CONSULTA CORREGIDA: Filtramos para que SOLO traiga los pedidos válidos/completados
$sql_global = "SELECT p.id, p.fecha_pedido AS fecha, c.nombre_negocio AS cliente, p.vendedor AS preventista, p.total 
               FROM pedidos p
               INNER JOIN clientes c ON p.cliente_id = c.id
               WHERE p.fecha_pedido BETWEEN '$query_inicio' AND '$query_fin'
                 AND p.estado = 'Completado' 
               ORDER BY p.fecha_pedido ASC";

$res_global = mysqli_query($conexion, $sql_global);

// 3. Estructuración del PDF Ejecutivo
class PDF_Global extends FPDF {
    private $f_inicio;
    private $f_fin;

    function setFechas($ini, $fin) {
        $this->f_inicio = date("d/m/Y", strtotime($ini));
        $this->f_fin = date("d/m/Y", strtotime($fin));
    }

    function Header() {
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(180, 0, 0); 
        $this->Cell(110, 10, iconv('UTF-8', 'windows-1252', 'HELADOS LA ARGENTINA'), 0, 0, 'L');
        
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(80, 10, iconv('UTF-8', 'windows-1252', 'AUDITORÍA DE VENTAS'), 0, 1, 'R');
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(110, 5, iconv('UTF-8', 'windows-1252', 'Reporte Consolidado de Pedidos - Sistema Prolacteca'), 0, 0, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(80, 5, iconv('UTF-8', 'windows-1252', 'Periodo: ' . $this->f_inicio . ' al ' . $this->f_fin), 0, 1, 'R');
        
        $this->SetDrawColor(180, 0, 0);
        $this->SetLineWidth(0.8);
        $this->Line(10, 27, 200, 27);
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(140, 140, 140);
        $this->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Confidencialidad Prolacteca - Generado para Gerencia - Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF_Global('P', 'mm', 'A4');
$pdf->setFechas($fecha_inicio, $fecha_fin);
$pdf->AliasNbPages();
$pdf->AddPage();

// 5. ENCABEZADO DE LA TABLA
$pdf->SetFillColor(180, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9.5);

$pdf->Cell(22, 8, iconv('UTF-8', 'windows-1252', 'NRO. PED.'), 1, 0, 'C', true);
$pdf->Cell(33, 8, iconv('UTF-8', 'windows-1252', 'FECHA / HORA'), 1, 0, 'C', true);
$pdf->Cell(65, 8, iconv('UTF-8', 'windows-1252', 'CLIENTE / ESTABLECIMIENTO'), 1, 0, 'L', true);
$pdf->Cell(40, 8, iconv('UTF-8', 'windows-1252', 'PREVENTISTA'), 1, 0, 'L', true);
$pdf->Cell(30, 8, iconv('UTF-8', 'windows-1252', 'TOTAL NETO'), 1, 1, 'R', true);

// 6. FILAS DE LA TABLA
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 9);

$total_acumulado = 0;
$cantidad_pedidos = 0;

if (mysqli_num_rows($res_global) === 0) {
    $pdf->Cell(190, 12, iconv('UTF-8', 'windows-1252', 'No se encontraron registros de pedidos completados en este rango.'), 1, 1, 'C');
} else {
    $item = 0;
    while ($pedido = mysqli_fetch_assoc($res_global)) {
        $fondo = ($item % 2 === 0) ? false : true;
        $pdf->SetFillColor(248, 248, 248);

        $pdf->Cell(22, 7.5, '#' . str_pad($pedido['id'], 5, "0", STR_PAD_LEFT), 1, 0, 'C', $fondo);
        $pdf->Cell(33, 7.5, date("d/m/Y h:i A", strtotime($pedido['fecha'])), 1, 0, 'C', $fondo);
        $pdf->Cell(65, 7.5, iconv('UTF-8', 'windows-1252', ' ' . $pedido['cliente']), 1, 0, 'L', $fondo);
        $pdf->Cell(40, 7.5, iconv('UTF-8', 'windows-1252', ' ' . $pedido['preventista']), 1, 0, 'L', $fondo);
        $pdf->Cell(30, 7.5, '$' . number_format($pedido['total'], 2), 1, 1, 'R', $fondo);

        $total_acumulado += $pedido['total'];
        $cantidad_pedidos++;
        $item++;
    }
}
$pdf->Ln(6);

// 7. RESUMEN GLOBAL
$pdf->SetFillColor(245, 245, 245);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(190, 7, iconv('UTF-8', 'windows-1252', ' RESUMEN GLOBAL DE AUDITORÍA'), 0, 1, 'L', true);

$pdf->SetFont('Arial', '', 9.5);
$pdf->Cell(95, 7, iconv('UTF-8', 'windows-1252', 'Volumen de pedidos efectivos: ' . $cantidad_pedidos), 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(45, 7, 'TOTAL FACTURADO:', 0, 0, 'R');
$pdf->SetTextColor(180, 0, 0);
$pdf->Cell(50, 7, '$' . number_format($total_acumulado, 2), 0, 1, 'R');
$pdf->Ln(25);

// 8. BLOQUE DE FIRMAS ADMINISTRATIVAS (Para reportes institucionales)
$pdf->SetFont('Arial', '', 9.5);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(85, 4, '__________________________________', 0, 0, 'C');
$pdf->Cell(20, 4, '', 0, 0);
$pdf->Cell(85, 4, '__________________________________', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(85, 5, iconv('UTF-8', 'windows-1252', 'GENERADO POR (SISTEMA)'), 0, 0, 'C');
$pdf->Cell(20, 5, '', 0, 0);
$pdf->Cell(85, 5, iconv('UTF-8', 'windows-1252', 'REVISADO POR (ADMINISTRACIÓN)'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 8.5);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(85, 4, iconv('UTF-8', 'windows-1252', 'Firma Electrónica Automatizada'), 0, 0, 'C');
$pdf->Cell(20, 4, '', 0, 0);
$pdf->Cell(85, 4, iconv('UTF-8', 'windows-1252', 'Edson Álvarez - Firma y Sello'), 0, 1, 'C');

// 9. Output final
$pdf->Output('I', 'Reporte_Global_' . $fecha_inicio . '_al_' . $fecha_fin . '.pdf');
?>