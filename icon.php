<?php
/**
 *	Icons
 *
 *	Load Windows icon files in PHP
 *
 *
 *	@author     http://www.codefocus.ca/
 *	@license	http://www.apache.org/licenses/LICENSE-2.0
 *	@version    0.1
 */


namespace Codefocus\Icons;

require_once('icondir.php');
require_once('icondirentry.php');
require_once('iconimage.php');

class Icon {
	
	public $data;			//	Icon data string
	protected $icondir;		//	IconDir
	
	
	/**
	 *	Prevent outside access to the constructor.
	 *	
	 */
	protected function __construct(){}
	
	
	/**
	 *	Create an icon from file
	 *	
	 */
	public static function createFromFile($filename) {
		if (!file_exists($filename)) {
			throw new \Exception('File does not exist: '.basename($filename));
		}
		$data = file_get_contents($filename);
		return self::createFromString($data);
	}	//	function createFromFile
	
	
	/**
	 *	Create an icon from a string (icon file contents)
	 *	
	 */
	public static function createFromString($data) {
	//	Create icon
		$icon			= new Icon();
		$icon->data		= $data;
	//	Create an icondir for this 
		$icon->icondir	= IconDir::createFromIcon($icon);
	//	Create icon images
		$icon->images	= array();
		foreach($icon->icondir->entries as &$icondirentry) {
			try {
				$iconimagedata	= substr($data, $icondirentry->imageoffset, $icondirentry->bytesinresource);
				$icon->images[]	= IconImage::createFromIconDirEntry($icondirentry, $iconimagedata);
			}
			catch(\Exception $e) {
				//$icon->images[] = false;
			}
		}
		return $icon;
	}	//	function createFromString
	
	
	
	public function getImage($minWidth=16, $minBitcount=8, $maxWidth=256, $maxBitcount=null) {
		$qualifyingImages = array();
		foreach($this->images as &$image) {
			if (
				$image->width >= $minWidth and
				$image->bitcount >= $minBitcount and
				$image->width <= $maxWidth and
				$image->bitcount <= $maxBitcount
			) {
				$qualifyingImages[] = $image;
			}
		}
		if (0 == count($qualifyingImages)) {
		//	No qualifying images found.
			return false;
		}
		if (1 == count($qualifyingImages)) {
		//	One qualifying image found.
			return $qualifyingImages[0];
		}
	//	Multiple qualifying images found.
	//	return the best quality one.
		$bestImage = $qualifyingImages[0];
		foreach($qualifyingImages as &$image) {
			if ($image->bitcount > $bestImage->bitcount) {
			//	Higher bitcount.
				$bestImage = $image;
			}
			elseif ($image->width > $bestImage->width) {
			//	Better resolution
				$bestImage = $image;
			}
		}
	//	Return the best image
		return $bestImage;
	}	//	function getIcon
	
	
	
	
}	//	class Icon


