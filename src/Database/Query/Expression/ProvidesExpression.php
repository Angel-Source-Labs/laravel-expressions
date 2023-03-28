<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;

use Illuminate\Database\Grammar;

/**
 * Default implementation trait that can be used by classes implementing IsExpression
 */
trait ProvidesExpression
{
    /**
     * The value of the expression.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Array of bindings for the expression.
     *
     * @var array
     */
    protected $bindings;

    /**
     * Create a new raw query expression.
     *
     * @param  mixed  $value
     * @param array $bindings
     * @return void
     */
    public function __construct($value, array $bindings = null)
    {
        $this->value = $value;
        $this->bindings = $bindings;
    }

    /**
     * Get the value of the expression.
     *
     * Laravel 6.x - 9.x ignore getValue parameter and default to null
     * Laravel 10.x ignores getValue parameter.  Grammar is resolved using the ExpressionGrammar
     *
     * @return mixed
     */
    public function getValue(Grammar $grammar = null)
    {
        return $this->value;
    }

    /**
     * Return getValue() as string
     * This function will typically be implemented as:
     * `return (string) $this->getValue();`
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }

    public function hasBindings() : bool
    {
        return !is_null($this->bindings) && count($this->bindings) > 0;
    }

    public function getBindings() : array
    {
        return array_map(function($binding) {
            return is_callable($binding) ? $binding() : $binding;
        }, $this->bindings);
    }
}
