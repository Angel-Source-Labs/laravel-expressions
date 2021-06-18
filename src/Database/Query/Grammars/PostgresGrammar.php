<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

class PostgresGrammar extends \Illuminate\Database\Query\Grammars\PostgresGrammar
{
    use HasExpressionsWithGrammar;

    protected $driver = 'pgsql';
}
