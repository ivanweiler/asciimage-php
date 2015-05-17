<?php

namespace ASCIImage\Renderer;

use ASCIImage\Shape;

class Svg
{
    const DOCTYPE = <<<EOS
<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
EOS;

    const HEAD = <<<EOS
<svg xmlns="http://www.w3.org/2000/svg" style="width: %upx; height: %upx;" preserveAspectRatio="none" viewBox="0 0 %u %u">
EOS;

    const STYLE = <<<EOS
<style>
line { stroke:black; stroke-width:10; stroke-linecap:square; }
polyline { stroke:black; fill:none; stroke-width:10; stroke-linecap:square; }
ellipse, polygon { stroke:black; fill:black; stroke-width:10; }
%s
</style>
EOS;

    const FOOT = '</svg>';

    const SCALE = 10;

    const LINE      = '<line id="%s" x1="%u" y1="%u" x2="%u" y2="%u"/>';
    const POLYGON   = '<polygon id="%s" points="%s"/>';
    const POLYLINE  = '<polyline id="%s" points="%s"/>';
    const ELLIPSE   = '<ellipse id="%s" cx="%u" cy="%u" rx="%u" ry="%u"/>';

    //private $_shapeDefaults = array();

    private $_asciImage;

    public function __construct(\ASCIImage\Image $asciImage, $options = array())
    {
        $this->_asciImage = $asciImage;
    }

    public function render()
    {
        $svg = '';
        $svg .= sprintf(
            self::HEAD,
            300,
            300 * $this->_asciImage->getWidth() / $this->_asciImage->getHeight(),
            $this->_asciImage->getWidth() * self::SCALE,
            $this->_asciImage->getHeight() * self::SCALE
        );

        $elements = array();
        $styles = array();
        foreach ($this->_asciImage->getShapes() as $index => $shape) {

            $svgPoints = array_map(array($this, '_getSvgPoints'), $shape->points);

            switch ($shape->type) {

                case Shape::TYPE_LINE:
                    $elements[] = sprintf(self::LINE, "shape$index", $svgPoints[0][0], $svgPoints[0][1], $svgPoints[1][0], $svgPoints[1][1]);
                    $styles["shape$index"] = array(
                        'stroke'            => $shape->color,
                        'stroke-linecap'    => $shape->linecap
                    );
                    break;

                case Shape::TYPE_POINT:
                    $fakeX1 = $svgPoints[0][0] - 0.01;
                    $fakeX2 = $svgPoints[0][0] + 0.01;
                    $y = $svgPoints[0][1];
                    $elements[] = sprintf(self::LINE, "shape$index", $fakeX1, $y, $fakeX2, $y);
                    $styles["shape$index"] = array(
                        'stroke'            => $shape->color,
                        'stroke-linecap'    => $shape->linecap
                    );
                    break;

                case Shape::TYPE_POLYGON:
                    $points = array();
                    foreach ($svgPoints as $coord) {
                        $points[] = $coord[0] . ',' . $coord[1];
                    }

                    $styles["shape$index"] = array(
                        'stroke'            => $shape->color,
                        'stroke-linecap'    => $shape->linecap
                    );

                    if($shape->path == 'open') {
                        $elements[] = sprintf(self::POLYLINE, "shape$index", implode(' ', $points));
                    } else {
                        $elements[] = sprintf(self::POLYGON, "shape$index", implode(' ', $points));
                        $styles["shape$index"]['fill'] = $shape->color;
                    }

                    break;

                case Shape::TYPE_ELLIPSE:
                    foreach ($svgPoints as $i => $coord) {
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

                    $elements[] = sprintf(self::ELLIPSE, "shape$index", $centerX, $centerY, $rx, $ry);
                    $styles["shape$index"] = array(
                        'stroke'            => $shape->color,
                        'fill'              => $shape->color,
                    );
                    break;
            }
        }

        $style = '';
        foreach($styles as $elementId => $elementStyle) {
            $style .= "\n#$elementId{";
            foreach($elementStyle as $property => $value) {
                $style .= " $property:$value;";
            }

            $style .= " }";
        }

        $svg .= "\n" . sprintf(self::STYLE, $style);
        $svg .= "\n" . implode("\n", $elements);
        $svg .= "\n" . self::FOOT;
        return $svg;
    }

    public function display()
    {
        header('Content-Type: image/svg+xml');
        echo self::DOCTYPE . "\n" . $this->render();
    }

    private function _getSvgPoints($point)
    {
        return array($point[1] * self::SCALE + self::SCALE/2, $point[0] * self::SCALE + self::SCALE/2);
    }
}