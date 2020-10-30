<?php

namespace Artificerkal\LaravelEloquentLikeCaching;

use Artificerkal\LaravelEloquentLikeCaching\Connector\Connector;
use Artificerkal\LaravelEloquentLikeCaching\Connector\QueueConnector;
use Illuminate\Support\Traits\ForwardsCalls;
use Jenssegers\Model\Model as BaseModel;

abstract class Model extends BaseModel
{
    use ForwardsCalls;

    protected $keyPrefix;

    /**
     * The primary key associated with the cache set.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $struct = 'stack';

    protected $structMap = [
        'stack' => Connector::class,
        'queue' => QueueConnector::class,
    ];

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->cacheConnector(), $method, $parameters);
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * Get the Connector that will manage this model being stored in a key-value storage system
     *
     * @return \Artificerkal\LaravelEloquentLikeCaching\Connector\Connector
     */
    public function cacheConnector()
    {
        $connectorClass = $this->structMap[$this->struct];
        return new $connectorClass($this);
    }

    public function getKeyPrefix()
    {
        return ($this->keyPrefix ?? static::class) . ':';
    }

    public function getPrimaryKey()
    {
        $primaryKey = $this->primaryKey;
        return $this->$primaryKey;
    }
}
