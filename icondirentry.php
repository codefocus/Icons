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


class IconDirEntry {
	public $width;				//	BYTE			Width, in pixels, of the image
	public $height;				//	BYTE			Height, in pixels, of the image
	public $colorcount;			//	BYTE			Number of colors in image (0 if >=8bpp)
	public $reserved;			//	BYTE			Reserved ( must be 0)
	public $planes;				//	WORD			Color Planes
	public $bitcount;			//	WORD			Bits per pixel
	public $bytesinresource;	//	DWORD			How many bytes in this resource?
	public $imageoffset;		//	DWORD			Where in the file is this image?
	
	
	/**
	 *	Prevent outside access to the constructor.
	 *	
	 */
	protected function __construct(){}
	
	
	/**
	 *	Create IconDirEntry objects from an IconDir
	 *	
	 */
	public static function createFromIconDir(IconDir &$icondir, &$data) {
	//	
		$cursor					= 6;
		$sizeof_icondirentry	= 16;
		
	//	Extract all IconDirEntry objects from the data
		$iconDirEntries = array();
		for($idx_icondirentry = 0; $idx_icondirentry < $icondir->count; ++$idx_icondirentry) {
		//	Fetch enough bytes for one IconDirEntry
			$icondirentrydata		= substr($data, $cursor, $sizeof_icondirentry);
		//	Unpack IconDirEntry data
			$icondirentrydata		= unpack('Cwidth/Cheight/Ccolorcount/Creserved/vplanes/vbitcount/Vbytesinresource/Vimageoffset', $icondirentrydata);
			if (0 !== $icondirentrydata['reserved']) {
			//	Reserved must be 0
				throw new Exception('Not a valid icon file');
			}
		//	Create IconDirEntry
			$iconDirEntry = new IconDirEntry();
			$iconDirEntry->width			= $icondirentrydata['width'];
			$iconDirEntry->height			= $icondirentrydata['height'];
			$iconDirEntry->colorcount		= $icondirentrydata['colorcount'];
			$iconDirEntry->reserved			= $icondirentrydata['reserved'];
			$iconDirEntry->planes			= $icondirentrydata['planes'];
			$iconDirEntry->bitcount			= $icondirentrydata['bitcount'];
			$iconDirEntry->bytesinresource	= $icondirentrydata['bytesinresource'];
			$iconDirEntry->imageoffset		= $icondirentrydata['imageoffset'];
		//	Add to array
			$iconDirEntries[] = $iconDirEntry;
		//	Move cursor
			$cursor += $sizeof_icondirentry;
		}	//	each icondirentry
		
		return $iconDirEntries;
		
	}	//	function createFromIconDir
	
	
}	//	class IconDirEntry

