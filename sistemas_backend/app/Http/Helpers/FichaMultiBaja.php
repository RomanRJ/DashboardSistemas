<?php

namespace App\Http\Helpers;

use App\Http\Helpers\fpdf\FPDF;

class FichaMultiBaja extends FPDF
{
    private $activos = [];
    private $column_widths = [11, 70, 22, 45, 40];
    private $labels = ["ID", "Nombre", "Marca", "No. de Serie", "imagen"];
    private $date;
    public function __construct($activos,  $imagenes)
    {
        for ($i = 0; $i < count($activos); $i++) {
            $id = $activos[$i]->id;
            $nombre = $activos[$i]->nombre;
            $no_serie = $activos[$i]->no_serie;
            $marca = $activos[$i]->marca;
            $imagen = $imagenes[$i];

            array_push($this->activos, [$id, $nombre, $marca, $no_serie,  $imagen]);
        }
        $this->date = date('d/m/Y');
        parent::__construct();
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $currentPage = $this->PageNo();
        $pageNumberText = mb_convert_encoding("Página {$currentPage} de {nb}", 'ISO-8859-1', 'UTF-8');
        $this->Cell(0, 10, $pageNumberText, 0, 0, 'C');
    }

    public function ImprimirBajas($id_baja)
    {
        $this->AliasNbPages();
        $this->AddPage();
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(190, 25, '', 1, 1, 'C');
        $this->SetXY(10, 10);
        $this->Image(__DIR__ . '/IMG/logocompletonegro.png', 9, 10, 70);
        $this->SetX(75);
        $this->Cell(75, 25,  mb_convert_encoding('Baja Múltiple de Activos', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
        $this->SetXY(150, 10);
        $this->Cell(25, 12, 'FOLIO', 1, 1, 'C');
        $this->SetXY(175, 10);
        $this->Cell(25, 12, 'Fecha', 1, 1, 'C');
        $this->SetXY(150, 22);
        $this->SetFont('Arial', '', 12);
        $this->Cell(25, 13, mb_convert_encoding($id_baja, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $this->SetXY(175, 22);
        $this->Cell(25, 13, mb_convert_encoding($this->date, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $this->SetXY(175, 25);
        $this->Ln(20);
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(190, 8, mb_convert_encoding("Mediante este documento se hace constar la baja definitiva de los activos listados a continuación; que ya no se encuentran en condiciones adecuadas para su uso en las actividades a las que están destinados, y resulta inviable intentar su reparación.", 'ISO-8859-1', "UTF-8"), 0, 'J', false);
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 11);
        foreach ($this->labels as $label) {
            $this->Cell($this->column_widths[key($this->labels)], 8, mb_convert_encoding($label, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
            next($this->labels);
        }
        $this->Ln();
        $this->setFont('Arial', '', 8);
        foreach ($this->activos as $activo) {
            $salto = $this->GetY();
            if ($salto > 240) {
                $this->AddPage();
            }
            $x = $this->GetX();
            $y = $this->GetY();
            for ($i = 0; $i < 5; $i++) {
                $this->SetXY($x, $y);
                if ($i < 4) {
                    $this->Cell($this->column_widths[$i], 26, mb_convert_encoding($activo[$i], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
                } else {
                    $imagePath = $activo[4];
                    // Obtener la posición central de la celda
                    $cellX = $x + ($this->column_widths[$i] / 2) - 12.5; // Centrar la imagen (25/2 = 12.5)
                    $cellY = $y;
                    // Cargar la imagen centrada
                    $this->Image($imagePath, $cellX, $cellY, 25, 25);
                }
                $this->SetXY($x, $y); // Establece la posición X e Y
                $this->Cell($this->column_widths[$i], 26, '', 1, 0);
                $x += $this->column_widths[$i];
            }
            $this->Ln(26);
        }
        $this->SetY(255);
        $y = $this->GetY();
        $half = $this->GetPageWidth() / 2;
        $this->Line($half - 45, $y - 2, $half + 45, $y - 2);
        $this->Cell(190, 10, 'Encargado de Seguridad e Higiene', 0, 0, 'C');
    }
}
