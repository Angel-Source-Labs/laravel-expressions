<?php


namespace Tests\Models;


use AngelSourceLabs\LaravelExpressions\HasExpression;

interface GeometryInterface extends HasExpression
{
    public function toWkt();
    public function getSrid();
}