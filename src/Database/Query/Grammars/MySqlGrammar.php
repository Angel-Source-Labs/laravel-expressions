<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

class MySqlGrammar extends \Illuminate\Database\Query\Grammars\MySqlGrammar
{
    use HasExpressionsWithGrammar;

    protected $driver = 'mysql';
}
