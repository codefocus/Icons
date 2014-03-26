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


class IconImage {
//	BITMAPINFOHEADER
	public $size;				//	DWORD			The number of bytes required by the structure.
	public $width;				//	LONG			The width of the bitmap, in pixels.
	public $height;				//	LONG			The height of the bitmap, in pixels.
								//					If biHeight is positive, the bitmap is a bottom-up DIB
								//					and its origin is the lower-left corner.
								//					If biHeight is negative, the bitmap is a top-down DIB
								//					and its origin is the upper-left corner.
	public $planes;				//	DWORD			The number of planes for the target device. Must be 1.
	public $bitcount;			//	WORD			The number of bits-per-pixel, or 0 for JPG/PNG (not allowed in icons)
	//public $compression;		//	DWORD			
	public $sizeimage;			//	DWORD			The size, in bytes, of the image.
	//public $xpelspermeter;		//	LONG			
	//public $ypelspermeter;		//	LONG			
	//public $clrused;			//	DWORD			
	//public $clrimportant;		//	DWORD			

//	ICONIMAGE
	//	[bitmapinfoheader]
	//public $xormask;			//	BYTE[]				DIB bits for XOR mask
	public $andmask;			//	BYTE[]				DIB bits for AND mask
	
	
	public $icondirentry;		//	IconDirEntry backreference
	public $palette;			//	RGBQUAD[]			Palette
	public $pixels;				//	RGBQUAD[]			Pixels
	
	
	
	/**
	 *	Prevent outside access to the constructor.
	 *	
	 */
	protected function __construct(){}
	
	
	/**
	 *	Create an IconImage from an IconDirEntry
	 *	
	 */
	public static function createFromIconDirEntry(IconDirEntry &$icondirentry, &$data) {
		//echo '<pre style="text-align: left; background-color: #fff; color: #444; border: 1px solid #ccc;">'.print_r($icondirentry, true).'</pre>';
	//	Unpack BITMAPINFOHEADER
		$bitmapinfoheader	= unpack('Vsize/lwidth/lheight/vplanes/vbitcount/Vcompression/Vsizeimage/lxpelspermeter/lypelspermeter/Vclrused/Vclrimportant', $data);
		if (1 !== $bitmapinfoheader['planes']) {
		//	Planes must be 1
			throw new \Exception('Not a valid icon image');
		}
	//	Only size, width, height, planes, bitcount and sizeimage are used for icons.
	//	All other members must be 0.
	//	http://msdn.microsoft.com/en-us/library/ms997538.aspx
		if (
			0 !== $bitmapinfoheader['compression'] or
			0 !== $bitmapinfoheader['xpelspermeter'] or
			0 !== $bitmapinfoheader['ypelspermeter']/* or
			0 !== $bitmapinfoheader['clrused'] or
			0 !== $bitmapinfoheader['clrimportant']*/) {
			throw new \Exception('Not a valid icon image');
		}
	//	Create IconImage object and populate it with the BITMAPINFOHEADER data.
		$image = new IconImage();
		$image->icondirentry	= $icondirentry;
		$image->size			= $bitmapinfoheader['size'];
		$image->width			= $bitmapinfoheader['width'];
		$image->height			= $bitmapinfoheader['height'];
		$image->planes			= $bitmapinfoheader['planes'];
		$image->bitcount		= $bitmapinfoheader['bitcount'];
		$image->sizeimage		= $bitmapinfoheader['sizeimage'];
		
	//	Read palette / image data
		switch ($image->bitcount) {
		case 32:
		//	32 bit color
		//	------------
		//	Get image data
		
		//	XOR (image) data
			$image->pixels = $image->dataToRgbQuads(substr($data, $image->size), $image->bitcount);
			
		//	Determine the size of the AND mask
			$sizePixels = $icondirentry->width * $icondirentry->height * ($icondirentry->bitcount/8);
			$sizeAndMask = (strlen($data) - $image->size - $sizePixels);
		//	AND mask width needs to be a mutiple of 32
			$andMaskWidth = $icondirentry->width;
			if (($andMaskWidth % 32) > 0) {
				$andMaskWidth += (32 - ($icondirentry->width % 32));
			}
		//	AND (mask) data
		//	for transparency
			$andMaskData = substr($data, $image->size + $sizePixels, $sizeAndMask);
			$andBits = '';
			for($idx_mask_byte = 0; $idx_mask_byte < $sizeAndMask; ++$idx_mask_byte) {
				$andBits .= str_pad(decbin(ord($andMaskData[$idx_mask_byte])), 8, '0', STR_PAD_LEFT);
			}
			$image->andmask = str_split(strrev($andBits), $andMaskWidth);
			if ($andMaskWidth != $icondirentry->width) {
				foreach($image->andmask as &$andMaskLine) {
					$andMaskLine = strrev(substr($andMaskLine, $icondirentry->width));
				}
			}
			
/*
			$image->render();
			exit();
*/
			break;
			
		case 24:
		//	24 bit color
		//	-----------------
		//	Get image data
			$image->pixels = $image->dataToRgbQuads(substr($data, $image->size), $image->bitcount);
			$image->render();
			exit();
/*
			//	Skip this until the code works.
			$length = $image->width * $image->height * ($icondirentry->bitcount / 8);
			$icondirentry['data'] = substr($data, $icondirentry->imageoffset + $image->size, $length);
*/
			break;
		
		case 8:
		case 4:
		//	Palettized image.
		//	-----------------
			//echo 'yep';
			//exit();
			if ($icondirentry->colorcount == 0) {
			//	4 and 8-bit images with a colorcount of "0" have 256 colors.
				$icondirentry->colorcount = 256;
			}
			if ($icondirentry->colorcount) {
			//	Get palette.
				$image->palette = $image->dataToRgbQuads(substr($data, $image->size, $icondirentry->colorcount * 4), $image->bitcount);
			//	Get image data
				$image->pixels = $image->dataToPaletteEntries(substr($data, $image->size + count($image->palette) * 4), $image->palette);
			}
			//$image->render();
			break;
			
		case 1:
			throw new \Exception('@TODO: 1-bit images not yet supported');
			exit();
		//	Black and white
			$icodata = substr($data, $icondirentry->imageoffset + $image->size, $icondirentry->colorcount * 4);
			
			$icondirentry['colors'][] = array(
				'blue'     => ord($icodata[0]),
				'green'    => ord($icodata[1]),
				'red'      => ord($icodata[2]),
				'reserved' => ord($icodata[3])
			);
			$icondirentry['colors'][] = array(
				'blue'     => ord($icodata[4]),
				'green'    => ord($icodata[5]),
				'red'      => ord($icodata[6]),
				'reserved' => ord($icodata[7])
			);
			$length = $image->width * $image->height / 8;
			$icondirentry['data'] = substr($data, $icondirentry->imageoffset + $image->size + 8, $length);
			break;
			
		}	//	switch bitcount
		
		return $image;
	}	//	function createFromString
	
	
	
	/**
	 *	Convert a data string to an array of RGB quads
	 *	
	 */
	protected function dataToRgbQuads($data, $bitcount) {
	//	Unpack data
		$data_array	= unpack('C*', $data);
	//	Convert raw data to an array of RGB quads
		$rgbquads		= array();
		$sizeof_data	= count($data_array);
		
		if (24 == $bitcount) {
		//	24 bit
			for($cursor = 0; $cursor <= $sizeof_data - 3; $cursor += 3) {
				$rgba = array();
				list($rgba['b'], $rgba['g'], $rgba['r']) = array_slice($data_array, $cursor, 3);
			//	Alpha is 1
				$rgba['a'] = 1;
				$rgbquads[] = $rgba;
			}
		}
		else {
		//	32 bit
			for($cursor = 0; $cursor <= $sizeof_data - 4; $cursor += 4) {
				$rgba = array();
				list($rgba['b'], $rgba['g'], $rgba['r'], $rgba['a']) = array_slice($data_array, $cursor, 4);
			//	Invert alpha
				$rgba['a'] = floor(128 - ($rgba['a'] / 2));
				$rgbquads[] = $rgba;
			}
		}
		//echo '<pre style="text-align: left; background-color: #fff; color: #444; border: 1px solid #ccc;">'.print_r($rgbquads, true).'</pre>';
		return $rgbquads;
	}	//	function dataToRgbQuads
	
	
	/**
	 *	Convert a data string to an array of RGB quads
	 *	
	 */
/*
	protected function dataToRgbQuadsViaPalette($data, array $palette) {
	//	Unpack data
		$data_array = $this->dataToPaletteEntries($data, $palette);
	//	Convert palette entries to an array of RGB quads
		$rgbquads		= array();
		foreach($data_array as $data_byte) {
			if (!isset($palette[$data_byte])) {
				echo 'Palette entry '.$data_byte.' does not exist!<br />';
				throw new \Exception('Palette entry does not exist');
			}
			$rgbquads[] = $palette[$data_byte];
		}
		return $rgbquads;
	}	//	function dataToRgbQuadsViaPalette
*/
	
	
	/**
	 *	Convert a data string to an array of palette indices
	 *	
	 */
	protected function dataToPaletteEntries($data, array $palette) {
	//	Unpack data
		if ($this->bitcount == 4) {
			$data_array_raw = unpack('C*', $data);
			$data_array = array();
			foreach($data_array_raw as $byte_raw) {
				$data_array[] = ($byte_raw >> 4);
				$data_array[] = ($byte_raw & 15);
			}
		}
		elseif ($this->bitcount == 8) {
			$data_array = array_values(unpack('C*', $data));
		}
		else {
			echo 'Unexpected bit count<br />';
			throw new \Exception('Unexpected bit count');
		}
		return $data_array;
	}	//	function dataToPaletteEntries
	
	
	/**
	* Ico::AllocateColor()
	* Allocate a color on $im resource. This function prevents
	* from allocating same colors on the same pallete. Instead
	* if it finds that the color is already allocated, it only
	* returns the index to that color.
	* It supports alpha channel.
	*
	* @param               resource    $im       Image resource
	* @param               integer     $red      Red component
	* @param               integer     $green    Green component
	* @param               integer     $blue     Blue component
	* @param   optional    integer     $alphpa   Alpha channel
	* @return              integer               Color index
	**/
	protected function allocateColor(&$gdimage, $rgba) {
		$color_in_palette = imagecolorexactalpha($gdimage, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
		if ($color_in_palette >= 0) {
			return $color_in_palette;
		}
		return imagecolorallocatealpha($gdimage, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
	}	//	function allocateColor
	
	
	
	/**
	 *	Render the image
	 *	
	 */
	public function render() {
		header('Content-type: image/png');
		
	//	Create image
		$gdimage = imagecreatetruecolor($this->icondirentry->width, $this->icondirentry->height);
		imagesavealpha($gdimage, true);
		
	//	Fill with transparent color
		$transparent = imagecolorallocatealpha($gdimage, 0, 0, 0,127);
		imagefill($gdimage, 0, 0, $transparent);
		
		if ($this->icondirentry->colorcount) {
		//	Build palette
			$palette = array();
			foreach($this->palette as $rgba) {
				$palette[] = $this->allocateColor($gdimage, $rgba);
			}
		}
		
	//	Draw pixels
	//	Build DIB bottom-to-top
		//$this->pixels
		$idx_pixel = 0;
		for ($y = $this->icondirentry->height - 1; $y >= 0; --$y) {
			for ($x = 0; $x < $this->icondirentry->width; ++$x) {
				
				
				if (!empty($this->andmask) and '1' == $this->andmask[$y][$x]) {
					++$idx_pixel;
					continue;
				}
				
				if ($this->icondirentry->colorcount) {
				//	Get palettized color
					imagesetpixel(
						$gdimage,
						$x,
						$y,
						$palette[$this->pixels[$idx_pixel]]
					);
				}
				else {
				//	Get RGBa color
					imagesetpixel(
						$gdimage,
						$x,
						$y,
						$this->allocateColor($gdimage, $this->pixels[$idx_pixel])
					);
				}
				
				++$idx_pixel;
			}	//	x
		}	//	y

		
		imagepng($gdimage);
		exit();
		
		
		
		
		
		
	}	//	function render
	
	
	
	/*
		function export
		//	32 bits: 4 bytes per pixel [ B | G | R | ALPHA ]
		//	24 bits: 3 bytes per pixel [ B | G | R ]
	*/
	
	
}	//	class IconImage

