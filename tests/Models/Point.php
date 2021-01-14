<?php


namespace Tests\Models;

use Illuminate\Database\Query\Expression;

class Point implements GeometryInterface
{
    private $lat = 1;
    private $lng = 2;
    private $srid = 4236;

    public function __construct($lat, $lng) {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function getExpression(): Expression
    {
        return new SpatialExpression($this);
    }

    public function toWkt()
    {
//        return new ExpressionWithBindings("POINT(? ?)", [$this->lng, $this->lat]);
        return "POINT({$this->lng} {$this->lat})";

    }

    public function getSrid()
    {
        return $this->srid;
    }
}
