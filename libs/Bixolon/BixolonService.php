<?php

namespace Libs\Bixolon;

use Libs\Bixolon\Service;

class BixolonService extends Service
{
    public function printEcoGreenPackageLabel(array $payload)
    {
        $url = $this->endpoint . "/api/print/eco-green-package-label";
        return $this->sendRequest("POST", $url, $payload);
    }
}
