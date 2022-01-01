<?php

namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class ExpressionWithBindings extends Expression implements IsExpression, HasBindings
{
    use ProvidesExpressionWithBindings;
}
