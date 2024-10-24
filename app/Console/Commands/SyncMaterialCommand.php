<?php

namespace App\Console\Commands;

use App\Repositories\MaterialRepository;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SyncMaterialCommand extends Command
{
    private $materialRepository;

    private $countNewMaterial = 0;
    private $countUpdateMaterial = 0;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:materials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Material With TT';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->materialRepository = new MaterialRepository();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        list($items, $nextPageUrl) = $this->fetchSku();

        $materials = collect([]);
        $materialTypes = collect([]);

        while (count($items) > 0) {
            foreach ($items as $item) {
                $sku = (string)$item['sku'];
                $skuName = empty($item['name']) ? '' : $item['name'];
                $ean = empty($item['ean']) ? '' : $item['ean'];
                $upc = empty($item['upc']) ? '' : $item['upc'];

                $material = $this->materialRepository->search([
                    'sku' => $sku
                ]);

                if (count($material) > 0) {
                    $material = $material->get(0);
                    if ($skuName !== $material->name) {
                        $materials->put($material->id, [
                            'full_name' => $skuName,
                            'display_name' => $skuName,
                            'ean' => $ean,
                            'upc' => $upc,
                            'updated_at' => $item['updatedAt']
                        ]);
                    }
                } else {
                    $materials->put('new-' . $sku, [
                        'sku' => $sku,
                        'full_name' => $skuName,
                        'display_name' => $skuName,
                        'ean' => $ean,
                        'upc' => $upc,
                        'created_at' => $item['updatedAt'],
                        'updated_at' => $item['updatedAt']
                    ]);
                }

                if ($materials->count() === 50) {
                    $this->upsertMaterials($materials);
                    $materials = collect([]);
                }
            }

            if (empty($nextPageUrl)) {
                $items = [];
            } else {
                list($items, $nextPageUrl) = $this->fetchSku($nextPageUrl);
            }
        }

        if ($materials->count() > 0) {
            $this->upsertMaterials($materials);
        }

        echo 'Materials created: ' . $this->countNewMaterial . ', updated: ' . $this->countUpdateMaterial;
        return 0;
    }

    private function fetchSku(string $endPoint = '')
    {
        /**
         * 
         * type: 產品類別
         * fromDate: 開始時間 yyyy-MM-dd HH:mm
         * toDate: 結束時間 yyyy-MM-dd HH:mm
         */
        if (empty($endPoint)) {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-ERP-ACCESS-TOKEN' => config('app.erp.headers.access_token'),
                'Origin' => config('app.erp.headers.origin')
            ])->get(config('app.erp.urls.sku'), [
                'shop_number' => ''
            ]);
        } else {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-ERP-ACCESS-TOKEN' => config('app.erp.headers.access_token'),
                'Origin' => config('app.erp.headers.origin')
            ])->get($endPoint);
        }

        if ($response->ok()) {
            $jsonObj = $response->json()['data'];
            return [
                $jsonObj['data'],
                $jsonObj['next_page_url']
            ];
        } else {
            return [
                [],
                null
            ];
        }
    }

    private function upsertMaterials(Collection $materials)
    {
        $items = $materials->filter(function($material, $key) {
            return !is_numeric($key);
        });

        if ($items->count() > 0) {
            $this->materialRepository->createMany($items->values()->all());
            $this->countNewMaterial += $items->count();
        }

        $items = $materials->filter(function($material, $key) {
            return is_numeric($key);
        });

        if ($items->count() > 0) {
            $items->each(function($item, $key) {
                $this->materialRepository->update((int)$key, $item);
            });
            $this->countUpdateMaterial += $items->count();
        }
    }
}
