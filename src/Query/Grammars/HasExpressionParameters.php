<?php


namespace AngelSourceLabs\LaravelExpressions\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\Query\Expression\HasExpression;
use AngelSourceLabs\LaravelExpressions\Query\Expression\isExpression;
use Illuminate\Database\Query\Expression;

trait HasExpressionParameters
{
    public function parameter($value)
    {
        $value = ($value instanceof isExpression) ? $value->getValue() : $value;
        $value = ($value instanceof Grammar) ? $value->expression($this->driver) : $value;

        return parent::parameter($value);
    }
}
