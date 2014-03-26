<?php
/**
 *	Icons
 *
 *	Load Windows icon files in PHP
 *
 *	LICENSE: https://creativecommons.org/licenses/by-sa/4.0/
 *
 *	@author     http://www.codefocus.ca/
 *	@license	https://creativecommons.org/licenses/by-sa/4.0/
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
			throw new Exception('File does not exist: '.basename($filename));
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
	
	
}	//	class Icon


