<?php


namespace Tests\Fixtures;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Expression;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionGrammar;

class EarlyBindingSpatialExpression extends Expression
{
    public function __construct(GeometryInterface $geometry)
    {
        $geomFromText = ExpressionGrammar::make()
            ->mySql("ST_GeomFromText(?, ?, 'axis-order=long-lat')")
            ->postgres("ST_GeomFromText(?, ?)");

        parent::__construct($geomFromText, [$geometry->toWkt(), $geometry->getSrid()]);
    }
}
