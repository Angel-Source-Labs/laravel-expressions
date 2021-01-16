<?php


namespace Tests\Models;


use AngelSourceLabs\LaravelExpressions\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\Query\Expression\Grammar;

class SpatialExpression extends ExpressionWithBindings
{
    public function __construct(GeometryInterface $geometry)
    {
        $geomFromText = Grammar::make()
            ->mySql("ST_GeomFromText(?, ?, 'axis-order=long-lat')")
            ->postgres("ST_GeomFromText(?, ?)");

        parent::__construct($geomFromText, [$geometry->toWkt(), $geometry->getSrid()]);
    }
}