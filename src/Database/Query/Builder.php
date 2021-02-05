<?php

namespace AngelSourceLabs\LaravelExpressions\Database\Query;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\HasBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use Illuminate\Database\Query\Expression;

class Builder extends \Illuminate\Database\Query\Builder
{
    public function cleanBindings(array $bindings)
    {
        $unpackedBindings = [];
        foreach ($bindings as &$binding) {
            if ($binding instanceof HasBindings) {
                $unpackedBindings = array_merge($unpackedBindings, $binding->getBindings());
            }
            else {
                $unpackedBindings[] = $binding;
            }
        }

        return parent::cleanBindings($unpackedBindings);
    }

    /**
     * @param Expression | HasBindings $expression
     * @param array $bindings
     * @return Builder|void
     */
    public function selectRaw($expression, array $bindings = [])
    {
        $bindings = ($expression instanceof HasBindings) ? array_merge($expression->getBindings(), $bindings) : $bindings;

        return parent::selectRaw($expression, $bindings);
    }

    public function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        $bindings = ($sql instanceof HasBindings) ? array_merge($sql->getBindings(), $bindings) : $bindings;

        return parent::whereRaw($sql, $bindings);
    }

    public function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        $bindings = ($sql instanceof HasBindings) ? array_merge($sql->getBindings(), $bindings) : $bindings;

        return parent::havingRaw($sql, $bindings);
    }

    public function orderByRaw($sql, $bindings = [])
    {
        $bindings = ($sql instanceof HasBindings) ? array_merge($sql->getBindings(), $bindings) : $bindings;

        return parent::orderByRaw($sql, $bindings);
    }

    public function groupByRaw($sql, array $bindings = [])
    {
        $bindings = ($sql instanceof HasBindings) ? array_merge($sql->getBindings(), $bindings) : $bindings;

        return parent::groupByRaw($sql, $bindings);
    }

    /**
     * Handle value as expression with/without bindings for simple query where:
     * 1. column is a string representing name of a single column
     * 2. value is an expression with/without bindings
     *
     * Does not handle these cases:
     * 1. column is an array of columns
     * 2. column is a closure (nested where)
     * 3. column is a json column
     * 4. value is a closure (sub-select)
     *
     * @param array|\Closure|string $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this|Builder
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        // From parent class -
        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        //
        // Additional Note -
        // This follows orWhere, which also executes this statement before calling where
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        parent::where($column, $operator, $value, $boolean);

        if ($value instanceof HasBindings) {
            $this->addBinding(head($value->getBindings()), 'where');
        }

        return $this;
    }

    public function select($columns = ['*'])
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        parent::select($columns);

        foreach ($columns as $as => $column) {
            if ($column instanceof HasBindings) {
                $this->bindings['select'] = array_merge($this->bindings['select'], $column->getBindings());
            }
        }

        return $this;
    }
}
