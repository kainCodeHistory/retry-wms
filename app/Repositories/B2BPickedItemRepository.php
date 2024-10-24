<?php

namespace App\Repositories;

use App\Models\B2BPickedItem;

class B2BPickedItemRepository extends BaseRepository
{
    protected $model = B2BPickedItem::class;

    public function getDetails($sku , $startTime , $endTime){
        return  $this->model::where('sku',$sku)->where('picked_date','>=',$startTime)->where('picked_date','<=',$endTime)->get();
    }
}
