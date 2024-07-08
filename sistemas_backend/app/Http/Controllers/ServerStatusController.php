<?php

namespace App\Http\Controllers;

use App\Models\LogModel;
use App\Models\StatusModel;
use Illuminate\Http\Request;

class ServerStatusController extends Controller
{
    public function GetStatus()
    {
        $physical = [];
        $virtual = [];
        $web = [];
        $statuses = StatusModel::all()->sortBy('tipo');

        foreach ($statuses as $server) {
            if ($server->tipo == "VIRTUAL") {
                array_push($virtual, $server);
            }
            if ($server->tipo == "WEB") {
                array_push($web, $server);
            }
            if ($server->tipo == "FISICO") {
                array_push($physical, $server);
            }
        }

        return response()->json(['fisicos' => $physical, 'virtuales' => $virtual, 'web' => $web], 200);
    }

    public function GetLogs($server)
    {
        $logs = LogModel::where('id_srv', '=', $server)->orderBy('fechalog', 'desc')->get();

        return response()->json(["logs" => $logs], 200);
    }

    public function SaveLog(Request $request)
    {
        date_default_timezone_set("America/Mexico_City");
        $server = $request->server;
        //wooh woh woooh exploooode! 🎵🎶🎶
        $server_id = $request->id;
        $message = $request->message;
        $date = date('Y-m-d H:i:s');

        if (empty($server) || empty($message)) {
            return response()->json(["error" => "Datos insuficientes para realizar el registro"], 400);
        }

        $log = new LogModel();
        $log->id_srv = $server;
        $log->mensaje = $message;
        $log->fechalog = $date;
        $result = $log->save();

        $server_status = StatusModel::find($server);
    }
}
