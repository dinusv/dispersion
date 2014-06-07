<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/****************************************************************************
**
** Copyright (C) 2010-2014 Dinu SV.
** (contact: mail@dinusv.com)
** This file is part of Dispersion framework.
** 
** The file may be used under the terms of the MIT license, appearing in the
** file LICENSE.MIT included in the packaging of this file.
**
****************************************************************************/

 /**
 * @version 1.2
 * @author DinuSV
 */

/** 
 * @ingroup helpers
 * @brief Image wrapper. Offers support between image-format conversions, resizing, watermarking and quality management.
 * 
 * Creating an object is done by passing an image path to the constructor
 * 
 * @code
 * // Relative path
 * $image = new Image("myimage.jpg");
 * // Absolute path
 * $image = new Image( ROOT . "/images/myimage.jpg" );
 * @endcode
 * 
 * Width and height of the image can be optained by the following getter methods :
 * 
 * @code
 * $image  = new Image("myimage.jpg");
 * $width  = $image->getWidth(); // Get width in pixels
 * $height = $image->getHeight();// Get height in pixels
 * $type   = $image->getType();  // jpg, png or gif
 * @endcode
 * 
 * For custom processing, the php image resource can be obtained : 
 * 
 * @code
 * $resource = $image->resource();
 * @endcode
 * 
 * Resizing can be done by either scaling the image, or resizing it according to a set width or height.
 * 
 * @code
 * $image = new Image("myimage.jpg");
 * $image->scale(50); // scale image by 50%
 * @endcode
 * 
 * When resizing by a set size, the original proportions of the image can be kept, and resizing the image
 * will be based either on the width, or on the height, depending which one is bigger. Sharpening while 
 * resizing will create a higher quality image, but will consume more resorces, so the process might take
 * longer.
 * 
 * @code
 * $image = new Image("myimage");
 * $image->resize( 400, 400 ); //constrain proportions and sharpen the image
 * $image->resize( 400, 400, true, false); //constrain proportions, but don't sharpen
 * @endcode
 * 
 * Two images can be merged using Image::mergeWidth :
 * 
 * @code
 * // Merge a logo with 75% opacity over a waterfall
 * $image = new Image("waterfall.jpg");
 * $image->mergeWith( new Image("logo.jpg"), 0, 0, 0, 0, null, null, 75% );
 * 
 * // Crop the center of the waterfall by 200x200px and merge it over the logo
 * $image_logo  = new Image("logo.jpg");
 * $image_water = new Image("waterfall.jpg");
 * $image_logo->mergeWith( $image_water,
 *     0, 0,
 *     $image_water->getWidth() / 2 - 100, // crop 100px just before the center
 *     $image_water->getHeight() / 2 - 100, // crop 100px above the center
 *     200, 200 // crop on a 200x200 pixel radius
 *     75 // 75% opacity
 * );
 * @endcode
 * 
 * Adding a watermark at the center of an image is done by positioning it's corner relative to its 
 * width and height, and relative to the images width and height. Presuming its corner is positioned at 
 * (x,y), we need to subtract half of the width and half of the height of the watermark from the center
 * of the image.
 * 
 * @code
 * $source    = new Image("source.jpg");
 * $watermark = new Image("watermark.jpg");
 * $source->mergeWith(
 *     $watermark,
 *     floor( $source->getWidth() / 2 ) - floor( $watermark->getWidth() / 2 ),
 *     floor( $source->getHeight() / 2) - floor( $watermark->getHeight() / 2),
 *     0, 0,
 *     null, null,
 *     70%
 * );
 * @endcode
 * 
 * The new image can be saved using Image::saveAs :
 * 
 * @code
 * $image = new Image("myimage.jpg");
 * $image->saveAs( "myimage.png" ); // image will be converted to png
 * $image->saveAs( "myimage.jpg", null, 100 ); // image will be saved as a jpeg with high quality
 * @endcode
 * 
 * These methods can also be chained to provide a more readable code :
 * @code
 * $image = new Image("image.jpg");
 * $image->resize( 800, 600 )->mergeWith( new Image("watermark.jpg") )->saveAs( "wimage.jpg" );
 * @endcode
 * 
 */
class Image{
	
	private static
		/** 
		 * @var $default_quality
		 * int : Default quality to save the image as ( [0, 100] )
		 */
		$default_quality = 90;
	
	private
		/** 
		 * @var $type
		 * string : Type of the image ( 'jpeg', 'png', 'gif' )
		 */
		$type,
		
		/** 
		 * @var $source
		 * resource : Image resource
		 */
		$source,
		
		/**
		 * @var $height
		 * int : Width of the image
		 */
		$height,
		
		/** 
		 * @var $width
		 * int : Height of the image
		 */
		$width;
	
	/** Constructor
	 * 
	 * @param string $image : the location of the image
	 * 
	 * @throws InvalidFileTypeException
	 * @throws FileNotFoundException
	 */
	public function Image( $image ){
		if ( !file_exists( $image ) ) throw new FileNotFoundException( 'Cannot find the file : ' . $image );
		list( $this->width, $this->height, $type ) = getimagesize( $image );
		if ( $type === null ) throw new InvalidFileTypeException( 'Cannot open image' );
		switch ( $type ){
			case IMAGETYPE_GIF : 
				$this->type   = 'gif';
				$this->source = imagecreatefromgif( $image );
				break;
			case IMAGETYPE_JPEG : 
				$this->type   = 'jpeg';
				$this->source = imagecreatefromjpeg( $image );
				break;
			case IMAGETYPE_PNG : 
				$this->type   = 'png';
				$this->source = imagecreatefrompng( $image );
				break;
			default :
				throw new InvalidFileTypeException( 'Image is not a known format( jpg, gif, png )' );
		}
	}
	
	/* 
	 * Getters
	 * ----------------------------------------- */
	
	/** Returns the current width of the image
	 * 
	 * @return int
	 */
	public function getWidth(){
		return $this->width;
	}
	
	/** Returns the current height of the image
	 * 
	 * @return int
	 */
	public function getHeight(){
		return $this->height;
	}
	
	/** Returns the current type of the image
	 * 
	 * @return string : jpeg / png / gif
	 */
	public function getType(){
		return $this->type;
	}
	
	/** Returns the current image resource
	 * 
	 * @return resource
	 */
	public function resource(){
		return $this->source;
	}
	
	/* 
	 * Save
	 * ----------------------------------------- */
	
	/** Save the image to a file
	 * 
	 * @param string $file     : file to save to
	 * @param string $type     : optional, the file extension will be used to check the type ( jpeg / png / gif )
	 * @param integer $quality : optional, default will be used if none is provided ( [0, 100] )
	 * 
	 * @return Image           : current Object
	 */
	public function saveAs( $file, $type = null, $quality = null ){
		if ( $type === null ){
			$ext = substr( $file, strlen( $file ) - 6 );
			if ( strpos( $file, '.jpg' ) !== false || strpos( $file, '.jpeg' ) !== false ){
				$type = 'jpeg';
			} elseif ( strpos( $file, '.png' ) !== false ){
				$type = 'png';
			} elseif ( strpos( $file, '.gif' ) !== false ){
				$type = 'gif';
			} else throw new InvalidFileTypeException( 'Cannot determine the type of the file to saved.' );
		}
		if ( $quality === null ) $quality = self::$default_quality;
		if ( $type === 'jpeg' ){
			if ( !imagejpeg($this->source, $file, $quality) ) throw new NoPermissionException('Cannot save the file to : ' . $file);
		} elseif ( $type === 'gif' ){
			if ( imagegif($this->source, $file) ) throw new NoPermissionException('Cannot save the file to : ' . $file);;
		} elseif ( $type === 'png' ){
			if ( imagepng($this->source, $file) ) throw new NoPermissionException('Cannot save the file to : ' . $file);;
		} else throw new InvalidFileTypeException( 'Cannot determine the type of the file to be saved.');
		return $this;
	}
	
	/* 
	 * Conversion
	 * ----------------------------------------- */
	
	/** Resize the image based on a percentage of the original 
	 * 
	 * @param int $scale : percentage ( [0, 100] )
	 * 
	 * @return Image        : current object
	 */
	public function scale( $scale ){
		return $this->resize( $this->width * $scale/100, $this->height * $scale/100, false );
	}
	
	/** Resize the image based on width and height
	 * 
	 * @param int $width      : width of the resized image in pixels
	 * @param int $height     : height of the resized image in pixels
	 * @param bool $constrain : if set to true, the proportions ( image / height )of the original image will be kept
	 * @param bool $sharpen   : set to true in order to sharpen image, but also extend the process of conversion. 
	 * 
	 * @return Image          : current object
	 */
	public function resize( $width, $height, $constrain = true, $sharpen = true ){
		/* Constrain proportions ( based on height/width ) */
		if ( $constrain ){
			$xRatio	= $width / $this->width;
			$yRatio	= $height / $this->height;
			if ( $xRatio * $this->height < $height ){ 
				$height = ceil( $xRatio * $this->height );// Resize the image based on width
			} else { 
				$width  = ceil( $yRatio * $this->width ); // Resize the image based on height
			}
		}
		/* Create new image */
		$new_image = imagecreatetruecolor( $width, $height );
		/* Set up transparency if this is a gif or png */
		if ( $this->type === 'png' || $this->type === 'gif' ){
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
		}
		/* Copy image content to new image and resize */
		imagecopyresampled( $new_image, $this->source, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		/* Sharpen image */
		if ( $sharpen ){
			/* Sharpen the image based the difference between the original size and the final size */
			$sharpness	= $this->findSharp( $this->width, $width );
			$sharpenMatrix	= array(
				array(-1, -2, -1),
				array(-2, $sharpness + 12, -2),
				array(-1, -2, -1)
			);
			imageconvolution( $new_image, $sharpenMatrix, $sharpness, 0 );
		}
		ImageDestroy( $this->source );
		$this->width = $width;
		$this->height = $height;
		$this->source = $new_image;
		return $this;
	}
	
	/** Conversion helper, used to find the sharpness
	 * 
	 * @param integer $orig  : the original size of the image
	 * @param integer $final : the final size of the image
	 * 
	 * @return integer       : the sharpness value
	 */
	private function findSharp($orig, $final){
		$final = $final * (750.0 / $orig);
		$a = 52;
		$b = -0.27810650887573124;
		$c = .00047337278106508946;
		$result = $a + $b * $final + $c * $final * $final;
		return max(round($result), 0);
	}
	
	/* 
	 * Merge
	 * ----------------------------------------- */
	
	/** Merge another image over this one at a specified position and opacity
	 * 
	 * @param Image $image
	 * @param integer $this_x       : optional, X-coordinate to merge the image on, default = 0
	 * @param integer $this_y       : optional, Y-coordinate to merge the image on, default = 0
	 * @param integer $image_x      : optional, X-coordinate of the merging image, default = 0
	 * @param integer $image_y      : optional, Y-coordinate of the merging image, default = 0
	 * @param integer $image_width  : optional, width of the merging image, default = 0
	 * @param integer $image_height : optional, height of the merging image, default = 0
	 * @param omteger $opacity      : optional, the opacity to merge at, default = 50 ( [ 0, 100 ] )
	 * 
	 * @return Image               : current object
	 */
	public function mergeWith( Image $image, $this_x = 0, $this_y = 0, $image_x = 0, $image_y = 0, $image_width = null, $image_height = null, $opacity = 50 ){
		if ( $image_width === null ) $image_width = $image->getWidth();
		if ( $image_height === null ) $image_height = $image->getHeight();
		imagecopymerge(
			$this->source,
			$image->resource(),
			$this_x,
			$this_y,
			$image_x,
			$image_y,
			$image_width,
			$image_height,
			$opacity
		);
		return $this;
	}
	
	/* 
	 * Destruct
	 * ----------------------------------------- */
	
	/** Free memory
	 */
	public function close(){
		ImageDestroy( $this->source );
	}
	
	/** Destruct
	 */
	public function __destruct(){
		$this->close();
	}
	
}
