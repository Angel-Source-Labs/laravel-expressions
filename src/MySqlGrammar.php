<?php


namespace AngelSourceLabs\LaravelExpressions;


class MySqlGrammar extends \Illuminate\Database\Query\Grammars\MySqlGrammar
{
    public function parameter($value)
    {
        return $value instanceof HasExpression ? $value->getExpression()->getValue() : parent::parameter($value);
    }
}