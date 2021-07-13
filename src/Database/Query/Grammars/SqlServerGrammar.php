<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\UsesExpressions;

class SqlServerGrammar extends \Illuminate\Database\Query\Grammars\SqlServerGrammar
{
    use UsesExpressions, HasParameterExpressionsWithGrammar;

    protected $driver = 'sqlsrv';
}
