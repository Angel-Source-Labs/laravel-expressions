<?php


namespace AngelSourceLabs\LaravelExpressions;


use Illuminate\Database\Query\Expression;

interface HasExpression
{
    public function getExpression(): Expression;
}