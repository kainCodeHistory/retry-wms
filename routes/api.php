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
//B2B紙本撿料
Route::post('/b2b/paper/picked-item', [B2BController::class, 'addB2bPickedItem']);
Route::get('/material/{sku}', [MaterialController::class, 'get']);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);


// 庫存扣帳
Route::post('/inventory/debit', [StorageBoxOutputController::class, "inventoryDebit"]);
Route::post('/b2b-inventory/debit', [B2BController::class, "inventoryDebit"]);

Route::get('/material/check/{location}/{storageBox}', [MaterialController::class, 'checkLocation']);
Route::post('/material', [MaterialController::class, 'upsertMaterial']);
Route::post('/material/alert-check-point', [MaterialController::class, 'upsertAlertCheckPoint']);
Route::post('/material/boms', [MaterialController::class, 'getBoms']);
Route::post('/material/storageBoxes', [MaterialController::class, 'getStorageBoxes']);

Route::get("/picking-area/refill/aa-zone/list", [RefillController::class, 'downloadAAZoneRefillList']);
Route::get("/picking-area/stock-warning-report", [RefillController::class, 'getStockWarningReport']);

//掃B類分箱
Route::post("/allocate-b-customized", [QueryController::class, 'allocateBCustomized']);
Route::post("/allocate-box/reset", [QueryController::class, 'resetAllocateBox']);
Route::get("/allocate-box/search/{allocateBox}", [QueryController::class, 'searchAllocateBCustomized']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    //箱號檢查
    Route::get("/storage-box/check/{storageBox}", [StorageBoxInputController::class, 'checkStorageBox']);

    // 預備倉 入庫
    Route::get("/storage-box/input/binding/{storageBox}", [StorageBoxInputController::class, 'getBinding']);
    Route::post("/storage-box/input/bind-material", [StorageBoxInputController::class, 'bindMaterial']);
    Route::post("/storage-box/input/bind-location", [StorageBoxInputController::class, 'bindLocation']);
    Route::post("/storage-box/input/quantity", [StorageBoxInputController::class, 'updateQuantity']);

    // 預備倉 出庫
    Route::post("/storage-box/output/reset", [StorageBoxOutputController::class, "reset"]);
    Route::post("/storage-box/output/reset-location", [StorageBoxOutputController::class, "resetLocation"]);

    // 補料作業
    Route::delete("/picking-area/refill/{recordId}", [RefillController::class, "deleteRecord"]);
    Route::get("/picking-area/refill", [RefillController::class, 'getRecord']);
    Route::get("/picking-area/refill/location/{storageBox}", [RefillController::class, 'getLocation']);
    Route::post("/picking-area/refill", [RefillController::class, 'addRecord']);
    Route::post("/picking-area/refill/location", [RefillController::class, "bindLocation"]);
    Route::put("/picking-area/refill/quantity", [RefillController::class, "updateRecord"]);
    Route::post("/picking-area/refill/ACMNLocation", [RefillController::class, 'updateACMNLocation']);
    Route::get("/picking-area/get-refill-list", [RefillController::class, 'getRefillRecord']);

    // AC 區
    Route::get('/picking-area/ac/{storageBox}', [RefillController::class, 'getACLocation']);
    Route::post('/picking-area/ac', [RefillController::class, 'bindACLocation']);

    //成品倉轉倉紀錄
    Route::get('/rollover/{ean}', [RefillController::class, 'getRolloverSku']);
    Route::post('/rollover-record', [RefillController::class, 'rolloverRecord']);
    Route::get("/rollover-record/report", [RefillController::class, 'dowloadRolloverFile']);

    // 查詢
    Route::get('/query/ean-sku/{eanSku}', [QueryController::class, 'getLocations']);
    Route::get('/query/storage-box/{barcode}', [QueryController::class, 'getMaterial']);
    Route::get('/query/bindSkuTime/{BindSku}', [QueryController::class, 'getBindTimes']);
    Route::get('/query/storage-box-sku/{storageBox}', [QueryController::class, 'getStorageBoxes']);

    Route::get('/stock/sku-list', [B2CStockController::class, 'skuList']);
    Route::get('/stock-logs/{sku}/', [B2CStockController::class, 'getSkuDate']);

    Route::get("/stock/{eanSku}", [B2CStockController::class, "getStock"]);
    Route::put('/stock/{eanSku}', [B2CStockController::class, "adjustStock"]);

    //儲位查詢
    Route::get("/locations/{Sku}", [SingleController::class, 'getLocations']);
    Route::post("/locations", [SingleController::class, 'editLocations']);
    Route::get("/locations-download", [BatchInOutController::class, 'dowloadFile']);
    Route::post("/locations-upload", [BatchInOutController::class, 'uploadFile']);

    //盤點
    Route::get("/inventory/{box}/{warehouse}", [InventoryController::class, 'getBox']);
    Route::get("/firstInventory/{box}", [InventoryController::class, 'getFirstRecord']);
    Route::post("/firstInventory/quantity", [InventoryController::class, 'firstInventory']);
    Route::post("/checkInventory/quantity", [InventoryController::class, 'checkInventory']);
    Route::get("/Inventory/report", [InventoryController::class, 'dowloadFirstInventoryFile']);
    Route::get("/checkInventory/report", [InventoryController::class, 'dowloadCheckInventoryFile']);

    //sku找儲位
    Route::post("/sku-bind-location-upload", [BatchInOutController::class, 'uploadSkuReturnFile']);
    Route::get("/sku-bind-location-download/{filename}", [BatchInOutController::class, 'dowloadSkuFile']);

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

    //B2B修正撿料記錄
    Route::post('/b2b/picked-items/fix-quantity', [B2BController::class, 'fixQuantity']);

    //B2B撿料記錄
    Route::get('/b2b/picked-items/export/{start_date}/{end_date}/{sku}', [B2BController::class, 'dowloadPickedItemRecordFile']);
});
