<?php

namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;

use Illuminate\Database\Query\Expression as BaseExpression;

class Expression extends BaseExpression implements IsExpression
{
    use ProvidesExpression;
}
