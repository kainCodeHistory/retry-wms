<?php

namespace Libs\ShippingServer;

use Libs\ShippingServer\Service;

class ShippingServerService extends Service
{
    public function allocateOneGridBox(array $payload)
    {
        $url = $this->endpoint . "/api/batch/allocate-one-grid-box";
        return $this->sendRequest("POST", $url, $payload);
    }

    public function bindBoxesWithPickingCar(array $payload)
    {
        $url = $this->endpoint . "/api/batch/bind-boxes-with-picking-car";
        return $this->sendRequest("POST", $url, $payload);
    }

    public function bypassBatchItem(array $payload)
    {
        $url = $this->endpoint . "/api/batch/bypass-batch-item";
        return $this->sendRequest("POST", $url, $payload);
    }

    public function deleteShipmentFromBatch(array $payload)
    {
        $url = $this->endpoint . "/api/batch/delete-shipment";
        return $this->sendRequest("POST", $url, $payload);
    }
    public function deleteShipmentFromBatchSetOutOfStock(array $payload)
    {
        $url = $this->endpoint . "/api/batch/delete-shipment-set-out-of-stock";
        return $this->sendRequest("POST", $url, $payload);
    }

    public function resetBatchItemLocation(array $payload)
    {
        $url = $this->endpoint . "/api/batch/reset-location";
        return $this->sendRequest("POST", $url, $payload);
    }

    public function generateECNPickingList()
    {
        $url = $this->endpoint . "/api/ecn/picking-list";
        return $this->sendRequest("GET", $url, []);
    }

    public function getECNShipments()
    {
        $url = $this->endpoint . "/api/ecn/shipments";
        return $this->sendRequest("GET", $url, []);
    }

    public function generateNoStockPickingList(int $isSample)
    {
        $url = $this->endpoint . "/api/no-stock/picking-list/" . $isSample;
        return $this->sendRequest("GET", $url, []);
    }

    public function getAllocateRule(array $payload)
    {
        $url = $this->endpoint . "/api/shipment/get-allocate-rule";
        return $this->sendRequest("POST", $url, $payload);
    }

    public function getAZLocationsByDate(string $date)
    {
        $url = $this->endpoint . "/api/no-stock/get-az-locations/date/" . $date;
        return $this->sendRequest("GET", $url, []);
    }

    public function getAZLocationsByStatus(string $status)
    {
        $url = $this->endpoint . "/api/no-stock/get-az-locations/status/" . $status;
        return $this->sendRequest("GET", $url, []);
    }

    public function getCurrentBatchesByCar(array $payload)
    {
        $url = $this->endpoint . "/api/picking-car/current-batches";
        return $this->sendRequest("POST", $url, $payload);
    }

    public function getCurrentPackingBatchByBox(string $boxNo)
    {
        $url = $this->endpoint . "/api/ware/get-current-packing-batch/" . $boxNo;
        return $this->sendRequest("GET", $url, []);
    }

    public function getNoStockItemsByLocation(string $location)
    {
        $url = $this->endpoint . "/api/no-stock/get-items/" . $location;
        return $this->sendRequest("GET", $url, []);
    }

    public function releaseLocation(array $payload)
    {
        $url = $this->endpoint . "/api/no-stock/release-location";
        return $this->sendRequest("POST", $url, $payload);
    }
    public function discardItems(array $payload)
    {
        $url = $this->endpoint . "/api/no-stock/discard-items";
        return $this->sendRequest("POST", $url, $payload);
    }

    public function getNoStockShipmentsByEanOrSku(string $eanSku)
    {
        $url = $this->endpoint . "/api/no-stock/ean-sku/" . $eanSku;
        return $this->sendRequest("GET", $url, []);
    }

    public function upsertPickingAreaInventory(string $checkSku, string $event, string $location, int $locationPriority, int $quantity)
    {
        $url = "{$this->endpoint}/api/picking-area-inventory";
        $form = [
            'checkSku' => $checkSku,
            'event' => $event,
            'location' => $location,
            'locationPriority' => $locationPriority,
            'quantity' => $quantity
        ];

        return $this->sendRequest("POST", $url, $form);
    }

    public function upsertB2BPickingAreaInventory(string $sku, string $event, string $location, int $locationPriority, int $quantity)
    {
        $url = "{$this->endpoint}/api/picking-area-inventory/b2b";
        $form = [
            "sku" => $sku,
            'event' => $event,
            "location" => $location,
            "locationPriority" => $locationPriority,
            "quantity" => $quantity
        ];


        return $this->sendRequest("POST", $url, $form);
    }

    public function getPickingAreaInventory()
    {
        $url = $this->endpoint . "/api/picking-area-inventory";
        return $this->sendRequest("GET", $url, []);
    }

    public function getBCustomizedShipment()
    {
        $url = $this->endpoint . "/api/shipment/get-allocate-b-shipment";
        return $this->sendRequest("GET", $url, []);
    }
    public function getAppendBCustomizedShipment()
    {
        $url = $this->endpoint . "/api/shipment/get-append-allocate-b-shipment";
        return $this->sendRequest("GET", $url, []);
    }

    public function getOutOfStockItem()
    {
        $url = $this->endpoint . "/api/out-of-stock/item";
        return $this->sendRequest("GET", $url, []);
    }

    public function countOutOfStockItem(array $payload)
    {
        $url = $this->endpoint . "/api/out-of-stock/item";

        return $this->sendRequest("POST", $url, $payload);
    }

    public function getPickedItemsByBatchKey(string $batchKey)
    {
        $url = sprintf("%s/api/batch/picked-items/%s", $this->endpoint, $batchKey);

        return $this->sendRequest("GET", $url, []);
    }
}
