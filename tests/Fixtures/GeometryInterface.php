<?php


namespace Tests\Fixtures;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;

interface GeometryInterface extends IsExpression
{
    public function toWkt();
    public function getSrid();
}