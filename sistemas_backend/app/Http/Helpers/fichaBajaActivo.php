<?php

namespace App\Http\Helpers;

use App\Http\Helpers\fpdf\FPDF;

class FichaBajaActivo extends FPDF
{
    private $id;
    private $nombre;
    private $descripcion;
    private $no_serie;
    private $marca;
    private $modelo;
    private $caracteristicas;
    private $agregados;
    private $fecha_compra;
    private $fecha_carga;
    private $proveedor;
    private $tipo;
    private $motivo_baja;
    private $imagen;
    private $date;
    public function __construct($activo,  $imagen, $motivo_baja)
    {
        $this->id = $activo->id;
        $this->nombre = $activo->nombre;
        $this->descripcion = $activo->descripcion;
        $this->no_serie = $activo->no_serie;
        $this->marca = $activo->marca;
        $this->modelo = $activo->modelo;
        $this->caracteristicas = $activo->caracteristicas;
        $this->agregados = $activo->agregados;
        $this->fecha_compra = $activo->fecha_compra;
        $this->fecha_carga = $activo->fecha_carga;
        $this->proveedor = $activo->proveedor;
        $this->tipo = $activo->tipo;
        $this->motivo_baja = $activo->motivo_baja ? $activo->motivo_baja : $motivo_baja;
        $this->imagen = $imagen;
        $this->date = date('d/m/Y');
        parent::__construct();
    }

    function Footer()
    {
        // Go to 1.5 cm from bottom
        $this->SetY(-15);
        // Select Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Print centered page number
        $this->Cell(0, 10,  mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo(), 0, 0, 'C');
    }

    public function ImprimirBaja()
    {
        $this->AliasNbPages();
        $this->AddPage();
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(190, 25, '', 1, 1, 'C');
        $this->SetXY(10, 10);
        $this->Image(__DIR__ . '/IMG/logocompletonegro.png', 9, 10, 70);
        $this->SetX(75);
        $this->Cell(75, 25,  mb_convert_encoding('Baja Definitiva de Activo', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
        $this->SetXY(150, 10);
        $this->Cell(25, 12, 'FOLIO', 1, 1, 'C');
        $this->SetXY(175, 10);
        $this->Cell(25, 12, 'Fecha', 1, 1, 'C');
        $this->SetXY(150, 22);
        $this->SetFont('Arial', '', 12);
        $this->Cell(25, 13, mb_convert_encoding($this->id, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $this->SetXY(175, 22);
        $this->Cell(25, 13, mb_convert_encoding($this->date, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $this->SetXY(175, 25);
        $this->Ln(8);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(90, 16, 'Datos del Activo', 0, 1, 'L');
        $this->Cell(190, 8, 'Nombre:', 1, 0, 'L');
        $this->SetX(30);
        $this->SetFont('Arial', '', 11);
        $this->Cell(60, 8, mb_convert_encoding($this->nombre, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(0);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(190, 8, 'No. serie:', 1, 0, 'L');
        $this->SetX(30);
        $this->SetFont('Arial', '', 11);
        $this->Cell(50, 8, mb_convert_encoding($this->no_serie, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(0);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(190, 8, 'Marca:', 1, 0, 'L');
        $this->SetX(30);
        $this->SetFont('Arial', '', 11);
        $this->Cell(50, 8, mb_convert_encoding($this->marca, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(0);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(190, 8, mb_convert_encoding('Modelo:', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $this->SetX(30);
        $this->SetFont('Arial', '', 11);
        $this->Cell(50, 8, mb_convert_encoding($this->modelo, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(0);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(190, 8, mb_convert_encoding('Descripción:', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $this->SetX(40);
        $this->SetFont('Arial', '', 11);
        $this->Cell(50, 8, mb_convert_encoding($this->descripcion, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(0);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(190, 8, mb_convert_encoding('Tipo de Activo:', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $this->SetX(42);
        $this->SetFont('Arial', '', 11);
        $this->Cell(50, 8, mb_convert_encoding($this->tipo, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 12);
        $this->Ln(4);
        $this->Cell(90, 6, mb_convert_encoding('Características', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->SetFont('Arial', '', 11);
        $this->MultiCell(190, 8, mb_convert_encoding($this->caracteristicas, 'ISO-8859-1', 'UTF-8'), 1,  'L', false);
        $this->Ln(10);
        $this->MultiCell(190, 8, mb_convert_encoding("Mediante este documento se hace constar la baja definitiva del Activo {$this->nombre}, con No. de serie {$this->no_serie}, que ya no se encuentra en condiciones adecuadas para su uso en las actividades a las que está destinado, y resulta inviable intentar su reparación. A continuación se detalla el motivo de su baja: " . $this->motivo_baja, 'ISO-8859-1', "UTF-8"), 0, 'J', false);
        $this->Ln(4);
        $this->Cell(90, 6, mb_convert_encoding('Evidencia de las condiciones del activo al momento de la baja:', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->SetFont('Arial', '', 11);
        $pdfWidth = $this->GetPageWidth();

        // Ancho deseado para la imagen (125px)
        $imageWidth = 72;

        // Coordenada X para centrar la imagen
        $x = ($pdfWidth - $imageWidth) / 2;

        // Coordenada Y, 5 unidades por debajo de la posición Y actual
        $y  = $this->GetY() + 5;
        $this->Image(public_path('evidencia_bajas/IMG_20240213_153407.jpg'), $x, $y, $imageWidth);
        $this->SetY(255);
        $y = $this->GetY();
        $half = $this->GetPageWidth() / 2;
        $this->Line($half - 45, $y - 2, $half + 45, $y - 2);
        $this->Cell(190, 10, 'Encargado de Seguridad e Higiene', 0, 0, 'C');
    }
}
