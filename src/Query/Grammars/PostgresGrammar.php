<?php


namespace AngelSourceLabs\LaravelExpressions\Query\Grammars;

class PostgresGrammar extends \Illuminate\Database\Query\Grammars\PostgresGrammar
{
    use HasExpressionParameters;

    protected $driver = 'pgsql';
}
