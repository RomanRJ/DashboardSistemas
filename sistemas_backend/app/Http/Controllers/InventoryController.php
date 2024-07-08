<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Guard;
use App\Models\History;

class InventoryController extends Controller
{

    private function format_item($item, $resguardo, $status, $username = null, $departamento = null)
    {
        return  [
            'id' => $item['id'],
            'estado' => $status,
            'nombre' => $item['nombre'],
            'descripcion' => $item['descripcion'],
            'no_serie' => $item['no_serie'],
            'marca' => $item['marca'],
            'modelo' => $item['modelo'],
            'caracteristicas' => $item['caracteristicas'],
            'agregados' => $item['agregados'],
            'fecha_compra' => $item['fecha_compra'],
            'fecha_carga' => $item['fecha_carga'],
            'proveedor' => $item['proveedor'],
            'precio' => $item['precio'],
            'garantia' => $item['garantia'],
            'resguardo' => $resguardo,
            'username' => $username,
            'departamento' => $departamento,
            'activo' => $item['activo'],
            'motivo_baja' => $item['motivo_baja'],
            'fecha_baja' => $item['fecha_baja'],
            'tipo' => $item['tipo']
        ];
    }

    private function format_history($item)
    {
        return [
            'idResguardo' => $item['id'],
            'nombre' => $item['nombre'],
            'no_serie' => $item['no_serie'],
            'marca' => $item['marca'],
            'agregados' => $item['agregados'],
            'usuario' => $item['usuario'],
            'departamento' => $item['departamento'],
            'fechaPrestamo' => $item['fecha_entrega'],
            'fechaDevolucion' => $item['fecha_devolucion'],
            'condiciones_entrega' => $item['condiciones_entrega'],
            'condiciones_devolucion' => $item['condiciones_devolucion'],
            'motivo' => $item['motivo']
        ];
    }

    public function GetInventory()
    {
        $items = [];
        $resguardo = '';
        $username = '';
        $departamento = '';
        $all_items = Item::all();
        foreach ($all_items as $item) {
            //traer resguardo con el modelo Guard
            if (!empty($item['resguardo']) || $item['resguardo'] != null) {
                $resguardo_db = Guard::find($item['resguardo']);
                if ($resguardo_db != null) {
                    $resguardo = $resguardo_db->usuario;
                    $username = $resguardo_db->username;
                    $departamento = $resguardo_db->departamento;
                    $status = 2;
                }
            } else {
                $resguardo = NULL;
                $username = NULL;
                $departamento = NULL;
                $status = 2;

                $status = 1;
            }
            if ($item['activo'] > 1 || $item['activo'] == 0) {
                $status = $item['activo'];
            }
            $formatted = $this->format_item($item, $resguardo, $status, $username, $departamento);
            array_push($items, $formatted);
        }
        return response()->json($items);
    }


    public function GetAvailable()
    {
        $items = [];
        $resguardo = '';
        $available = Item::where(function ($query) {
            $query->where('resguardo', '')->orWhereNull('resguardo');
        })->where('activo', 1)->get();

        foreach ($available as $item) {
            //traer resguardo con el modelo Guard
            $status = 1;
            $formatted = $this->format_item($item, $resguardo, $status);
            array_push($items, $formatted);
        }
        return response()->json($items);
    }


    public function GetHistory($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return response()->json(['error' => 'Parámetros de búsqueda no válidos'], 400);
        }

        $idInventario = $id;
        $history = [];
        $result = History::join('inventario', 'articulo_por_resguardo.idActivo_FK', '=', 'inventario.id')
            ->join('resguardos', 'articulo_por_resguardo.idResguardo_FK', '=', 'resguardos.id')
            ->where('idActivo_FK', '=', $idInventario)
            ->select('resguardos.id', 'nombre', 'no_serie', 'marca', 'descripcion', 'agregados', 'usuario', 'departamento', 'fecha_entrega', 'fecha_devolucion', 'condiciones', 'condiciones_devolucion', 'motivo')
            ->orderBy('fecha_entrega', 'DESC')
            ->get();
        foreach ($result as $item) {
            array_push($history, $this->format_history($item));
        }
        return response()->json($history, 200);
    }


    public function GetGuards()
    {
        $resguardos = [];
        $guards_db = Guard::all()->sortBy('id', SORT_REGULAR, true);
        foreach ($guards_db as $guard) {
            $guard_items = [];
            $items = History::join('inventario', 'articulo_por_resguardo.idActivo_FK', '=', 'inventario.id')
                ->join('resguardos', 'articulo_por_resguardo.idResguardo_FK', '=', 'resguardos.id')
                ->where('idResguardo_FK', '=', $guard->id)
                ->select('inventario.id', 'nombre', 'fecha_devolucion', 'no_serie', 'modelo', 'condiciones', 'condiciones_devolucion', 'descripcion')
                ->get();
            foreach ($items as $item) {
                $item->devuelto = isset($item->fecha_devolucion);
                array_push($guard_items, $item);
            }
            array_push($resguardos, ["resguardo" => $guard, "articulos" => $items]);
        }
        return response()->json($resguardos, 200);
    }
    public function GetFulfilledGuards()
    {
        //para los resguardos que ya fueron devueltos
    }
}
