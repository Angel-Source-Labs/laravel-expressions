<?php


namespace Tests\Models;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\HasBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;

interface GeometryInterface extends IsExpression, HasBindings
{
    public function toWkt();
    public function getSrid();
}