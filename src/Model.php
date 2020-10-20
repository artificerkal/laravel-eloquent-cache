<?php

namespace Artificerkal\LaravelEloquentCache;

use Artificerkal\LaravelEloquentCache\Connector\Connector;
use Artificerkal\LaravelEloquentCache\Connector\QueueConnector;
use Illuminate\Support\Traits\ForwardsCalls;
use Jenssegers\Model\Model as BaseModel;

abstract class Model extends BaseModel
{
    use ForwardsCalls;

    protected $keyPrefix;

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
     * @return \Artificerkal\LaravelEloquentCache\Connector\Connector
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
}
