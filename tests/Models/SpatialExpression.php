<?php


namespace Tests\Models;


use AngelSourceLabs\LaravelExpressions\Query\Expression\ExpressionWithBindings;

class SpatialExpression extends ExpressionWithBindings
{
    public function __construct(GeometryInterface $geometry)
    {
        parent::__construct("ST_GeomFromText(?, ?, 'axis-order=long-lat')", [$geometry->toWkt(), $geometry->getSrid()]);
    }
}