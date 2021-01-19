<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

class SqlServerGrammar extends \Illuminate\Database\Query\Grammars\SqlServerGrammar
{
    use HasExpressionParameters;

    protected $driver = 'sqlsrv';
}
