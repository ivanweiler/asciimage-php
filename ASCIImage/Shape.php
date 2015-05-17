<?php

namespace ASCIImage;

class Shape
{
    const TYPE_POINT    = 'point';
    const TYPE_LINE     = 'line';
    const TYPE_POLYGON  = 'polygon';
    //const TYPE_POLYLINE = 'polyline';
    const TYPE_ELLIPSE  = 'ellipse';

    public $type;
    public $points = array();

    public $path = 'close'; //close|open
    //public $fill = false;
    public $color = '#000000';
    //public $fillColor = '#ffffff';
    //public $strokeColor = '#000000';
    public $linecap = 'square'; //round|square

    public function __construct($type, $points)
    {
        $this->type = $type;
        $this->points = $points;
    }
}