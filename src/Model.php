<?php

namespace Artificerkal\LaravelEloquentLikeCaching;

use Artificerkal\LaravelEloquentLikeCaching\Connector\ArrayConnector;
use Artificerkal\LaravelEloquentLikeCaching\Connector\QueueConnector;
use Artificerkal\LaravelEloquentLikeCaching\Connector\StackConnector;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use Jenssegers\Model\Model as BaseModel;

abstract class Model extends BaseModel
{
    use ForwardsCalls;


    /**
     * The attribute which will be used as the model key
     *
     * @var string
     */
    protected $keyName = 'id';

    /**
     * The model key prefix which is used for all models of this type. Defaults to the model class
     *
     * @var string
     */
    protected $keyPrefix;

    /**
     * The data structure that this group of models should be stored as
     *
     * @var string
     */
    protected $struct = 'array';

    protected $structMap = [
        'array' => ArrayConnector::class,
        // 'queue' => QueueConnector::class,
        // 'stack' => StackConnector::class,
    ];

    /**
     * Get the Connector that will manage this model being stored in a key-value storage system
     *
     * @return \Artificerkal\LaravelEloquentLikeCaching\Contracts\Connector
     */
    public function cacheStructure()
    {
        $structureClass = $this->structMap[$this->struct];
        return new $structureClass($this);
    }

    public function getKeyPrefix()
    {
        return ($this->keyPrefix ?? static::class) . ':';
    }

    public function getKeyName()
    {
        return $this->primaryKey;
    }

    public function getKey()
    {
        $primaryKey = $this->primaryKey;
        return $this->$primaryKey;
    }

    /**
     * Save this model
     *
     * @return bool
     */
    public function save()
    {
        return $this->cacheStructure()->save();
    }

    /**
     * Delete this model
     *
     * @return bool
     */
    public function delete()
    {
        return $this->cacheStructure()->delete();
    }

    /**
     * Destroy the models for the given IDs.
     *
     * @param  \Illuminate\Support\Collection|array|int|string  $ids
     * @return int
     */
    public static function destroy($ids)
    {
        $count = 0;

        if ($ids instanceof Collection) {
            $ids = $ids->all();
        }

        $ids = is_array($ids) ? $ids : func_get_args();

        $instance = new static;

        foreach ($ids as $id) {
            if ($instance->find($id)->delete()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->cacheStructure(), $method, $parameters);
    }
}
