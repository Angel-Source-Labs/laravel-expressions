<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

class SqlServerGrammar extends \Illuminate\Database\Query\Grammars\SqlServerGrammar
{
    use HasExpressionsWithGrammar;

    protected $driver = 'sqlsrv';
}
