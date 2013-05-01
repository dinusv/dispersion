<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/**
 * @license   : http://dispersion.inevy.com/license
 * @namespace : helpers
 * @file      : libraries/helpers/image.class.php
 * @version   : 1.0
 */
 
class Image{
	
	private static
		/** Default quality to save the image as ( [0, 100] )
		 * 
		 * @var integer
		 */
		$default_quality = 90;
	
	private
		/** Type of the image ( jpeg, png, gif )
		 * 
		 * @var string
		 */
		$type,
		
		/** Image resource
		 * 
		 * @var resource
		 */
		$source,
		
		/** Width of the image
		 * 
		 * @var integer
		 */
		$height,
		
		/** Height of the image
		 * 
		 * @var integer
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
	 * @return integer
	 */
	public function getWidth(){
		return $this->width;
	}
	
	/** Returns the current height of the image
	 * 
	 * @return integer
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
	 * @param integer $scale : percentage ( [0, 100] )
	 * 
	 * @return Image        : current object
	 */
	public function scale( $scale ){
		return $this->resize( $this->width * $scale/100, $this->height * $scale/100, false );
	}
	
	/** Resize the image based on width and height
	 * 
	 * @param integer $width     : width of the resized image in pixels
	 * @param integer $height    : height of the resized image in pixels
	 * @param boolean $constrain : if set to true, the proportions ( image / height )of the original image will be kept
	 * @param boolean $sharpen   : set to true in order to sharpen image, but also extend the process of conversion. 
	 * 
	 * @return Image            : current object
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
	 * @param integer $thix_y       : optional, Y-coordinate to merge the image on, default = 0
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
