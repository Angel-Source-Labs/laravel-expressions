<?php


namespace Tests\Fixtures;


use AngelSourceLabs\LaravelExpressionGrammar\ExpressionGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Expression;

class SpatialExpression extends Expression
{
    public function __construct(GeometryInterface $geometry)
    {
        $geomFromText = ExpressionGrammar::make()
            ->mySql("ST_GeomFromText(?, ?, 'axis-order=long-lat')")
            ->postgres("ST_GeomFromText(?, ?)");

        parent::__construct($geomFromText, [[$geometry, "toWkt"], [$geometry, "getSrid"]]);
    }
}
