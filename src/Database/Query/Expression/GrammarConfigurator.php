<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;


use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;

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
     */
    public function configureExpression($expression)
    {
        if ($this->isExpressionWithGrammar($expression))
            $this->configureGrammar($expression->getValue());
    }

    /**
     * @param Grammar $grammar
     */
    public function configureGrammar($grammar)
    {
        if ($grammar instanceof Grammar)
            $grammar->connection($this->connection);
    }
}