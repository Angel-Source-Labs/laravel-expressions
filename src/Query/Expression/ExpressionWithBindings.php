<?php

namespace AngelSourceLabs\LaravelExpressions\Query\Expression;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class ExpressionWithBindings extends Expression implements isExpression, HasBindings
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