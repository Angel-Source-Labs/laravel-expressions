<?php

namespace AngelSourceLabs\LaravelExpressions\Expression;

use Illuminate\Database\Query\Expression;

class ExpressionWithBindings extends Expression implements HasBindings
{
    private $bindings;

    public function __construct($value, array $bindings)
    {
        $this->value = $value;
        $this->bindings = $bindings;
        parent::__construct($value);
    }

    public function getBindings() : array
    {
        return $this->bindings;
    }
}