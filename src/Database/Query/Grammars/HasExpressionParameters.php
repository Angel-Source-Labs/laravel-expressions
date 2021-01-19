<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;

trait HasExpressionParameters
{
    public function parameter($value)
    {
        $value = ($value instanceof IsExpression
            && $value->getValue() instanceof Grammar) ?
            $value->getValue()->expression($this->driver) : $value;

        return parent::parameter($value);
    }
}
