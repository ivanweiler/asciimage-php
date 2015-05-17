<?php

spl_autoload_register(function($class) {
    require str_replace(array('_','\\'), DIRECTORY_SEPARATOR, $class) . '.php';
});

$arrowImageWithColor = "
. . . . . . . . . . . .
. . . . . . . . . . . .
. . . . . . . . . . . .
. . . . . . 1 . . . . .
. . . . . . o o . . . .
. . . 7 o o 8 o 2 . . .
. . . 6 o o 5 o 3 . . .
. . . . . . o o . . . .
. . . . . . 4 . . . . .
. . . . . . . . . . . .
. . . . . . . . . . . .
. . . . . . . . . . . .
";

$chevronImageWithColor = "
. . . . . . . . . . . .
. . . 1 2 . . . . . . .
. . . A o o . . . . . .
. . . . o o o . . . . .
. . . . . o o o . . . .
. . . . . . 9 o 3 . . .
. . . . . . 8 o 4 . . .
. . . . . o o o . . . .
. . . . o o o . . . . .
. . . 7 o o . . . . . .
. . . 6 5 . . . . . . .
. . . . . . . . . . . .
";

$image = new \ASCIImage\Image($chevronImageWithColor, array(0 => array('color' => '#ff0000')));
//$image->setShapeOption(0, array('path' => 'close', 'linecap' => 'round'));
$svg = new \ASCIImage\Renderer\Svg($image);
$svg->display();

