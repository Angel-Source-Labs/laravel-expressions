<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;


use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;

class GrammarConfigurator
{
    use UsesExpressions;

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
            $expression->getValue()->connection($this->connection);
    }
}