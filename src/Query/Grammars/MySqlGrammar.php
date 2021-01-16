<?php


namespace AngelSourceLabs\LaravelExpressions\Query\Grammars;

class MySqlGrammar extends \Illuminate\Database\Query\Grammars\MySqlGrammar
{
    use HasExpressionParameters;

    protected $driver = 'mysql';
}
