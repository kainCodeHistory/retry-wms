<?php

namespace App\Repositories;

use App\Models\Material;

class MaterialRepository extends BaseRepository
{
    protected $model = Material::class;

    public function getMaterialByEanOrSku(string $keyword)
    {
        return Material::where('ean', $keyword)->orWhere('sku', $keyword)->get();
    }
}
