<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;

use Illuminate\Database\Grammar;

class IsExpressionAdapter extends Expression
{
    protected $expression;

    /**
     * IsExpressionAdapter constructor.
     * @param IsExpression $expression
     */
    public function __construct(IsExpression $expression)
    {
        $this->expression = $expression;
    }

    public function getValue(Grammar $grammar = null)
    {
        return $this->expression->getValue($grammar);
    }

    public function __toString()
    {
        return $this->expression->__toString();
    }

    public function hasBindings(): bool
    {
        return $this->expression->hasBindings();
    }

    public function getBindings(): array
    {
        return $this->expression->getBindings();
    }
}
