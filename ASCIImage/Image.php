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

        //var_dump($marks); die();

        //figure out shapes; polygon, polyline, ellipse, point, line
        $group = array();
        for ($i = 0; $i < count($marks); $i++) {

            $currentMark = current($marks);
            $currentIndex = key($marks);
            $nextMark = next($marks);
            $nextIndex = key($marks);

            switch (count($currentMark)) {
                case 1:
                    $group[] = $currentMark[0];

                    if ($nextIndex === false || count($nextMark) > 1 || $nextIndex != $currentIndex + 1) {
                        if (count($group) == 1) {
                            $this->_shapes[] = array('type' => 'point', 'value' => $group);
                        } else {
                            $this->_shapes[] = array('type' => 'path', 'value' => $group);
                        }
                        //clear group
                        $group = array();
                    }
                    break;

                case 2:
                    $this->_shapes[] = array('type' => 'line', 'value' => $currentMark);
                    break;

                //case > 2
                default:
                    $this->_shapes[] = array('type' => 'ellipse', 'value' => $currentMark);
            }
        }

        //var_dump($this->_shapes); die();
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
