<?php

namespace ASCIImage;

class Shape
{
    const TYPE_POINT    = 'point';
    const TYPE_LINE     = 'line';
    const TYPE_POLYGON  = 'polygon';
    const TYPE_POLYLINE = 'polyline';
    const TYPE_ELLIPSE  = 'ellipse';

    public $type;
    public $points = array();

    public $path = false; //path: open close
    public $fill = false;
    public $color = '#000000';
    public $linecap = 'round';
    //fill, stroke

    public function __construct($type, $points)
    {
        $this->type = $type;
        $this->points = $points;
    }
}