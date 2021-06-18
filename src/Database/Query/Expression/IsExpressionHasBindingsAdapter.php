<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;


class IsExpressionHasBindingsAdapter extends ExpressionWithBindings
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

    public function getValue()
    {
        return $this->expression->getValue();
    }

    public function getBindings(): array
    {
        return $this->expression->getBindings();
    }
}