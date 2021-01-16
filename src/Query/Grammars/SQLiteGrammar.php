<?php


namespace AngelSourceLabs\LaravelExpressions\Query\Grammars;

class SQLiteGrammar extends \Illuminate\Database\Query\Grammars\SQLiteGrammar
{
    use HasExpressionParameters;

    protected $driver = 'sqlite';
}
