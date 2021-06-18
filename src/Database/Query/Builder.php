<?php

namespace AngelSourceLabs\LaravelExpressions\Database\Query;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\HasBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpressionAdapter;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpressionHasBindingsAdapter;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Builder extends \Illuminate\Database\Query\Builder
{
    /**
     * The callbacks that should be invoked before the query is executed.
     *
     * @var array
     */
    public $beforeQueryCallbacksForExpressions = [];

    /**
     * array of references to expressions contained in query builder
     *
     * @var array
     */
    protected $expressions;

    public function __construct(ConnectionInterface $connection, \Illuminate\Database\Query\Grammars\Grammar $grammar = null, Processor $processor = null)
    {
        parent::__construct($connection, $grammar, $processor);
        $this->beforeQueryForExpressions(function (Builder $query) {
           $queryGrammar = $query->getGrammar();
           if (method_exists($queryGrammar, 'configureExpressionsWithGrammar'))
               $queryGrammar->configureExpressionsWithGrammar($query);
        });
    }

    public function isExpression($value)
    {
        return $value instanceof Expression || $value instanceof IsExpression;
    }

    public function configureExpressions()
    {
        $configureGrammar = method_exists($this->getGrammar(), 'configureExpressionsWithGrammar');

        foreach($this->expressions() as &$value)
        {
            $value = $this->wrapIsExpressionWithAdapter($value);
            if ($configureGrammar)
                $this->getGrammar()->configureExpressionsWithGrammar($value);
        }
    }

    public function wrapIsExpressionWithAdapter($expression)
    {
        if ( !($expression instanceof IsExpression) ) return $expression;
        if ($expression instanceof Expression) return $expression;

        if ($expression instanceof HasBindings) return new IsExpressionHasBindingsAdapter($expression);
        return new IsExpressionAdapter($expression);
    }

    /**
     * Returns collection of all expressions in query builder
     * method is memoized and can be reset by setting reset parameter to true
     *
     * @param false $reset set to true to regenerate the expression collection
     * @return array
     */
    public function expressions($reset = false)
    {
        if (isset($this->expressions) && !$reset) return $this->expressions;

        $expressions = [];
        $components = [];
        foreach (
            [
                'aggregate',
                'columns',
                'distinct',
                'from',
                'joins',
                'wheres',
                'groups',
                'havings',
                'orders',
                'limit',
                'offset',
                'unions',
                'unionLimit',
                'unionOffest',
                'unionOrders',
                'lock',
             ] as $component) $components[] = &$component;

        array_walk_recursive($components, function(&$value, $key) {
            if ($this->isExpression($value))
                $expressions[] = &$value;
        });

        return $this->expressions = $expressions;
    }

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
     * Add a binding to the query.
     *
     * @param  mixed  $value
     * @param  string  $type
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function addBinding($value, $type = 'where')
    {
        if ($value instanceof Expression || $value instanceof isExpression) return $this;

        return parent::addBinding($value, $type);
    }


    /**
     * Register a closure to be invoked before the query is executed.
     * This is a polyfill from Laravel 8.x for Laravel 6.x and 7.x.
     * This is used to configure expressions with grammar before compiling the query.
     *
     * @param  callable  $callback
     * @return \Illuminate\Database\Query\Builder
     */
    public function beforeQueryForExpressions(callable $callback)
    {
        $this->beforeQueryCallbacks[] = $callback;

        return $this;
    }

    /**
     * Invoke the "before query" modification callbacks.
     * This is a polyfill from Laravel 8.x for Laravel 6.x and 7.x.
     * This is used to configure expressions with grammar before compiling the query.
     *
     * @return void
     */
    public function applyBeforeQueryCallbacksForExpressions()
    {
        foreach ($this->beforeQueryCallbacks as $callback) {
            $callback($this);
        }

        $this->beforeQueryCallbacks = [];
    }

    public function toSql()
    {
        $this->applyBeforeQueryCallbacksForExpressions();
        return parent::toSql();
    }

    public function exists()
    {
        $this->applyBeforeQueryCallbacksForExpressions();
        return parent::exists();
    }

    public function insert(array $values)
    {
        $this->applyBeforeQueryCallbacksForExpressions();
        return parent::insert($values);
    }

    public function insertOrIgnore(array $values)
    {
        $this->applyBeforeQueryCallbacksForExpressions();
        return parent::insertOrIgnore($values);
    }

    public function insertGetId(array $values, $sequence = null)
    {
        $this->applyBeforeQueryCallbacksForExpressions();
        return parent::insertGetId($values, $sequence);
    }

    public function insertUsing(array $columns, $query)
    {
        $this->applyBeforeQueryCallbacksForExpressions();
        return parent::insertUsing($columns, $query);
    }

    public function update(array $values)
    {
        $this->applyBeforeQueryCallbacksForExpressions();
        return parent::update($values);
    }

    /*
     * Upsert is Laravel 8.x only.  Will throw exception if called in Laravel 6.x or 7.x.   
     * Exception is presumably better than silent fail.
     */
    public function upsert(array $values, $uniqueBy, $update = null)
    {
        $this->applyBeforeQueryCallbacksForExpressions();
        return parent::upsert($values, $uniqueBy, $update);
    }

    public function delete($id = null)
    {
        $this->applyBeforeQueryCallbacksForExpressions();
        return parent::delete($id);
    }

    public function truncate()
    {
        $this->applyBeforeQueryCallbacksForExpressions();
        parent::truncate();
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

        return parent::whereRaw($sql, $bindings, $boolean);
    }

    public function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        $bindings = ($sql instanceof HasBindings) ? array_merge($sql->getBindings(), $bindings) : $bindings;

        return parent::havingRaw($sql, $bindings, $boolean);
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
            $this->addBinding(($value->getBindings()), 'where');
        }

        return $this;
    }

    protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and')
    {
        parent::addDateBasedWhere($type, $column, $operator, $value, $boolean);

        if ($value instanceof HasBindings) {
            $this->addBinding($value->getBindings(), 'where');
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
