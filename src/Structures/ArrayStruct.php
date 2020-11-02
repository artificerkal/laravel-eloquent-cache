<?php

namespace Artificerkal\LaravelEloquentLikeCaching\Structures;

use Artificerkal\LaravelEloquentLikeCaching\Model;
use Exception;
use Illuminate\Support\Facades\Cache;

class ArrayStruct
{
    protected $model;

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

    /**
     * Remove an item from the model index
     */
    protected function removeFromIndex($id)
    {
        Cache::put(
            $this->model->getKeyPrefix() . '_index',
            $this->index()->filter(function ($value) use ($id) {
                return $value != $id;
            })->toArray()
        );
    }

    /**
     * Retrieve all of the models
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        $models = [];

        foreach ($this->index() as $id) {
            $models[] = $this->find($id);
        }

        return \collect($models);
    }

    /**
     * Retrieve the model that matcfhes the given key
     *
     * @return \Artificerkal\LaravelEloquentLikeCaching\Model
     */
    public function find($id)
    {
        return Cache::get($this->model->getKeyPrefix() . $id);
    }

    /**
     * Retrieve the model that matcfhes the given key
     *
     * @return bool
     */
    public function save()
    {

        $saved = Cache::put($this->model->getKeyPrefix() . $this->model->getKey(), $this->model);

        if ($saved) {
            $this->addToIndex($this->model->getKey());
        }

        return $saved;
    }

    /**
     * Delete this model or the one with the given key
     *
     * @return bool
     */
    public function delete()
    {
        if (is_null($this->model->getKeyName())) {
            throw new Exception('No primary key defined on model.');
        }

        $deleted = Cache::forget($this->model->getKeyPrefix() . $this->model->getKey());

        if ($deleted) {
            $this->removeFromIndex($this->model->getKey());
        }

        return $deleted;
    }

    /**
     * Instatiate a new model
     *
     * @return \Artificerkal\LaravelEloquentLikeCaching\Model
     */
    public function make(array $attributes = [])
    {
        return $this->model->fill($attributes);
    }

    /**
     * Instaniate a new model and persist it to the cache
     *
     * @return \Artificerkal\LaravelEloquentLikeCaching\Model
     */
    public function create(array $attributes = [])
    {
        return \tap($this->model->make($attributes), function (&$model) {
            $model->save();
        });
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
