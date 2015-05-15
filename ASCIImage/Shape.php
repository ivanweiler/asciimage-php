<?php

namespace ASCIImage;

class Shape
{
    public $type = 'polygon';
    public $points = array(array(1, 1), array(2, 3)); //points?

    public $path = false; //path: open close
    public $fill = false;
    public $color = '#000000';
    public $linecap = 'round';
    //fill, stroke
}