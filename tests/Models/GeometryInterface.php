<?php


namespace Tests\Models;


use AngelSourceLabs\LaravelExpressions\Query\Expression\HasExpression;

interface GeometryInterface extends HasExpression
{
    public function toWkt();
    public function getSrid();
}