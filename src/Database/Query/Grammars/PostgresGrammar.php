<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\UsesExpressions;

class PostgresGrammar extends \Illuminate\Database\Query\Grammars\PostgresGrammar
{
    use UsesExpressions, HasParameterExpressionsWithGrammar;

    protected $driver = 'pgsql';
}
