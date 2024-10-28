<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\B2BController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PickingArea\RefillController;
use App\Http\Controllers\QueryController;
use App\Http\Controllers\StorageBox\InputController ;
use App\Http\Controllers\StorageBox\OutputController ;
use App\Http\Controllers\B2BCRUD\B2BInputController;
use App\Http\Controllers\B2BStockController;
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

Route::get('/material/{sku}', [MaterialController::class, 'get']);
// 庫存扣帳
Route::post('/b2b-inventory/debit', [B2BController::class, "inventoryDebit"]);

Route::group(['middleware' => 'auth:sanctum'], function () {
  
    // 特殊區
    Route::get('/picking-area/ac/{storageBox}', [RefillController::class, 'getACLocation']);
    Route::post('/picking-area/ac', [RefillController::class, 'bindACLocation']);

     //箱號檢查
     Route::get("/storage-box/check/{storageBox}", [InputController::class, 'checkStorageBox']);
     Route::get("/storage-box/input/binding/{storageBox}", [InputController::class, 'getBinding']);
    
    Route::delete("/b2b-5f/input/{inputId}", [B2BController::class, 'delete5FInput']);
    Route::get("/b2b-5f/input/{inputId}", [B2BController::class, 'get5FInput']);
    Route::post("/b2b-5f/inputs", [B2BController::class, "get5FInputList"]);
    Route::post("/b2b-5f/input", [B2BController::class, 'upsert5FInput']);


    //B2B查詢
    Route::get('/b2b/query/ean-sku/{eanSku}', [QueryController::class, 'getB2BLocations']);
    Route::get('/b2b/stock/sku-list', [B2BStockController::class, 'skuList']);
    Route::get('/b2b/stock-logs/{sku}/', [B2BStockController::class, 'getSkuDate']);


    //B2B入庫
    Route::post("/b2b-5f/storage-box/input/bind-picking-Box", [B2BInputController::class, 'bindPickingBox']);
    Route::post("/b2b-5f/storage-box/input/bind-location", [B2BInputController::class, 'bindLocation']);
    Route::post('/b2b-5f/storage-box/input/xb', [B2BInputController::class, 'bindXBLocation']);
    
    //B2B出庫
    Route::post("/b2b-5f/input/quantity", [B2BController::class, 'updateQuantity']);

});
