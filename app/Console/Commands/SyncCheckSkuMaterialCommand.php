<?php

namespace App\Console\Commands;

use App\Repositories\MaterialRepository;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SyncCheckSkuMaterialCommand extends Command
{
    private $materialRepository;

    private $countNewMaterial = 0;
    private $countUpdateMaterial = 0;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:materialsCheckSku';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fillCheckSku';

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
        $fillCheckSkuCount = 0;

        $emptyCheckSkus = $this->materialRepository->getEmptyCheckSku()->toArray();

        foreach ($emptyCheckSkus as $emptyCheckSku) {
            $findCheckSku = $this->materialRepository->getCheckSku($emptyCheckSku["sku"])->get(0);
            if (!is_null($findCheckSku)) {
                $this->materialRepository->update($emptyCheckSku["id"], ["check_sku" => $findCheckSku["sku"]]);
                $fillCheckSkuCount ++;
            }else{
                $this->materialRepository->update($emptyCheckSku["id"], ["check_sku" => $emptyCheckSku["sku"]]);
                $fillCheckSkuCount ++;
            }
        }
        echo $fillCheckSkuCount;
        return 0;
    }
}
