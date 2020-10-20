<?php

namespace Artificerkal\LaravelEloquentCache\Contracts\Connector;

use Artificerkal\LaravelEloquentCache\Model;
use phpDocumentor\Reflection\Types\Boolean;

interface Connector
{
    public function __construct(Model $model);
    public function find($id);
    public function save();
    public function delete();
    public function updateOrCreate($id, array $values = []);
}
