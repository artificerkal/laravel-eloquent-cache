<?php

namespace Artificerkal\LaravelEloquentLikeCaching\Connector;

use Artificerkal\LaravelEloquentLikeCaching\Contracts\Connector\Connector as ConnectorContract;
use Artificerkal\LaravelEloquentLikeCaching\Model;
use Illuminate\Support\Facades\Cache;

class Connector implements ConnectorContract
{
    public $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get the model index
     *
     * @return \Illuminate\Support\Collection
     */
    protected function index()
    {
        return collect(Cache::get($this->model->getKeyPrefix() . '_index'));
    }

    /**
     * Append an item to the model index
     */
    protected function addToIndex($id)
    {
        if (!($index = $this->index())->contains($id)) {
            Cache::put(
                $this->model->getKeyPrefix() . '_index',
                $index->merge($id)->toArray()
            );
        }
    }

    protected function removeFromIndex($id)
    {
        Cache::put(
            $this->model->getKeyPrefix() . '_index',
            $this->index()->filter(function ($value) use ($id) {
                return $value != $id;
            })->toArray()
        );
    }

    public function all()
    {
        $models = [];

        foreach ($this->index() as $id) {
            $models[] = $this->find($id);
        }

        return \collect($models);
    }

    public function find($id)
    {
        return Cache::get($this->model->getKeyPrefix() . $id);
    }

    public function save()
    {
        Cache::put($this->model->getKeyPrefix() . $this->model->getPrimaryKey(), $this->model);
        $this->addToIndex($this->model->getPrimaryKey());
        return $this->model;
    }

    public function delete($id = \null)
    {
        $modelKey = $id ?? $this->model->getPrimaryKey();
        Cache::forget($this->model->getKeyPrefix() . $modelKey);
        $this->removeFromIndex($this->model->getPrimaryKey());
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
