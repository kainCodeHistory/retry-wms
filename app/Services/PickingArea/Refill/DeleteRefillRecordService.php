<?php

namespace App\Services\PickingArea\Refill;

use App\Repositories\PickingArea\RefillRepository;
use App\Services\AppService;
use Illuminate\Validation\ValidationException;

class DeleteRefillRecordService extends AppService
{
    protected $recordId;
    protected $refillRepository;

    public function __construct(RefillRepository $refillRepository)
    {
        $this->refillRepository = $refillRepository;
    }

    public function setRecordId(int $recordId)
    {
        $this->recordId = $recordId;
        return $this;
    }

    public function exec()
    {
        $refill = $this->refillRepository->findOrFail($this->recordId);

        if ($refill->status !== 'pending' && $refill->status !== 'processing' ) {
            throw ValidationException::withMessages(['refill' => '此筆補料作業' . ($refill->status === 'processing' ? '正在進行中' : '已完成') . '。']);
        }else if ($refill->status == 'processing'){
            $this->refillRepository->update($refill->id,['status'=>'aborted']);
            throw ValidationException::withMessages(['refill' => '此筆補料作業已取消。']);
        }else{
            $this->refillRepository->delete($refill->id);
        }

        return [
            'id' => $refill->id
        ];
    }
}
