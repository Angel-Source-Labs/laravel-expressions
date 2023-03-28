<?php


namespace Tests\Fixtures;

use Illuminate\Database\Grammar;

class Point implements GeometryInterface
{
    protected $lat = 1;
    protected $lng = 2;
    protected $srid = 4236;

    protected $expression;

    public function __construct($lat, $lng) {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->expression = new SpatialExpression($this);
    }

    public function setLat($lat)
    {
        $this->lat = (float) $lat;
    }

    public function setLng($lng)
    {
        $this->lng = (float) $lng;
    }

    public function getValue(Grammar $grammar = null)
    {
        return $this->expression->getValue($grammar);
    }

    public function hasBindings(): bool
    {
        return $this->expression->hasBindings();
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

    public function __toString()
    {
        return (string) $this->getValue();
    }
}
