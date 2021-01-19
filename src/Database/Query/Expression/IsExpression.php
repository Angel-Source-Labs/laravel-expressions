<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;


interface IsExpression
{
    /**
     * Get the value of the expression.
     *
     * @return mixed
     */
    public function getValue();
}