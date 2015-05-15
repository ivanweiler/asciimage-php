<?php

//error_reporting(E_ALL | E_STRICT);
//ini_set('display_errors', 1);

spl_autoload_register(function($class) {
    require str_replace(array('_','\\'), DIRECTORY_SEPARATOR, $class) . '.php';
});

$asciimage = '
1 # . # 2
. . . . .
6 . . . .
. . . . .
';

$asciimage2 = '
. . . . 8 . . . .
. 7 . . 8 . . 9 .
. . 7 . . . 9 . .
. . . . 1 . . . .
6 6 . 1 # 1 . 2 2
. . . . 1 . . . .
. . 5 . . . 3 . .
. 5 . . 4 . . 3 .
. . . . 4 . . . .
';

$image = new \ASCIImage\Image($asciimage2);
$svg = new \ASCIImage\Renderer\Svg($image);
echo $svg->render();

//$image->setAscii();
//$image->setOptions();
//$image->setOption(2, array());
