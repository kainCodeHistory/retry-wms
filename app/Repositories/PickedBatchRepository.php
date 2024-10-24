<?php

namespace App\Repositories;

use App\Models\PickedBatch;

class PickedBatchRepository extends BaseRepository
{
    protected $model = PickedBatch::class;

    public function setRedo(string $batchKey, int $flag)
    {
        $this->model::where('batch_key', '=', $batchKey)
            ->update([
                'redo' => $flag
            ]);
    }
}
