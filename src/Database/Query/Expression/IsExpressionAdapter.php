<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;


use Illuminate\Database\Query\Expression;

class IsExpressionAdapter extends \Illuminate\Database\Query\Expression
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
}
