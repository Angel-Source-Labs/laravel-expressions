<?php

namespace AngelSourceLabs\LaravelExpressions\Query;

use AngelSourceLabs\LaravelExpressions\Query\Expression\HasBindings;
use AngelSourceLabs\LaravelExpressions\Query\Expression\HasExpression;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Builder extends QueryBuilder
{


    public function cleanBindings(array $bindings)
    {
        $unpackedBindings = [];
        foreach ($bindings as &$binding) {
            if ($binding instanceof HasBindings) {
                $unpackedBindings = array_merge($unpackedBindings, $binding->getBindings());
            }
            else {
                $unpackedBindings[] = $binding;
            }
        }

        return parent::cleanBindings($unpackedBindings);
    }
}
