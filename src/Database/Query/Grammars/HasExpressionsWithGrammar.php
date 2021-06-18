<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

trait HasExpressionsWithGrammar
{
    /**
     * @param Expression | IsExpression $value
     */
    public function isExpressionWithGrammar($value)
    {
        if (! ($value instanceof IsExpression || $value instanceof Expression) ) return false;
        return $value->getValue() instanceof Grammar;
    }

    /**
     * @param Expression | IsExpression $expression
     */
    protected function setDriverForExpressionWithGrammar($expression)
    {
        if ($this->isExpressionWithGrammar($expression))
            $expression->getValue()->driver($this->driver);
    }

    public function configureExpressionsWithGrammar(Builder $query)
    {
        foreach ($this->selectComponents as $component) {
            if (isset($query->$component)) {
                collect($query->$component)->each(function($component) {
                    if (is_array($component)) {
                        if (isset($component['sql']))
                            $sqlOrExpression = $component['sql'];
                        elseif (isset($component['value']))
                            $sqlOrExpression = $component['value'];
                        else
                            $sqlOrExpression = $component;
                        $this->setDriverForExpressionWithGrammar($sqlOrExpression);
                    }
                });
            }
        }
    }

    public function parameter($value)
    {
        $value = $this->isExpressionWithGrammar($value) ?
            $value->getValue()->expression($this->driver) : $value;

        return parent::parameter($value);
    }

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
}
