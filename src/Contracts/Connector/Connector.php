<?php

namespace Artificerkal\LaravelEloquentLikeCaching\Contracts\Connector;

use Artificerkal\LaravelEloquentLikeCaching\Model;
use phpDocumentor\Reflection\Types\Boolean;

interface Connector
{
    public function __construct(Model $model);
    public function find($id);
    public function save();
    public function delete($id = \null);
    public function updateOrCreate($id, array $values = []);
}
