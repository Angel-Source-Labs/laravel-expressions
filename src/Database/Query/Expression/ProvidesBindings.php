<?php

namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;

/**
 * Default implementation trait that is used by ExpressionWithBindings and can be used by classes implementing HasBindings
 *
 * Implements a late-binding mechanism, so that if callables are passed as bindings, they are not resolved until the bindings are resolved.
 */
trait ProvidesBindings
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