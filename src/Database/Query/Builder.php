<?php

namespace AngelSourceLabs\LaravelExpressions\Database\Query;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\GrammarConfigurator;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\HasBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpressionAdapter;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpressionHasBindingsAdapter;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\HasParameterExpressionsWithGrammar;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Processors\Processor;

class Builder extends \Illuminate\Database\Query\Builder
{
    /**
     * The callbacks that should be invoked before the query is executed.
     * This is a polyfill of Laravel 8 functionality to Laravel 6 and 7
     *
     * @var array
     */
    public $beforeQueryCallbacksPolyfill = [];

    /**
     * array of references to expressions contained in query builder
     *
     * @var array
     */
    protected $expressions = null;

    /**
     * @var GrammarConfigurator
     */
    protected $grammarConfigurator;

    /**
     * Register a closure to be invoked before the query is executed.
     * This is a polyfill from Laravel 8.x for Laravel 6.x and 7.x.
     * This is used to configure expressions with grammar before compiling the query.
     *
     * @param callable $callback
     * @return \Illuminate\Database\Query\Builder
     */
    public function beforeQueryPolyfill(callable $callback)
    {
        $this->beforeQueryCallbacksPolyfill[] = $callback;

        return $this;
    }

    /**
     * Invoke the "before query" modification callbacks.
     * This is a polyfill from Laravel 8.x for Laravel 6.x and 7.x.
     * This is used to configure expressions with grammar before compiling the query.
     *
     * @return void
     */
    public function applyBeforeQueryCallbacksPolyfill()
    {
        foreach ($this->beforeQueryCallbacksPolyfill as $callback) {
            $callback($this);
        }

        $this->beforeQueryCallbacksPolyfill = [];
    }

    public function __construct(ConnectionInterface $connection, \Illuminate\Database\Query\Grammars\Grammar $grammar = null, Processor $processor = null)
    {
        parent::__construct($connection, $grammar, $processor);

        $this->grammarConfigurator = new GrammarConfigurator($connection);
        if ($this->queryGrammarHasExpressionsWithGrammar())
            $this->getGrammar()->setGrammarConfigurator($this->grammarConfigurator);

        $this->beforeQueryPolyfill(function (Builder $query) {
            $this->configureExpressions();
        });
    }

    public function isExpression($value)
    {
        return $value instanceof Expression || $value instanceof IsExpression;
    }

    protected function configureExpressions()
    {
        foreach ($this->expressions() as &$value) {
            $value = $this->unwrapRawDoubleExpression($value);
            $value = $this->wrapIsExpression($value);
            $this->grammarConfigurator->configureExpression($value);
        }
    }

    /**
     * SelectRaw and GroupByRaw always wraps the raw sql in an expression, even if it is already an expression.
     * This unwraps the double expression to a single expression.
     *
     * @param $expression
     * @return mixed
     */
    protected function unwrapRawDoubleExpression($expression)
    {
        if (
            get_class($expression) == Expression::class &&
            $this->isExpression($expression->getValue())
        )
        {
            return $expression->getValue();
        }

        return $expression;
    }

    protected function queryGrammarHasExpressionsWithGrammar()
    {
        return $this->getGrammar() !== null && in_array(HasParameterExpressionsWithGrammar::class, class_uses_recursive(get_class($this->getGrammar())));
    }

    public function wrapIsExpression($expression)
    {
        if ( !($expression instanceof IsExpression) ) return $expression;

        if ( $expression instanceof Expression ) return $expression;

        if ( $expression instanceof HasBindings )
            return new IsExpressionHasBindingsAdapter($expression);

        return new IsExpressionAdapter($expression);
    }

    /**
     * Returns array of all expressions that have been added to the query builder
     * method is memoized and can be reset by setting `reset` parameter to true
     * values are by reference to value in original location in query builder
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
             ] as $component) {
                $components[] = &$this->{$component};
        }

        array_walk_recursive($components, function(&$value, $key) use (&$expressions) {
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

    // TODO is this no longer needed?  Who called it?  What was this previously fixing?
    //  Looks like this was previously called by builder->where() from an `if (! $value instanceof Expression)` block
    //  That instance would be resolved by IsExpression wrapping
    //  - groupByRaw: It is also called by groupByRaw. look at groupByRaw test cases
    //  - havingRaw
    //  - whereIn
    //  - orderByRaw
    //  - selectRaw
    //  - join (not yet implemented)
    //  -

    /**
     * Add a binding to the query.
     *
     * @param  mixed  $value
     * @param  string  $type
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
//    public function addBinding($value, $type = 'where')
//    {
//        if ($this->isExpression($value)) return $this;
//
//        return parent::addBinding($value, $type);
//    }

    public function toSql()
    {
        $this->applyBeforeQueryCallbacksPolyfill();
        return parent::toSql();
    }

    /**
     * Exists does not have expressions to process.
     * This method override exists to provide the before query callbacks polyfill behavior for Laravel 6.x and 7.x
     */
    public function exists()
    {
        $this->applyBeforeQueryCallbacksPolyfill();
        return parent::exists();
    }

    public function insert(array $values)
    {
        $this->applyBeforeQueryCallbacksPolyfill();
        return parent::insert($values);
    }

    public function insertOrIgnore(array $values)
    {
        $this->applyBeforeQueryCallbacksPolyfill();
        return parent::insertOrIgnore($values);
    }

    public function insertGetId(array $values, $sequence = null)
    {
        $this->applyBeforeQueryCallbacksPolyfill();
        return parent::insertGetId($values, $sequence);
    }

    public function insertUsing(array $columns, $query)
    {
        $this->applyBeforeQueryCallbacksPolyfill();
        return parent::insertUsing($columns, $query);
    }

    public function update(array $values)
    {
        $this->applyBeforeQueryCallbacksPolyfill();
        return parent::update($values);
    }

    /*
     * Upsert is Laravel 8.x only.  Will throw exception if called in Laravel 6.x or 7.x.   
     * Exception is presumably better than silent fail.
     */
    public function upsert(array $values, $uniqueBy, $update = null)
    {
        $this->applyBeforeQueryCallbacksPolyfill();
        return parent::upsert($values, $uniqueBy, $update);
    }

    public function delete($id = null)
    {
        $this->applyBeforeQueryCallbacksPolyfill();
        return parent::delete($id);
    }

    /**
     * Truncate does not have expressions to process.
     * This method override exists to provide the before query callbacks polyfill behavior for Laravel 6.x and 7.x
     */
    public function truncate()
    {
        $this->applyBeforeQueryCallbacksPolyfill();
        parent::truncate();
    }

    /**
     * @param HasBindings | mixed $expression
     * @param array $bindings
     * @return array
     */
    protected function mergeExpressionBindings($expression, array $bindings)
    {
        return ($expression instanceof HasBindings) ? array_merge($expression->getBindings(), $bindings) : $bindings;
    }

    /**
     * selectRaw
     *
     * Call parent with merged bindings
     *
     * Notes:
     * - Grammar is configured later by toSql() calling configureExpressions
     * - Expressions are evaluated later when grammar->compileSelect calls columnize => wrap which evaluates the expression by getValue
     *
     * @param string | Expression | IsExpression $expression
     * @param array $bindings
     * @return Builder|void
     */
    public function selectRaw($expression, array $bindings = [])
    {
        return parent::selectRaw($expression, $this->mergeExpressionBindings($expression, $bindings));
    }

    /**
     * whereRaw
     *
     * Call parent with merged bindings
     *
     * Notes:
     * - Grammar is configured later by toSql() calling configureExpressions
     * - Expressions are evaluated later when grammar->compileWheres calls compileWheresToArray, which calls expression->__toString via concatenation
     *
     * @param string | Expression | IsExpression $sql
     * @param array $bindings
     * @param string $boolean
     * @return Builder
     */
    public function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        return parent::whereRaw($sql, $this->mergeExpressionBindings($sql, $bindings), $boolean);
    }

    /**
     * havingRaw
     *
     * Call parent with merged bindings
     *
     * Notes:
     * - Grammar is configured later by toSql() calling configureExpressions
     * - Expressions are evaluated later when grammar->compileOrders calls compileOrdersToArray, which calls expression->__toString via concatenation
     *
     * @param string | Expression | IsExpression $sql
     * @param array $bindings
     * @return Builder
     */
    public function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        return parent::havingRaw($sql, $this->mergeExpressionBindings($sql, $bindings), $boolean);
    }

    /**
     * orderByRaw
     *
     * Call parent with merged bindings
     *
     * Notes:
     * - Grammar is configured later by toSql() calling configureExpressions
     * - Expressions are evaluated later when grammar->compileOrders calls compileOrdersToArray
     *
     * @param string | Expression | IsExpression $sql
     * @param array $bindings
     * @return Builder
     */
    public function orderByRaw($sql, $bindings = [])
    {
        return parent::orderByRaw($sql, $this->mergeExpressionBindings($sql, $bindings));
    }

    /**
     * groupByRaw
     *
     * Call parent with merged bindings
     *
     * Notes:
     * - Grammar is configured later by toSql() calling configureExpressions
     * - Expressions are evaluated later when grammar->compileGroups calls columnize => wrap which evaluates the expression by getValue
     *
     * @param string | Expression | IsExpression $sql
     * @param array $bindings
     * @return Builder
     */
    public function groupByRaw($sql, array $bindings = [])
    {
        return parent::groupByRaw($sql, $this->mergeExpressionBindings($sql, $bindings));
    }

    /**
     * where
     *
     * 1. Resolve optional operator parameter
     * 2. Wrap IsExpression with an adapter to satisfy the parent method 'if (! $value instanceof Expression)' check
     * 3. Call parent
     * 4. Add bindings from Expression
     *
     * This is a partial implementation of where that handles most of the author's current use cases.
     * Remaining cases can be added in the future as needed.
     *
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
        // resolve optional operator parameter, which has the implicit value of '=' when not specified
        // this must be done before the value can be wrapped.
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        // Wrap IsExpression with an adapter to satisfy the parent method 'if (! $value instanceof Expression)' check
        $value = $this->wrapIsExpression($value);

        // Call parent
        parent::where($column, $operator, $value, $boolean);

        // Add bindings from Expression
        if ($value instanceof HasBindings) {
            $this->addBinding(($value->getBindings()), 'where');
        }

        return $this;
    }

    /**
     * whereDay
     *
     * 1. Resolve optional operator parameter
     * 2. Wrap IsExpression with an adapter to satisfy the parent method 'if (! $value instanceof Expression)' check
     * 3. Call parent
     * 4. Expression bindings are resolved afterwards by parent method calling addDateBasedWhere()
     *
     * @param string $column
     * @param string $operator
     * @param null $value
     * @param string $boolean
     * @return Builder
     */
    public function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        // resolve optional operator parameter, which has the implicit value of '=' when not specified
        // this must be done before the value can be wrapped.
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        // Wrap IsExpression with an adapter to satisfy the parent method 'if (! $value instanceof Expression)' check
        $value = $this->wrapIsExpression($value);

        // Call parent
        return parent::whereDay($column, $operator, $value, $boolean);

        // Expression bindings are resolved afterwards by parent method calling addDateBasedWhere()
    }

    /**
     * whereMonth
     *
     * 1. Resolve optional operator parameter
     * 2. Wrap IsExpression with an adapter to satisfy the parent method 'if (! $value instanceof Expression)' check
     * 3. Call parent
     * 4. Expression bindings are resolved afterwards by parent method calling addDateBasedWhere()
     *
     * @param string $column
     * @param string $operator
     * @param null $value
     * @param string $boolean
     * @return Builder
     */
    public function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        // resolve optional operator parameter, which has the implicit value of '=' when not specified
        // this must be done before the value can be wrapped.
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        // Wrap IsExpression with an adapter to satisfy the parent method 'if (! $value instanceof Expression)' check
        $value = $this->wrapIsExpression($value);

        // Call parent
        return parent::whereMonth($column, $operator, $value, $boolean);

        // Expression bindings are resolved afterwards by parent method calling addDateBasedWhere()
    }

    /*
     * The following where-date-based methods do not need overloads
     *
     * whereDate
     * whereTime
     * whereYear
     *
     * 1. These methods do not have an 'if (! $value instanceof Expression)' check that requires expression wrapping
     * 2. Expression bindings are resolved afterwards by addDateBasedWhere()
     *
     * Tests exist for these methods in BuilderWhereDateBasedTest to verify proper function
     *
     */

    /**
     * addDateBasedWhere
     *
     * 1. Wrap IsExpression with an adapter to satisfy the parent method 'if (! $value instanceof Expression)' check
     * 2. Call parent
     * 3. Add bindings from expression
     *
     * @param string $type
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return $this|Builder
     */
    protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and')
    {
        $value = $this->wrapIsExpression($value);

        parent::addDateBasedWhere($type, $column, $operator, $value, $boolean);

        if ($value instanceof HasBindings) {
            $this->addBinding($value->getBindings(), 'where');
        }

        return $this;
    }

    // TODO - no tests currently call this.  So how do I know that I needed it?
    /**
     * Having
     *
     * 1. Resolve optional operator parameter
     * 2. Wrap IsExpression with an adapter to satisfy the parent method 'if (! $value instanceof Expression)' check
     * 3. Call parent
     * 4. Add bindings from expression
     *
     * @param string $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return Builder
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        // resolve optional operator parameter, which has the implicit value of '=' when not specified
        // this must be done before the value can be wrapped.
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->wrapIsExpression($value);

        // Call parent
        parent::having($column, $operator, $value, $boolean);

        // Add bindings from Expression
        if ($value instanceof HasBindings) {
            $this->addBinding($value->getBindings(), 'where');
        }

        return $this;

    }


    /**
     * Select query
     *
     * 1. Call parent
     * 2. Add bindings from Expression
     * 3. Expression is evaluated later in toSql()
     *
     * example query:
     *  select price * ? as price_with_tax from `orders`
     * example expression:
     *  price * ? as price_with_tax
     *
     * This example query multiples the price column by a tax rate specified in the binding and returns the column as
     * `price_with_tax`
     *
     * @param array|mixed|string[] $columns
     * @return $this|Builder
     */
    public function select($columns = ['*'])
    {
        // TODO columns can be expressions so could configure them here.
        //  Currently the expressions are configured in toSql before the query compiles
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
