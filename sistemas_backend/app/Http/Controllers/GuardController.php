<?php

namespace App\Http\Controllers;

use App\Http\Helpers\FichaResguardo;
use Illuminate\Http\Request;
use App\Models\Guard;
use App\Models\Item;
use App\Models\History;
use Illuminate\Database\QueryException;

class GuardController extends Controller
{

    private function ValidateReturnedItems($db_items, $returning_items)
    {
        $conflicts = [];
        foreach ($returning_items as $item) {
            $exists = false;
            foreach ($db_items as $bd_item) {
                if ($item['id'] == $bd_item->id) {
                    $exists = true;
                }
            }
            if (!$exists) {

                $item_name = Item::find($item['id']);
                if (!$item_name) {
                    array_push($conflicts, [$item['id'] => "El Activo " . $item['id'] . " no existe"]);
                } else {
                    array_push($conflicts, [$item_name->nombre => "No está en el resguardo solicitado"]);
                }
            }
        }

        return $conflicts;
    }

    private function ValidateRecievedGuard($data)
    {
        $empleado = $data["empleado"];
        $articulos = $data["articulos"];
        if (empty($empleado["nombre"]) || empty($empleado["departamento"]) || empty($empleado["motivoPrestamo"]) || !count($articulos)) {
            return false;
        }
        foreach ($articulos as $item) {
            if (empty($item)) {
                return false;
            }
        }

        return true;
    }

    private function CheckItemsAvailability($items)
    {
        $conflicts = [];
        foreach ($items as $item) {
            $existing = Item::find($item);
            if (!$existing) {
                array_push($conflicts, ["Item: " . $item => "No existe"]);
            } else {
                if ($existing->activo == 0) {
                    array_push($conflicts, ["Item: " . $existing->nombre => "Inactivo"]);
                }
                if (!empty($existing->resguardo)) {
                    array_push($conflicts, ["Item: " . $existing->nombre => "En préstamo"]);
                }
            }
        }

        return $conflicts;
    }


    public function NewGuard(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');
        $articulos = [];
        $empleado = $request->empleado;
        $requested_items = $request->articulos;
        if (!$this->ValidateRecievedGuard($request)) {
            return response()->json(["error" => "Los datos requeridos están incompletos", "req" => $request->all()], 400);
        }

        $unavailability = $this->CheckItemsAvailability($requested_items);
        if (count($unavailability)) {
            return response()->json(["error" => "Uno o más Artículos tienen conflicto", "conflictos" => $unavailability], 409);
        }

        try {
            $resguardo = new Guard();
            $resguardo->usuario = $empleado["nombre"];
            $resguardo->nomina = $empleado["noNomina"] ? $empleado["noNomina"] : "";
            $resguardo->motivo = $empleado["motivoPrestamo"] ? $empleado["motivoPrestamo"] : "";
            $resguardo->fecha_entrega = date('Y-m-d h:i a');
            $resguardo->departamento = $empleado["departamento"] ? $empleado["departamento"] : "";
            $resguardo->condiciones = $empleado["condicionesEntrega"] ? $empleado["condicionesEntrega"] : "";
            $resguardo->tiempo = $empleado["tiempoPrestamo"] ? $empleado["tiempoPrestamo"] : "Indeterminado";

            $result = $resguardo->save();
            if (!$result) {
                return response()->json(["error" => "Error al guardar nuevo resguardo"], 500);
            }
            $resguardo = $resguardo->refresh();

            foreach ($requested_items as $item) {
                Item::whereId($item)->update(["resguardo" => $resguardo->id]);
                $history_record = new History();
                $history_record->idActivo_FK = $item;
                $history_record->idResguardo_FK = $resguardo->id;
                $history_record->save();
                array_push($articulos, Item::find($item));
            }
            $ficha = new FichaResguardo($resguardo, $articulos);
            $ficha->NuevaFicha(false, false);
            $name = 'resguardo_' . $resguardo->id . '_' . $resguardo->usuario . '.pdf';
            $ficha->Output('F', __DIR__ . '/temp/' . $name);
            $result = $ficha->Output('S', $name);
        } catch (QueryException $ex) {
            return response()->json(["error" => "Algo salió mal " . $ex->getMessage()], 500);
        }
        return response($result)->header('Content-Type', 'application/pdf')->header('Content-Disposition', 'attachment; filename=' . $name);
    }


    public function ReturnItems(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');
        $id_resguardo = $request->resguardo;
        $articulos = $request->articulos;
        $to_print = [];
        $condiciones_por_articulo = [];
        if (!$id_resguardo || !count($articulos)) {
            return response()->json(["error" => "Sin datos de resguardo o artículos"], 400);
        }
        $bd_guard = Guard::find($id_resguardo);

        if (!$bd_guard) {
            return response()->json(["error" => "El resguardo solicitado no existe"], 400);
        }

        $db_items = History::join('inventario', 'articulo_por_resguardo.idActivo_FK', '=', 'inventario.id')
            ->join('resguardos', 'articulo_por_resguardo.idResguardo_FK', '=', 'resguardos.id')
            ->where('idResguardo_FK', '=', $id_resguardo)
            ->select('inventario.id', 'nombre', 'fecha_devolucion', 'no_serie', 'modelo', 'condiciones', 'condiciones_devolucion')
            ->get();

        $conflicts = $this->ValidateReturnedItems($db_items, $articulos);

        if (count($conflicts)) {
            return response()->json(["error" => "uno o más artículos tienen conflicto", "conflictos" => $conflicts], 400);
        }

        $warnings = [];

        foreach ($articulos as $returned) {
            try {
                $id = $returned['id'];
                $condiciones = $returned['condiciones'];
                $reparacion = $returned['reparacion'];
                $fecha_retorno = date('Y-m-d h:i a');
                $resguardo = History::where('idResguardo_FK', '=', $id_resguardo)->where('idActivo_FK', '=', $id)->get();

                if (empty($condiciones)) {
                    $condiciones = "Sin Detalles";
                }

                if ($resguardo[0]->fecha_devolucion != null) {
                    array_push($warnings, ["articulo " . Item::find($resguardo[0]->idActivo_FK)->nombre . " marcado como devuelto el " . $resguardo[0]->fecha_devolucion]);
                } else {
                    History::where('idResguardo_FK', '=', $id_resguardo)
                        ->where('idActivo_FK', '=', $id)
                        ->update(['condiciones_devolucion' => $condiciones, 'fecha_devolucion' => $fecha_retorno]);
                }
                if (!$reparacion) {
                    Item::whereId($id)->update(["resguardo" => null, "activo" => 1]);
                } else {
                    Item::whereId($id)->update(["resguardo" => null, "activo" => 4]);
                }
                $updated_item = History::join('inventario', 'articulo_por_resguardo.idActivo_FK', '=', 'inventario.id')
                    ->join('resguardos', 'articulo_por_resguardo.idResguardo_FK', '=', 'resguardos.id')
                    ->where('idResguardo_FK', '=', $id_resguardo)->where('idActivo_FK', '=', $id)
                    ->select(
                        'resguardos.id',
                        'nombre',
                        'no_serie',
                        'marca',
                        'descripcion',
                        'agregados',
                        'usuario',
                        'departamento',
                        'fecha_entrega',
                        'fecha_devolucion',
                        'condiciones',
                        'condiciones_devolucion',
                        'motivo'
                    )
                    ->first();;
                array_push($to_print, $updated_item);
                $condiciones_por_articulo[$updated_item->nombre] = $condiciones;
                if ($updated_item->resguardo) {
                    return response()->json(["error" => "Error al actualizar el status del Activo " . $id, "result" => $updated_item], 500);
                }
            } catch (QueryException $ex) {
                return response()->json(["error" => "Error al actualizar status, copia esto y envialo a TI: " . $ex->getMessage()], 500);
            }
        }
        $ficha = new FichaResguardo($bd_guard, $to_print, $condiciones_por_articulo);
        $ficha->NuevaFicha(true, false);
        $name = 'devolucion_' . $id_resguardo . '_' . $bd_guard->usuario . '.pdf';
        $ficha->Output('F', __DIR__ . '/temp/' . $name);
        $result = $ficha->Output('S', $name);

        return response($result)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename=' . $name);
    }


    public function PrintGuard(Request $request)
    {
        $id = $request->resguardo;
        $resguardo = Guard::find($id);
        if (!$resguardo) return response()->json(["error" => "El resguardo no existe"]);
        $articulos = History::join('inventario', 'articulo_por_resguardo.idActivo_FK', '=', 'inventario.id')
            ->join('resguardos', 'articulo_por_resguardo.idResguardo_FK', '=', 'resguardos.id')
            ->where('idResguardo_FK', '=', $id)
            ->select('descripcion', 'nombre', 'no_serie', 'marca', 'modelo', 'caracteristicas')
            ->get();
        try {

            $ficha = new FichaResguardo($resguardo, $articulos);
            $ficha->NuevaFicha(false, false);
            $name = 'reimpresion -' . $resguardo->id . '_' . $resguardo->usuario . date('y-m-d') . '.pdf';
            $ficha->Output('F', __DIR__ . '/temp/' . $name);
            $result = $ficha->Output('S', $name);
            return response($result)->header('Content-Type', 'application/pdf')->header('Content-Disposition', 'attachment; filename=' . $name);
        } catch (QueryException $ex) {
            return response()->json(["error" => $ex->getMessage()]);
        }
    }
    public function RePrint(Request $request)
    {
        $id = $request->resguardo;
        $resguardo = Guard::find($id);
        if (!$resguardo) return response()->json(["error" => "El resguardo no existe"]);
        $articulos = History::join('inventario', 'articulo_por_resguardo.idActivo_FK', '=', 'inventario.id')
            ->join('resguardos', 'articulo_por_resguardo.idResguardo_FK', '=', 'resguardos.id')
            ->where('idResguardo_FK', '=', $id)
            ->select('descripcion', 'nombre', 'no_serie', 'marca', 'modelo', 'caracteristicas')
            ->get();
        try {

            $ficha = new FichaResguardo($resguardo, $articulos);
            $ficha->NuevaFicha(false, true);
            $name = 'reimpresion -' . $resguardo->id . '_' . $resguardo->usuario . date('y-m-d') . '.pdf';
            $ficha->Output('F', __DIR__ . '/temp/' . $name);
            $result = $ficha->Output('S', $name);
            return response($result)->header('Content-Type', 'application/pdf')->header('Content-Disposition', 'attachment; filename=' . $name);
        } catch (QueryException $ex) {
            return response()->json(["error" => $ex->getMessage()]);
        }
    }
    public function PrintDev(Request $request)
    {
        $id = $request->resguardo;
        $resguardo = Guard::find($id);
        $condiciones_por_articulo = [];
        if (!$resguardo) return response()->json(["error" => "El resguardo no existe"]);
        $articulos = History::join('inventario', 'articulo_por_resguardo.idActivo_FK', '=', 'inventario.id')
            ->join('resguardos', 'articulo_por_resguardo.idResguardo_FK', '=', 'resguardos.id')
            ->where('idResguardo_FK', '=', $id)
            ->whereNotNull('fecha_devolucion')
            ->select(
                'nombre',
                'no_serie',
                'marca',
                'modelo',
                'caracteristicas',
                'descripcion',
                'agregados',
                'usuario',
                'departamento',
                'fecha_entrega',
                'fecha_devolucion',
                'condiciones',
                'condiciones_devolucion',
                'motivo'
            )
            ->get();

        foreach ($articulos as $articulo) {
            $condiciones_por_articulo[$articulo->nombre] = $articulo->condiciones_devolucion;
        }
        try {
            $ficha = new FichaResguardo($resguardo, $articulos, $condiciones_por_articulo);
            $ficha->NuevaFicha(true, true);
            $name = 'reimpresion -' . $resguardo->id . '_' . $resguardo->usuario . date('y-m-d') . '.pdf';
            $ficha->Output('F', __DIR__ . '/temp/' . $name);
            $result = $ficha->Output('S', $name);
            return response($result)->header('Content-Type', 'application/pdf')->header('Content-Disposition', 'attachment; filename=' . $name);
        } catch (QueryException $ex) {
            return response()->json(["error" => $ex->getMessage()]);
        }
    }
}
