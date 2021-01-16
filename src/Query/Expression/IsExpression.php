<?php


namespace AngelSourceLabs\LaravelExpressions\Query\Expression;


interface isExpression
{
    /**
     * Get the value of the expression.
     *
     * @return mixed
     */
    public function getValue();
}