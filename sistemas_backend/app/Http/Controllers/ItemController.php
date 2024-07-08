<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Repairment;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Helpers\FichaBajaActivo;
use App\Http\Helpers\FichaMultiBaja;
use App\Models\Baja;

class ItemController extends Controller
{

    private function GetUpdateFields($new)
    {
        //qué podemos cambiar?
        $updated = [];
        if (!empty($new->nombre)) {
            $updated["nombre"] = $new->nombre;
        }
        if (!empty($new->descripcion)) {
            $updated["descripcion"] = $new->descripcion;
        }
        if (!empty($new->caracteristicas)) {
            $updated["caracteristicas"] = $new->caracteristicas;
        }
        if (!empty($new->modelo)) {
            $updated["modelo"] = $new->modelo;
        }
        if (!empty($new->agregados)) {
            $updated["agregados"] = $new->agregados;
        }
        if (!empty($new->marca)) {
            $updated["marca"] = $new->marca;
        }
        if (!empty($new->no_serie)) {
            $updated["no_serie"] = $new->no_serie;
        }
        if (!empty($new->proveedor)) {
            $updated["proveedor"] = $new->proveedor;
        }
        if (!empty($new->tipo)) {
            $updated['tipo'] = $new->tipo;
        }
        return $updated;
    }

    private function GenerateRepairTicket($request)
    {
        date_default_timezone_set('America/Mexico_City');
        if (empty($request->procedimiento)) {
            throw new ErrorException("Se requiere procedimiento de reparación");
        }
        $ticket = new Repairment();
        $ticket->idActivo_FK = $request->id;
        $ticket->fecha_solicitud = date('Y-m-d h:i a');
        $ticket->procedimiento;
        $ticket->save();
        return $ticket;
    }

    private function ValidateRecievedData($data)
    {
        if (
            !$data->nombre ||
            empty($data->descripcion) ||
            !$data->serie ||
            !$data->marca ||
            !$data->modelo
        ) {
            return false;
        }
        return true;
    }

    public function NewItem(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');
        if (!$this->ValidateRecievedData($request)) {
            return response()->json(["error" => "Los datos requeridos están incompletos"], 400);
        }

        $item = new Item();
        $item->nombre = $request->nombre;
        $item->descripcion = $request->descripcion;
        $item->no_serie = $request->serie;
        $item->marca = $request->marca;
        $item->modelo = $request->modelo;
        $item->caracteristicas = $request->caracteristicas;
        $item->agregados = $request->agregado ? $request->agregado : "";
        $item->fecha_compra = $request->compra;
        $item->proveedor = $request->proveedor;
        $item->precio = $request->precio;
        $item->garantia = $request->garantia;
        $item->tipo = $request->tipo;
        $item->fecha_carga = date('Y-m-d');

        try {
            $item->save();
        } catch (QueryException $ex) {
            return response()->json(["error" => "Algo salió mal " . $ex->getMessage()], 500);
        } catch (ErrorException $ex) {
        }

        return response()->json($item, 200);
    }

    public function UpdateItem($id, Request $request)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return response()->json(["error" => "Identificador no válido"], 400);
        }
        $original = Item::find($id);
        if (!$original) {
            return response()->json(["error" => "Este artículo no existe"], 400);
        }

        $updated_data = $this->GetUpdateFields($request);
        try {
            $result = Item::whereId($id)->update($updated_data);
            if ($result) {
                $new_item = $original->refresh();
            } else {
                return response()->json(["error" => "Sin cambios para realizar "], 200);
            }
        } catch (QueryException $ex) {
            return response()->json(["error" => "Algo salió mal " . $ex->getMessage()], 500);
        }

        return response()->json([$new_item], 200);
    }

    public function SetItemUnavailable(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');
        $id = $request->input('id');
        $motivo_baja = $request->input('motivo_baja');
        $estado = $request->input('tipo');


        if (empty($id) || empty($motivo_baja) || $estado == null) {
            return response()->json(["error" => "Datos incompletos o corruptos", "request" => $request->all()], 400);
        }

        $existing = Item::find($id);

        if (!$existing) {
            return response()->json(["error" => "Este Activo no Existe"], 404);
        }

        //acordar qué hacemos en casos donde se quiere dar de baja en resguardo sin devolver.
        if ($existing->resguardo != null) {
            return response()->json(["advertencia" => "el articulo está prestado actualmente"], 409);
        }

        try {
            //si va a reparación
            if ($estado == 4) {
                $result = $this->GenerateRepairTicket($request);
            }

            if ($estado == 0 || $estado == 3) {
                if (strtolower($motivo_baja) != 'robo') {
                    $request->validate(['evidencia' => 'required|file|mimes:jpeg,png,jpg']);
                    $evidencia = $request->file('evidencia');
                    $extension = $evidencia->getClientOriginalExtension();
                    $new_name =  date('Y-m-d') . "-" . $id . "-baja" . "." . $extension;
                    $evidencia->move(public_path('evidencia_bajas'), $new_name);
                } else {
                    $new_name = "NA";
                }

                $result = Item::whereId($id)->update(["activo" => $estado, "motivo_baja" => $motivo_baja, "evidencia_baja" => $new_name]);
            } else {

                $result = Item::whereId($id)->update(["activo" => $estado,  "motivo_baja" => $motivo_baja]);
            }

            if ($result) {
                $updated = $existing->refresh();
                response()->json(["data" => $updated], 200);
            } else {
                return response()->json(["error" => "Nada para Actualizar"], 200);
            }
        } catch (QueryException $ex) {
            return response()->json(["error" => $ex->getMessage()], 500);
        }

        return response()->json([$existing], 200);
    }

    public function BajaTest(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');
        $id = $request->input('id');
        $motivo_baja = $request->input('motivo_baja');
        $existing = Item::find($id);
        if (!$existing) {
            return response()->json(["error" => "Este Activo no Existe", "ex" => $id], 404);
        }
        $evidencia = $request->file('evidencia');
        $extension = $evidencia->getClientOriginalExtension();
        $ficha = new FichaBajaActivo($existing, $evidencia, $motivo_baja);
        $ficha->ImprimirBaja();
        $result = $ficha->Output('S', 'Test_baja.pdf');

        return response($result)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename=' . 'baja.pdf');
    }


    private function InsertBaja($activos)
    {
        $ultima_baja = Baja::max('id_baja');
        $id_baja = $ultima_baja + 1;
        foreach ($activos as $activo) {
            $baja = new Baja();
            $baja->id_baja = $id_baja;
            $baja->id_activo_FK = $activo->id;
            $baja->save();
        }
        return $id_baja;
    }

    public function MultiBaja(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');
        $articulos = [];
        $rutas = [];
        $images = $request->file('images');
        $ids = $request->input('ids');
        $fecha_baja = date('Y-m-d');
        foreach ($ids as $id) {
            $item = Item::find($id);
            array_push($articulos, $item);
        }

        foreach ($images as $image) {
            $filename = $image->getClientOriginalName();
            $image->move(public_path('images'), $filename);
            $path = public_path('images') . '/' . $filename;
            array_push($rutas, $path);
        }
        for ($i = 0; $i < count($articulos); $i++) {
            $id = $articulos[$i]->id;
            $evidencia = $rutas[$i];
            Item::whereId($id)->update(["activo" => 0, "motivo_baja" => "Baja Definitiva (Multibaja)", "evidencia_baja" => $evidencia, "fecha_baja" => $fecha_baja]);
        } //puede haber cambios aquí si se añade el motivo de baja
        $id_baja = $this->InsertBaja($articulos);
        $ficha = new FichaMultiBaja($articulos, $rutas);
        $ficha->ImprimirBajas($id_baja);
        $name = 'Baja Multiple' . '.pdf';
        $result = $ficha->Output('S', $name);

        return response($result)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename=' . $name);
    }
}
