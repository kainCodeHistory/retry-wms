<?php

namespace App\Repositories;

use App\Models\Model;

abstract class BaseRepository
{
    /** @var Model $model */
    protected $model;

    public function create(array $payload)
    {
        return $this->model::create($payload);
    }

    public function createMany(array $items)
    {
        return $this->model::insert($items);
    }

    public function findOrFail(int $id, array $relations = [])
    {
        if (count($relations) > 0) {
            return $this->model::with($relations)->findOrFail($id);
        }
        return $this->model::findOrFail($id);
    }

    public function firstOrFail(array $args)
    {
        $query = $this->model::query();
        foreach ($args as $key => $value) {
            $query->where($key, $value);
        }

        return $query->firstOrFail();
    }

    public function search (array $args = [])
    {
        $query = $this->model::query();

        foreach ($args as $key => $value) {
            $query->where($key, $value);
        }

        return $query->get();
    }

    public function update(int $id, array $payload)
    {
        return $this->model::where('id', $id)->update($payload);
    }

    public function updateMany(array $ids, array $payload)
    {
        return $this->model::whereIn('id', $ids)->update($payload);
    }

    public function delete(int $id)
    {
        return $this->model::destroy($id);
    }

    public function deleteMany(array $ids)
    {
        return $this->model::destroy($ids);
    }
}
