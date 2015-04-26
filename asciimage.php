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
	
	function __construct($ascii, $options = array())
	{
		$this->_ascii = $ascii;
		$this->_parse();
	}
	
	private function _parse()
	{
		$this->_ascii = str_replace(array(' ',"\r\n"),array('',"\n"), $this->_ascii);
		$this->_ascii = trim($this->_ascii);
		$this->_ascii = preg_replace('#[^' . self::SYMBOLS . '\n]#', self::SYMBOL_BLANK, $this->_ascii);
		
		$rows = explode("\n", $this->_ascii);
		$width = 0;
		$marks = array();
		
		foreach($rows as $i => $row) {
			$cols = str_split($row);
			$width = max($width, count($cols));
			foreach($cols as $j => $symbol) {
				if($symbol == '.') {
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
		for($i=0; $i<count($marks); $i++) {
			
			$currentMark = current($marks);
			$currentIndex = key($marks);
			$nextMark = next($marks);
			$nextIndex = key($marks);
			
			switch(count($currentMark)) {
				case 1:
					$group[] = $currentMark[0];

					if($nextIndex === false || count($nextMark) > 1 || $nextIndex != $currentIndex+1) {
						if(count($group) == 1) {
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

//maybe?
class Shape
{
	public $type = 'polygon';
	public $position = array(array(1,1), array(2,3)); //points?
	public $closed = false; //path: open closed
	public $color = '#000000';
	//fill, stroke
}

class Renderer_Svg
{
	const SCALE = 10;
	
	const DOCTYPE = '<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';

	const HEAD = '<svg xmlns="http://www.w3.org/2000/svg" style="width: %upx; height: %upx;" preserveAsdpectRatio="none" viewBox="0 0 %u %u">';
	const STYLE = <<<EOS
<style>
ellipse,polygon{fill:%s;stroke:%s;stroke-width:%u}
line,polyline{stroke:%s;stroke-width:%u}
%s
</style>
EOS;
	const FOOT = "</svg>";
	
	const LINE = '<line id="%s" x1="%u" y1="%u" x2="%u" y2="%u"/>';
	const POLYGON = '<polygon id="%s" points="%s"/>';
	const POLYLINE = '<polyline id="%s" points="%s"/>';
	const ELLIPSE = '<ellipse id="%s" cx="%u" cy="%u" rx="%u" ry="%u"/>';
	
	function __construct(\ASCIImage\Image $asciImage, $options = array())
	{
		$this->_asciImage = $asciImage;
	}
	
	function render() 
	{
		$svg = '';
		$svg .= sprintf(self::HEAD, 
					300, 300*$this->_asciImage->getWidth()/$this->_asciImage->getHeight(), 
					$this->_asciImage->getWidth()*10, $this->_asciImage->getHeight()*10
				);
		
		$svg .= <<<EOS

<style>
ellipse,polygon{fill:black;stroke:black;stroke-width:10}
line{ stroke:black; stroke-width:10; stroke-linecap: square; }

polyline{ stroke:black; stroke-width: 10; stroke-linecap: square; fill: none;}

</style>
EOS;
		
		//$svg .= '<rect width="100%" height="100%" fill="red" />';
		//var_dump($this->_asciImage->getShapes()); die();
		
		foreach($this->_asciImage->getShapes() as $index => $shape) {
			
			array_walk($shape['value'], array($this, '_svg_coord'));
			
			switch($shape['type']) {
				
				case 'line':
					$svg .= "\n" . sprintf(self::LINE, "shape$index", $shape['value'][0][0], $shape['value'][0][1], $shape['value'][1][0], $shape['value'][1][1]);
					break;
				case 'point':
					break;
				case 'path':
					$points = array();
					foreach($shape['value'] as $coord) {
						$points[] = $coord[0].','.$coord[1];
					}
					$svg .= "\n" . sprintf(self::POLYLINE, "shape$index", implode(' ', $points));
					break;
				case 'ellipse':
					foreach($shape['value'] as $i => $coord) {
						if($i == 0) {
							$minX = $maxX = $coord[0];
							$minY = $maxY = $coord[1];
						} else {
							$minX = min($minX, $coord[0]);
							$maxX = max($maxX, $coord[0]);
							$minY = min($minY, $coord[1]);
							$maxY = max($maxY, $coord[1]);
						}
					}

					$centerX = ($minX + $maxX)/2;
					$centerY = ($minY + $maxY)/2;
					$rx = ($maxX - $minX)/2;
					$ry = ($maxY - $minY)/2;
				
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
		$item = array($item[1]*10 + 5, $item[0]*10 + 5);
	}
}

// :)
class Renderer_Ttf
{
}

$asciimage = '
. # 1 # .
1 . . . 1
. . . . .
. . 1 . .
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

$image = new Image($asciimage2);
$svg = new Renderer_Svg($image);
echo $svg->render();

//$image->setAscii();
//$image->setOptions();
//$image->setOption(2, array());



