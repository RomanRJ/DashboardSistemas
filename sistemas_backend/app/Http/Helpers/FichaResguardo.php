<?php

namespace App\Http\Helpers;

use App\Http\Helpers\fpdf\FPDF;

class FichaResguardo extends FPDF
{
    private $resguardo;
    private $articulos;
    private $condiciones; //solo en devolucion
    private $fecha;
    private $column_widths = array(32, 27, 27, 27, 27, 50);
    private $column_headers = array("Descripción", "Nombre", "No. Serie", "Marca", "Modelo", "Características");
    function __construct($resguardo, $articulos, $condiciones = null)
    {
        date_default_timezone_set('America/Mexico_City');
        header('Content-Type: application/pdf; charset=utf-8');
        $this->resguardo = $resguardo;
        $this->articulos = $articulos;
        $this->condiciones = $condiciones;
        $this->fecha = date('d/m/Y');
        $this->AliasNbPages('totalPages');
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

    private function calculateCellHeight($item)
    {
        $max = null;
        $index = 0;
        for ($i = 0; $i < count($item); $i++) {
        }
    }

    private function generateReturnConditions($condiciones_por_item)
    {
        $condiciones = "";
        foreach ($condiciones_por_item as $nombre => $condiciones_dev) {
            $condiciones .= $nombre . ": " . $condiciones_dev . " ";
        }
        return $condiciones;
    }

    //REVISAR QUE SIGA FUNCIONANDO!!

    public function NuevaFicha($devolucion = false, $reimpresion = false)
    {
        $this->AliasNbPages();
        $this->AddPage();
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(190, 25, '', 1, 1, 'C');
        $this->SetXY(10, 10);
        $this->Image(__DIR__ . '/IMG/logocompletonegro.png', 9, 10, 70);
        $this->SetX(75);
        if (!$devolucion) {
            if (!$reimpresion) {
                $this->Cell(75, 25, 'Resguardo de Activo Fijo', 1, 1, 'C');
            } else {
                $this->Cell(75, 25,  mb_convert_encoding('Reimpresión de Resguardo', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
            }
        } else {
            $this->Cell(75, 25,  mb_convert_encoding('Devolución de Resguardo', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
        }
        $this->SetXY(150, 10);
        $this->Cell(25, 12, 'FOLIO', 1, 1, 'C');
        $this->SetXY(175, 10);
        $this->Cell(25, 12, 'Fecha', 1, 1, 'C');
        $this->SetXY(150, 22);
        $this->SetFont('Arial', '', 12);
        $this->Cell(25, 13, mb_convert_encoding($this->resguardo->id, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $this->SetXY(175, 22);
        if ($reimpresion) {
            if ($devolucion) {
                $this->Cell(25, 13, mb_convert_encoding(date('d/m/Y', strtotime($this->articulos[0]->fecha_devolucion)), 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
            } else {
                $this->Cell(25, 13, mb_convert_encoding($this->fecha, 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
            }
        } else {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $this->resguardo->fecha_entrega)) {
                $this->Cell(25, 13, mb_convert_encoding($this->resguardo->fecha_entrega, 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
            } else {
                $this->Cell(25, 13, mb_convert_encoding(date('d/m/Y', strtotime($this->resguardo->fecha_entrega)), 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
            }
        }
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(90, 15, 'Datos del Usuario', 0, 1, 'L');
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(190, 8, 'Nombre:', 1, 0, 'L');
        $this->SetX(30);
        $this->SetFont('Arial', '', 11);
        $this->Cell(60, 8, mb_convert_encoding($this->resguardo->usuario, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(0);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(190, 8, 'Nomina:', 1, 0, 'L');
        $this->SetX(30);
        $this->SetFont('Arial', '', 11);
        $this->Cell(50, 8, mb_convert_encoding($this->resguardo->nomina, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(0);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(190, 8, 'Departamento:', 1, 0, 'L');
        $this->SetX(40);
        $this->SetFont('Arial', '', 11);
        $this->Cell(50, 8, mb_convert_encoding($this->resguardo->departamento, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(0);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(190, 8, mb_convert_encoding('Motivo de préstamo:', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $this->SetX(50);
        $this->SetFont('Arial', '', 11);
        $this->Cell(50, 8, mb_convert_encoding($this->resguardo->motivo, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(0);
        if (!$devolucion) {
            $this->SetFont('Arial', 'B', 11);
            $this->Cell(190, 8, mb_convert_encoding('Tiempo que se le será prestado el bien:', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
            $this->SetX(85);
            $this->SetFont('Arial', '', 11);
            $this->Cell(50, 8, mb_convert_encoding($this->resguardo->tiempo, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        }
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 11);

        foreach ($this->column_headers as $header) {
            $this->Cell($this->column_widths[key($this->column_headers)], 8, mb_convert_encoding($header, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
            next($this->column_headers);
        }

        $this->ln();
        $this->setFont('Arial', '', 11);

        foreach ($this->articulos as $articulo) {
            $salto = $this->GetY();
            if ($salto > 180) {
                $this->AddPage();
            }
            // Inicializa la posición X y Y para la fila actual
            $x = $this->GetX();
            $y = $this->GetY();

            // Dibuja una fila de datos
            $articulo_array = [
                $articulo->descripcion,
                $articulo->nombre,
                $articulo->no_serie,
                $articulo->marca,
                $articulo->modelo,
                $articulo->caracteristicas
            ];
            for ($i = 0; $i < count($articulo_array); $i++) {
                $this->SetXY($x, $y);
                $this->MultiCell($this->column_widths[$i], 6, mb_convert_encoding($articulo_array[$i], 'ISO-8859-1', 'UTF-8'), 0, 'C', false);
                $this->SetXY($x, $y); // Establece la posición X e Y
                $this->Cell($this->column_widths[$i], 35, '', 1, 0);
                $x += $this->column_widths[$i]; // Avanza la posición X para la siguiente celda
            }
            $this->Ln(35);
        }

        $salto = $this->GetY();
        if ($salto > 180) {
            $this->AddPage();
        }
        $this->Ln(10);
        $this->SetFont('Arial', '', 11);
        $this->Cell(190, 8, 'Especificar las condiciones en que se entrega el bien y en su caso los accesorios que esta tenga:', 0, 1, 'L');
        $this->SetFillColor(255, 255, 255);
        if (!$devolucion) {
            $this->MultiCell(190, 8, mb_convert_encoding($this->resguardo->condiciones, 'ISO-8859-1', 'UTF-8'), 1, 'L', 1);
        } else {
            $this->MultiCell(190, 8, mb_convert_encoding($this->generateReturnConditions($this->condiciones), 'ISO-8859-1', 'UTF-8'), 1, 'L', 1);
        }
        $this->Ln(5);
        $this->SetX(10);
        if (!$devolucion) {
            $this->MultiCell(150, 6, mb_convert_encoding('Al usuario que le es entregado(s) el(los) bien(es) en calidad de préstamo, es estrictamente responsable del uso y custodia de estos, aceptando y siguiendo las políticas y procedimientos de uso de equipo (anexo).', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
        }
        $this->Ln(5);
        $this->SetY(230);
        if (!$devolucion) {
            $this->Cell(95, 35, '', 1, 0, 'C');
            $this->Cell(95, 35, '', 1, 0, 'C');
        } else {
            $this->Cell(190, 35, '', 1, 0, 'C');
        }
        $this->SetY(255);
        $y = $this->GetY();
        if (!$devolucion) {
            $this->Line(20, $y - 2, 95, $y - 2);
            $this->Line(115, $y - 2, 190, $y - 2);
        } else {
            $half = $this->GetPageWidth() / 2;
            $this->Line($half - 45, $y - 2, $half + 45, $y - 2);
        }
        if (!$devolucion) {
            $this->Cell(95, 10, 'Encargado departamento de Sistemas', 0, 0, 'C');
            $this->Cell(95, 10, 'Nombre y firma del usuario que acepta el(los) bienes', 0, 0, 'C');
        } else {
            $this->Cell(190, 10, 'Encargado departamento de Sistemas', 0, 0, 'C');
        }
    }
}
