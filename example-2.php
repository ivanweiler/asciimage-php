<?php

spl_autoload_register(function($class) {
    require str_replace(array('_','\\'), DIRECTORY_SEPARATOR, $class) . '.php';
});

$chevron = "
. . . . . . . . . . . . .
. A B . . . L M . . . . .
. J # # . . U # # . . . .
. . # # # . . # # # . . .
. . . # # # . . # # # . .
. . . . I # C . . T # N .
. . . . H # D . . S # O .
. . . # # # . . # # # . .
. . # # # . . # # # . . .
. G # # . . R # # . . . .
. F E . . . Q P . . . . .
. . . . . . . . . . . . .
";

$image = new \ASCIImage\Image($chevron, array(
    0 => array('color' => '#ff0000'),
    1 => array('color' => '#0000ff', 'linecap' => 'round')
));
$svg = new \ASCIImage\Renderer\Svg($image);
$svg->display();

