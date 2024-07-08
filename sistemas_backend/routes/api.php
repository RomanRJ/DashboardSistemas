<?php

use App\Http\Controllers\GuardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServerStatusController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('jwt.auth')->group(function () {

    Route::get('/inventario', [InventoryController::class, 'GetInventory']);

    Route::get('/inventario/disponible', [InventoryController::class, 'GetAvailable']);

    Route::get('/inventario/asignado', [InventoryController::class, 'GetInventory']);

    Route::get('/inventario/inactivo', [InventoryController::class, 'GetInventory']);

    Route::get('/historial/{id}', [InventoryController::class, 'GetHistory']);

    Route::post('/activo', [ItemController::class, 'NewItem']);

    Route::put('/activo/{id}', [ItemController::class, 'UpdateItem']);

    Route::post('/baja', [ItemController::class, 'SetItemUnavailable']);

    Route::post('/baja-test', [ItemController::class, 'BajaTest']);

    Route::post('/multibaja', [ItemController::class, 'MultiBaja']);

    Route::get('/resguardos', [InventoryController::class, 'GetGuards']);

    Route::post('/resguardos', [GuardController::class, 'NewGuard']);

    Route::post('/reimpresion', [GuardController::class, 'RePrint']);

    Route::post('/devolucion', [GuardController::class, 'ReturnItems']);

    Route::post('/reimpresion/devolucion', [GuardController::class, 'PrintDev']);

    Route::get('/status', [ServerStatusController::class, 'GetStatus']);

    Route::get('/logs/{server}', [ServerStatusController::class, 'GetLogs']);

    // Route::post('/estatus/')
});
