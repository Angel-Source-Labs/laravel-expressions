<?php


namespace AngelSourceLabs\LaravelExpressions\Query\Expression;


use Illuminate\Database\Query\Expression;

interface HasExpression
{
    public function getExpression(): Expression;
}