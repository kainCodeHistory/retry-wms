<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\B2BController;
use App\Http\Controllers\LocationCRUD\SingleController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PickingArea\RefillController;
use App\Http\Controllers\QueryController;
use App\Http\Controllers\LocationCRUD\BatchInOutController;
use App\Http\Controllers\StorageBox\InputController as StorageBoxInputController;
use App\Http\Controllers\StorageBox\OutputController as StorageBoxOutputController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\B2BCRUD\B2BInputController;
use App\Http\Controllers\B2BCRUD\B2BRefillController;
use App\Http\Controllers\B2BStockController;
use App\Http\Controllers\B2CStockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);


// 庫存扣帳
Route::post('/inventory/debit', [StorageBoxOutputController::class, "inventoryDebit"]);
Route::post('/b2b-inventory/debit', [B2BController::class, "inventoryDebit"]);

Route::group(['middleware' => 'auth:sanctum'], function () {
  
    // AC 區
    Route::get('/picking-area/ac/{storageBox}', [RefillController::class, 'getACLocation']);
    Route::post('/picking-area/ac', [RefillController::class, 'bindACLocation']);

    // 製造轉B2B 入庫紀錄
    Route::post("/b2b/input", [B2BController::class, 'addInput']);
    Route::delete("/b2b/input/{inputId}", [B2BController::class, 'deleteInput']);
    Route::get("/b2b/input-list", [B2BController::class, 'getInputList']);
    Route::get("/b2b/export/{workingDay}", [B2BController::class, "exportB2BInput"]);

    Route::delete("/b2b-5f/input/{inputId}", [B2BController::class, 'delete5FInput']);
    Route::get("/b2b-5f/input/{inputId}", [B2BController::class, 'get5FInput']);
    Route::post("/b2b-5f/inputs", [B2BController::class, "get5FInputList"]);
    Route::post("/b2b-5f/input", [B2BController::class, 'upsert5FInput']);

    //B2B盤點庫存
    Route::get("/b2b-5f/inventory/{eanSku}", [B2BController::class, 'get5FInventory']);
    Route::post("/b2b-5f/inventory/quantity", [B2BController::class, 'b2bInventory']);
    Route::get("/b2b-5f/first-inventory/{eanSku}", [B2BController::class, 'getb2bFirstInventory']);
    Route::post("/b2b-5f/check-inventory/quantity", [B2BController::class, 'b2bCheckInventory']);

    //B2B查詢
    Route::get('/b2b/query/ean-sku/{eanSku}', [QueryController::class, 'getB2BLocations']);
    Route::get('/b2b/query/storage-box/{barcode}', [QueryController::class, 'getB2BMaterial']);
    Route::get('/b2b/query/location/{barcode}', [QueryController::class, 'getB2BStorageBox']);
    Route::get('/b2b/stock/sku-list', [B2BStockController::class, 'skuList']);
    Route::get('/b2b/stock-logs/{sku}/', [B2BStockController::class, 'getSkuDate']);

    Route::post('/b2b/picked-items/record', [B2BController::class, 'searchPickedItemsRecord']);

    //B2B入庫
    Route::post("/b2b-5f/storage-box/input/bind-picking-Box", [B2BInputController::class, 'bindPickingBox']);
    Route::post("/b2b-5f/storage-box/input/bind-location", [B2BInputController::class, 'bindLocation']);
    Route::post('/b2b-5f/storage-box/input/xb', [B2BInputController::class, 'bindXBLocation']);
    //B2B補料

    Route::post("/b2b-5f/picking-area/refill/storage-box", [B2BRefillController::class, 'updateLocationQuantity']);

    //B2B出庫
    Route::post("/b2b-5f/input/quantity", [B2BController::class, 'updateQuantity']);

});
