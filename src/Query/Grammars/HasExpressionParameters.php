<?php


namespace AngelSourceLabs\LaravelExpressions\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\Query\Expression\HasExpression;
use Illuminate\Database\Query\Expression;

trait HasExpressionParameters
{
    public function parameter($value)
    {
        $value = ($value instanceof HasExpression) ? $value->getExpression() : $value;
        $value = ($value instanceof Expression &&
                $value->getValue() instanceof Grammar)
            ? $value->getValue()->expression($this->driver) : $value;

        return parent::parameter($value);
    }
}
