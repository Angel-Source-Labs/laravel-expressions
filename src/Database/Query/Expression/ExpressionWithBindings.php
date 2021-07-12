<?php

namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class ExpressionWithBindings extends Expression implements IsExpression, HasBindings
{
    protected $bindings;

    public function __construct($value, array $bindings)
    {
        $this->value = $value;
        $this->bindings = $bindings;
        parent::__construct($value);
    }

    public function getBindings() : array
    {
        return array_map(function($binding) {
            return is_callable($binding) ? $binding() : $binding;
        }, $this->bindings);
    }
}