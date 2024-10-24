<?php

namespace App\Services\StorageBox\Input;

use App\Repositories\StorageBox\StorageBoxRepository;
use App\Services\AppService;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckStorageBoxService extends AppService
{
    protected $storageBox;


    protected $storageBoxRepository;

    public function __construct(
        StorageBoxRepository $storageBoxRepository
    ) {
        $this->storageBoxRepository = $storageBoxRepository;
    }

    public function setStorageBox(string $storageBox)
    {
        $this->storageBox = $storageBox;
        return $this;
    }

    public function exec()
    {


        $prefix = substr($this->storageBox, 0, 1);
        $storageZone = config('storageBoxZone.storage');
        $unLimit = (array_values($storageZone['unLimit']));
        $storageBox = $this->storageBoxRepository->search([
            'barcode' => $this->storageBox
        ])->first();


        if (in_array($prefix, $unLimit) && is_null($storageBox)) {
            if (strlen($this->storageBox) !== 9) {
                throw ValidationException::withMessages(['storageBox' => '箱號格式錯誤要9碼 (' . $this->storageBox . ')。']);
            }
            try {
                DB::beginTransaction();
                $this->storageBoxRepository->create([
                    'prefix' => $prefix,
                    'barcode' => $this->storageBox,
                    'factory_id' => 1
                ]);

                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                throw ValidationException::withMessages(['box' => $ex->getMessage()]);
            }
        } else if (in_array($prefix, $unLimit) && !is_null($storageBox)) {

            throw ValidationException::withMessages(['storageBox' => '此貨箱條碼已使用 (' . $this->storageBox . ')。']);
        } else if (is_null($storageBox)) {

            throw ValidationException::withMessages(['storageBox' => '無此貨箱條碼 (' . $this->storageBox . ')。']);
        }

        return [
            'message' => 'ok'
        ];
    }
}
