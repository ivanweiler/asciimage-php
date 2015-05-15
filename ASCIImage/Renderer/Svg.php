<?php

namespace ASCIImage\Renderer;

class Svg
{
    const DOCTYPE = <<<EOS
<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
EOS;

    const HEAD = <<<EOS
<svg xmlns="http://www.w3.org/2000/svg"
    style="width: %upx; height: %upx;"
    preserveAspectRatio="none"
    viewBox="0 0 %u %u">
EOS;

    const STYLE = <<<EOS
<style>
ellipse,polygon{fill:%s;stroke:%s;stroke-width:%u}
line,polyline{stroke:%s;stroke-width:%u}
%s
</style>
EOS;

    const FOOT = '</svg>';

    const SCALE = 10;

    const LINE      = '<line id="%s" x1="%u" y1="%u" x2="%u" y2="%u"/>';
    const POLYGON   = '<polygon id="%s" points="%s"/>';
    const POLYLINE  = '<polyline id="%s" points="%s"/>';
    const ELLIPSE   = '<ellipse id="%s" cx="%u" cy="%u" rx="%u" ry="%u"/>';

    function __construct(\ASCIImage\Image $asciImage, $options = array())
    {
        $this->_asciImage = $asciImage;
    }

    function render()
    {
        $svg = '';
        $svg .= sprintf(
            self::HEAD,
            300,
            300 * $this->_asciImage->getWidth() / $this->_asciImage->getHeight(),
            $this->_asciImage->getWidth() * 10,
            $this->_asciImage->getHeight() * 10
        );

        $svg .= <<<EOS
<style>
ellipse,polygon{ fill:black; stroke:black; stroke-width:10; }
line{ stroke:black; stroke-width:10; stroke-linecap: square; }
polyline{ stroke:black; stroke-width: 10; stroke-linecap: round; fill: none; }
</style>
EOS;

        //$svg .= '<rect width="100%" height="100%" fill="red" />';
        //var_dump($this->_asciImage->getShapes()); die();

        foreach ($this->_asciImage->getShapes() as $index => $shape) {

            array_walk($shape['value'], array($this, '_svg_coord'));

            switch ($shape['type']) {

                case 'line':
                    $svg .= "\n" . sprintf(self::LINE, "shape$index", $shape['value'][0][0], $shape['value'][0][1], $shape['value'][1][0], $shape['value'][1][1]);
                    break;
                case 'point':
                    $fakeX1 = $shape['value'][0][0] - 0.01;
                    $fakeX2 = $shape['value'][0][0] + 0.01;
                    $y = $shape['value'][0][1];
                    $svg .= "\n" . sprintf(self::LINE, "shape$index", $fakeX1, $y, $fakeX2, $y);
                    break;
                case 'path':
                    $points = array();
                    foreach ($shape['value'] as $coord) {
                        $points[] = $coord[0] . ',' . $coord[1];
                    }
                    $svg .= "\n" . sprintf(self::POLYLINE, "shape$index", implode(' ', $points));
                    break;
                case 'ellipse':
                    foreach ($shape['value'] as $i => $coord) {
                        if ($i == 0) {
                            $minX = $maxX = $coord[0];
                            $minY = $maxY = $coord[1];
                        } else {
                            $minX = min($minX, $coord[0]);
                            $maxX = max($maxX, $coord[0]);
                            $minY = min($minY, $coord[1]);
                            $maxY = max($maxY, $coord[1]);
                        }
                    }

                    $centerX = ($minX + $maxX) / 2;
                    $centerY = ($minY + $maxY) / 2;
                    $rx = ($maxX - $minX) / 2;
                    $ry = ($maxY - $minY) / 2;

                    $svg .= "\n" . sprintf(self::ELLIPSE, "shape$index", $centerX, $centerY, $rx, $ry);
                    break;
            }
        }

        $svg .= "\n" . self::FOOT;
        return $svg;
    }

    function display()
    {
        header('Content-Type: image/svg+xml');
        echo $this->render();
    }

    private function _svg_coord(&$item, $key)
    {
        $item = array($item[1] * 10 + 5, $item[0] * 10 + 5);
    }
}