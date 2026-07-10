<?php
// 1. Incluimos la librería FPDF desde la carpeta libs
require_once 'libs/fpdf/fpdf.php';

// 2. Creamos una nueva instancia de la clase FPDF
// P = Retrato (Portrait), mm = milímetros, A4 = Tamaño del papel
$pdf = new FPDF('P', 'mm', 'A4');

// 3. Añadimos una página en blanco al documento
$pdf->AddPage();

// 4. Definimos el tipo de fuente (Arial), estilo (B = Negrita/Bold) y tamaño (16 puntos)
$pdf->SetFont('Arial', 'B', 16);

// 5. Creamos una celda de texto (Ancho, Alto, Texto, Borde, Siguiente Línea, Alineación)
// Nota: Usamos iconv para que PHP 8.2 procese bien los acentos en FPDF sin lanzar advertencias
$texto = iconv('UTF-8', 'windows-1252', '¡Hola Mundo desde Helados La Argentina!');
$pdf->Cell(0, 10, $texto, 0, 1, 'C');

// 6. Espaciado e instrucciones secundarias
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(5); // Salto de línea de 5mm
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Módulo de Reportes - Sistema Prolacteca listo.'), 0, 1, 'C');

// 7. Enviamos el PDF directamente al navegador
$pdf->Output();
?>