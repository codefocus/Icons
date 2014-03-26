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


class IconDir {
	public $reserved;			//	WORD			Reserved (must be 0)
	public $type;				//	WORD			Resource Type (1 for icons)
	public $count;				//	WORD			How many images?
	public $entries;			//	ICONDIRENTRY[]	An entry for each image ("count" of 'em)
	
	
	/**
	 *	Prevent outside access to the constructor.
	 *	
	 */
	protected function __construct(){}
	
	
	/**
	 *	Create an IconDir object from an Icon object
	 *	
	 */
	public static function createFromIcon(Icon &$icon) {
		if (strlen($icon->data) < 6) {
		//	Require at least 6 bytes of data
			throw new \Exception('Not a valid icon file');
		}
	//	Unpack IconDir header data
		$data = unpack('vreserved/vtype/vcount/', $icon->data);
		if (0 !== $data['reserved']) {
		//	Reserved must be 0
			throw new \Exception('Not a valid icon file');
		}
		if (1 !== $data['type']) {
		//	Type must be 0
			throw new \Exception('Not a valid icon file');
		}
		if (0 === $data['count']) {
		//	No icons in this file
			throw new \Exception('No icons in this file');
		}
	//	Create IconDir object
		$icondir = new IconDir();
		$icondir->reserved	= $data['reserved'];
		$icondir->type		= $data['type'];
		$icondir->count		= $data['count'];
	//	Load IconDirEntry objects
		$icondir->entries	= IconDirEntry::createFromIconDir($icondir, $icon->data);
	//	Return
		return $icondir;
	}	//	function createFromIcon
	
	
	
}	//	class IconDir
