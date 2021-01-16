<?php


namespace AngelSourceLabs\LaravelExpressions\Eloquent;

use AngelSourceLabs\LaravelExpressions\Query\Grammars\MySqlGrammar;
use AngelSourceLabs\LaravelExpressions\Query\Builder as QueryBuilder;

trait HasExpressionAttributes
{
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection,
            new MySqlGrammar(),
            $connection->getPostProcessor()
        );
    }
}