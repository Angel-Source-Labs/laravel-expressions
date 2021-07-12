<?php


namespace Tests\Fixtures;

class EarlyBindingPoint extends Point
{
    public function __construct($lat, $lng) {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->expression = new EarlyBindingSpatialExpression($this);
    }
}
