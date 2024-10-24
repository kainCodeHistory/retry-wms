<?php

namespace Libs\NXCloud;

use Libs\NXCloud\Service;

class MaterialStockService extends Service
{
    public function updateMaterialStock($payload)
    {
        $url = $this->endpoint . "/material/stock";

        $response = $this->sendRequest("POST", $url, $payload);

        return $response;
    }
}
