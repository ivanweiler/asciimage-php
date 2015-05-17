<?php
namespace ASCIImage;

class Image
{
    const SYMBOLS = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz';
    const SYMBOL_BLANK = '.';

    private $_ascii;
    private $_width;
    private $_height;
    private $_shapes = array();
    private $_options = array();

    function __construct($ascii, $options = array())
    {
        $this->_ascii = $ascii;
        $this->_options = $options;
        $this->_parse();
    }

    private function _parse()
    {
        $this->_ascii = str_replace(array(' ', "\r\n"), array('', "\n"), $this->_ascii);
        $this->_ascii = trim($this->_ascii);
        $this->_ascii = preg_replace('#[^' . self::SYMBOLS . '\n]#', self::SYMBOL_BLANK, $this->_ascii);

        $rows = explode("\n", $this->_ascii);
        $width = 0;
        $marks = array();

        foreach ($rows as $i => $row) {
            $cols = str_split($row);
            $width = max($width, count($cols));
            foreach ($cols as $j => $symbol) {
                if ($symbol == self::SYMBOL_BLANK) {
                    continue;
                }
                $marks[strpos(self::SYMBOLS, "$symbol")][] = array($i, $j);
            }
        }

        $this->_height = count($rows);
        $this->_width = $width;
        ksort($marks, SORT_STRING);

        /**
         * figure out shapes (polygon, polyline, ellipse, point, line)
         */
        $pendingPoints = array();
        $lastIndex = false;
        foreach ($marks as $index => $mark) {

            $pointCount = count($mark);

            // flush pending shape if needed
            if ($pendingPoints && ($pointCount != 1 || $lastIndex !== $index - 1)) {
                $shapeType = (count($pendingPoints) == 1) ? Shape::TYPE_POINT : Shape::TYPE_POLYGON;
                $this->_shapes[] = new Shape($shapeType, $pendingPoints);
                $pendingPoints = array();
            }

            if ($pointCount == 1) {
                $pendingPoints = array_merge($pendingPoints, $mark);
                $lastIndex = $index;
            } elseif ($pointCount == 2) {
                $this->_shapes[] = new Shape(Shape::TYPE_LINE, $mark);
            } elseif ($pointCount > 2) {
                $this->_shapes[] = new Shape(Shape::TYPE_ELLIPSE, $mark);
            }

        }

        // close pending shape
        if ($pendingPoints) {
            $shapeType = (count($pendingPoints) == 1) ? Shape::TYPE_POINT : Shape::TYPE_POLYGON;
            $this->_shapes[] = new Shape($shapeType, $pendingPoints);
        }

        //set shape options
        foreach ($this->_options as $shapeIndex => $shapeOptions) {
            if(isset($this->_shapes[$shapeIndex])) {
                foreach ($shapeOptions as $optionKey => $optionValue) {
                    $this->_shapes[$shapeIndex]->$optionKey = $optionValue;
                }
            }
        }

    }

    public function getWidth()
    {
        return $this->_width;
    }

    public function getHeight()
    {
        return $this->_height;
    }

    public function getShapes()
    {
        return $this->_shapes;
    }

}
