<?php


namespace Tests\Models;

use Illuminate\Database\Query\Expression;

class Point implements GeometryInterface
{
    private $lat = 1;
    private $lng = 2;
    private $srid = 4236;

    private $expression;

    public function __construct($lat, $lng) {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->expression = new SpatialExpression($this);
    }

    public function getValue()
    {
        return $this->expression->getValue();
    }

    public function getBindings(): array
    {
        return $this->expression->getBindings();
    }

    public function toWkt()
    {
        return "POINT({$this->lng} {$this->lat})";

    }

    public function getSrid()
    {
        return $this->srid;
    }
}
