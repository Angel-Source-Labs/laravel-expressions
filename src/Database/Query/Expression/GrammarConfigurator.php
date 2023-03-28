<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;


use AngelSourceLabs\LaravelExpressionGrammar\ExpressionGrammar;
use Illuminate\Database\Connection;

class GrammarConfigurator
{
    use IdentifiesExpressions;

    /**
     * @var Connection $connection;
     */
    protected $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Expression | IsExpression $expression
     *
     * Laravel 6.x - 9.x ignore the parameter to $expression->getValue($this->connection->getQueryGrammar()).
     * Laravel 10.x requires the parameter to $expression->getValue($this->connection->getQueryGrammar()).

     *
     */
    public function configureExpression($expression)
    {
        if ($this->isExpression($expression))
            $this->configureGrammar($expression->getValue($this->connection->getQueryGrammar()));
    }

    /**
     * @param ExpressionGrammar $grammar
     */
    public function configureGrammar($grammar)
    {
        if ($grammar instanceof ExpressionGrammar)
            $grammar->connection($this->connection);
    }
}