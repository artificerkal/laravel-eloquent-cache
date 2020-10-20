<?php

namespace Artificerkal\LaravelEloquentCache\Connector;

use Artificerkal\LaravelEloquentCache\Contracts\Connector\Connector as ConnectorContract;
use Artificerkal\LaravelEloquentCache\Model;
use Illuminate\Support\Facades\Cache;

class Connector implements ConnectorContract
{
    public $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function find($id)
    {
        return Cache::get($this->model->getKeyPrefix() . $id);
    }

    public function save()
    {
        Cache::put($this->model->getKeyPrefix() . $this->model->id, $this->model);
        return $this->model;
    }

    public function delete()
    {
        Cache::forget($this->model->getKeyPrefix() . $this->model->id);
        return true;
    }

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @param  string  $id
     * @param  array  $values
     * @return \App\Models\Redis\Model
     */
    public function updateOrCreate($id, array $values = [])
    {
        return tap(Cache::get($this->model->getKeyPrefix() . $id) ?? $this->model->fill(['id' => $id]), function ($instance) use ($values) {
            $instance->fill($values)->save();
        });
    }
}
