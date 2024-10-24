<?php

namespace App\Services\ShippingStation;

use App\Services\AppService;

use Libs\Bixolon\BixolonService;
use Libs\Datasource\DatasourceService;

class PrintEcoGreenPackageLabelService extends AppService
{
    protected $pyaload;

    public function __construct()
    {
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function exec()
    {
        $this->validate(
            $this->payload,
            [
                'lang' => 'required|string|in:io,eu,tw,jp,th,vn',
                'stationNo' => 'required|string',
                'shipmentItem' => 'required|string',
                'printQuantity' => 'required|integer',
                'sku' => 'required|string',
                'productTitle' => 'required|string'
            ],
            [
                'lang.required' => '語系必須有值。',
                'lang.in' => '語系值需為 io,eu,jp,th,tw,vn 其中一個。',
                'stationNo.required' => '包裝站編號必須有值。',
                'shipmentItem.required' => 'Shipment Item 必須有值。',
                'printQuantity.required' => '列印標籤數量必須有值。',
                'sku' => 'SKU 必須有值。',
                'productTitle' => '產品名稱必須有值。'
            ]
        );

        $ecoGreenPackageInfo = app(DatasourceService::class)
            ->getEcoGreenPackageInfo([
                'sku' => $this->payload['sku'],
                'lang' => $this->payload['lang']
            ]);

        $ecoGreenPackageInfo['lang'] = $this->payload['lang'];
        $ecoGreenPackageInfo['stationNo'] = $this->payload['stationNo'];
        $ecoGreenPackageInfo['shipmentItem'] = $this->payload['shipmentItem'];
        $ecoGreenPackageInfo['printQuantity'] = $this->payload['printQuantity'];
        $ecoGreenPackageInfo['productTitle'] = $this->payload['productTitle'];

        $result = app(BixolonService::class)
            ->printEcoGreenPackageLabel($ecoGreenPackageInfo);

        return $result;
    }
}
