<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;

use AngelSourceLabs\LaravelExpressionGrammar\ExpressionGrammar;
use Illuminate\Database\Query\Expression as BaseExpression;

trait IdentifiesExpressions
{
    /**
     * Determine if the given value is a raw base expression that is not an instance of isExpression.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function isBaseExpression($value)
    {
        return $value instanceof BaseExpression && !($value instanceof isExpression);
    }

    /**
     * Determine if the given value is a raw expression.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function isExpression($value)
    {
        return $value instanceof BaseExpression || $value instanceof isExpression;
    }

    public function isExpressionWithBindings($value) : bool
    {
        return $value instanceof IsExpression && $value->hasBindings();
    }

    /**
     * @param BaseExpression | IsExpression $value
     */
    public function isExpressionWithGrammar($value, $grammar = null)
    {
        return $this->isExpression($value) && $value->getValue($grammar ?? $this) instanceof ExpressionGrammar;
    }
}