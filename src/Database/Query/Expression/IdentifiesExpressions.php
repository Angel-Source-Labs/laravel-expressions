<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;


use Illuminate\Database\Query\Expression;

trait IdentifiesExpressions
{
    /**
     * Determine if the given value is a raw expression.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function isExpression($value)
    {
        return $value instanceof Expression || $value instanceof isExpression;
    }

    /**
     * @param Expression | IsExpression $value
     */
    public function isExpressionWithGrammar($value)
    {
        return $this->isExpression($value) && $value->getValue() instanceof ExpressionGrammar;
    }
}