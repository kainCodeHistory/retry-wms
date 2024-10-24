<?php

namespace App\Repositories;

use App\Models\Material;

class MaterialRepository extends BaseRepository
{
    protected $model = Material::class;

    public function getMaterialsBySku(array $skus)
    {
        return Material::whereIn('sku', $skus)->get();
    }

    public function getMaterialByEanOrSku(string $keyword)
    {
        return Material::where('ean', $keyword)->orWhere('sku', $keyword)->get();
    }

    public function findMaterialBySku(string $sku)
    {
        return Material::where('sku', $sku)->get();
    }

    public function getEmptyCheckSku()
    {
        return Material::where('sku','not like','%-%')->where('check_sku','')->get();
    }

    public function getCheckSku(string $keyword)
    {
        return Material::where('sku','like','%-'.$keyword)->where('sku','not like','%SP%')->get();
    }
}
