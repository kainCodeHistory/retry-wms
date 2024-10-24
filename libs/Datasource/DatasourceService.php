<?php

namespace Libs\Datasource;

use Libs\Datasource\Service;

class DatasourceService extends Service
{
    public function getEcoGreenPackageInfo(array $payload)
    {
        $url = $this->endpoint . "/api/label/eco-green-package";
        return $this->sendRequest("POST", $url, $payload);
    }
}
