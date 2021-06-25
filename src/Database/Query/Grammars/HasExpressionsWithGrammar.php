<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

trait HasExpressionsWithGrammar
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
        return $this->isExpression($value) && $value->getValue() instanceof Grammar;
    }

    /**
     * @param Expression | IsExpression $expression
     */
    public function configureExpressionWithGrammar($expression)
    {
        if ($this->isExpressionWithGrammar($expression))
            $expression->getValue()->driver($this->driver);
    }

    public function parameter($value)
    {
        $this->configureExpressionWithGrammar($value);

        return parent::parameter($value);
    }

}
