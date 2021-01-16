<?php


namespace Tests\Models;


use AngelSourceLabs\LaravelExpressions\Query\Expression\HasBindings;
use AngelSourceLabs\LaravelExpressions\Query\Expression\IsExpression;

interface GeometryInterface extends IsExpression, HasBindings
{
    public function toWkt();
    public function getSrid();
}