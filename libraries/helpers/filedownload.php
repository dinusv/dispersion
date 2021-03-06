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
 * @brief Outputs file contents directly to browser, hiding the actual path of the file. 
 * 
 * The use of this class comes into play when one wants to hide the actual path to the file or restrict
 * access to it by unknown sources.
 * 
 * The constructor takes 2 argument : the path to the file, and the file name to display when downloading.
 * If the second argument is not given, the name displaed will be hte same as the original name. The 
 * `output()` function will display the file to the browser for download.
 * 
 * @code
 * $dl = new FileDownload( ROOT . '/myarchive.zip', 'My Collection.zip' );
 * $dl->output();
 * @endcode
 * 
 * You can also set the name of the file after the object was created, using `setFileName()` :
 * 
 * @code
 * $dl = new FileDownload( ROOT . '/myarchive.zip' );
 * $dl->setFileName( 'My Collection.zip' )->output();
 * @endcode
 * 
 * By default, the mime type is deducted from the file extension ( The mime type is the value the browser 
 * needs in order to know what to do with the file ). The following extensions are known : zip, pdf, doc,
 * xls, ppt, exe, gif, png, jpg, jpeg, mp3, wav, mpeg, mpg, mpe, mov, avi. In order to add more extensions 
 * and their mime type, you can use the `addExtension()` function, and in order to remove them, you can
 * use the `removeExtension()` function : 
 * 
 * @code
 * $dl = new FileDownload( 'myarchive.zip' );
 * $dl->removeExtension( 'zip' );
 * $dl->addExtension( 'zip', 'application/zip' );
 * @endcode
 * 
 * You can restrict download to users by using the `allowedReferrer` method :
 * 
 * @code
 * $dl = new FileDownload( 'myarchive.zip' );
 * $dl->addAllowedReferrer( 'http://mydomain.com/download-section' );
 * @endcode
 * 
 * To display a message for an unallowed referrer, you need to catch the NoPermissionsException from the 
 * `output()` method.
 * 
 * @code
 * try{
 *     $dl = new FileDownload( 'myarchive.zip' );
 *     $dl->addAllowedReferrer( 'http://mydomain.com/download-section' );
 *     $dl->output();
 * } catch ( NoPermissionsException $e ){
 *     echo 'You are not allowed to download this file';
 * }
 * @endcode
 * 
 * Another exception to look for is the InvalidArgumentTypeException in the output() method, which is thrown
 * when the file-type doesn't match the available extensions.
 * 
 * @code
 * $dl = new FileDownload( 'myarchive.abc' );   
 * try{
 *     $dl->output();
 * } catch ( InvalidArgumentTypeException $e ){
 *     echo $this->debug->exception($e);
 * }
 * @endcode
 * 
 */
class FileDownload{
	
	private
		/** 
		 * @var $allowed_referrers 
		 * array : Allowed referrers to download this file
		 */
		$allowed_referrers = array(),
		
		/** 
		 * @var $file_name 
		 * string : The name of the file to be displayed when downloading
		 */
		$file_name = null,
		
		/** 
		 * @var $file
		 * string : Full path to the file to be downloaded
		 */
		$file = null;
		
	public
		/**
		 * @var $extensions
		 * array : Allowed extensions and their mime type
		 */
		$extensions;
	
	/** Constructor
	 * 
	 * @param string $file      : the full path to the file
	 * @param string $file_name : [optional] the name of the file to be displayed when downloading
	 */
	public function FileDownload( $file, $file_name = null ){
		$this->extensions = array(
			'zip' => 'application/zip',

			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			'exe' => 'application/octet-stream',

			'gif' => 'image/gif',
			'png' => 'image/png',
			'jpg' => 'image/jpeg',
			'jpeg'=> 'image/jpeg',

			'mp3' => 'audio/mpeg',
			'wav' => 'audio/x-wav',

			'mpeg'=> 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'mov' => 'video/quicktime',
			'avi' => 'video/x-msvideo'
		);
		$this->changeFile( $file );
		$this->setFileName( $file_name );
	}
	
	/** Add an allowed extension and it's mime type
	 * 
	 * @param string $ext   : extension name
	 * @param string $value : extension mime type
	 * 
	 * @return FileDownload : current object
	 */
	public function addExtension( $ext, $value ){
		$this->extensions[$ext] = $value;
		return $this;
	}
	
	/** Remove an allowed extension from the list.
	 * 
	 * @note To remove all extensions, the extensions field is public, so just reset it : $this->extensions = array()
	 * 
	 * @param string $ext   : the extension name to be removed
	 * 
	 * @return FileDownload : current object
	 */
	public function removeExtension( $ext ){
		unset( $this->extensions[$ext] );
		return $this;
	}
	
	/** Add an allowed referrer to download the file
	 * 
	 * @note If none is set, all referrers will be able to download the file
	 * 
	 * @param string $referrer : the allowed referrer
	 * 
	 * @return FileDownload    : current object
	 */
	public function addAllowedReferrer( $referrer ){
		$this->allowed_referrers[] = strtolower($referrer);
		return $this;
	}
	
	/** Set the name of the file to display for the download
	 * 
	 * @param string $file_name : the file name to set
	 * 
	 * @return FileDownload    : current object
	 */
	public function setFileName( $file_name ){
		$this->file_name = $file_name;
		return $this;
	}
	
	/** Change the file to be downloaded
	 * 
	 * @param string $file  : the full path to the file
	 * 
	 * @throws FileNotFoundException
	 * 
	 * @return FileDownload : current object
	 */
	public function changeFile( $file ){
		if ( !file_exists( $file ) ) throw new FileNotFoundException( "The file requested cannot be found.");
		$this->file = $file;
		return $this;
	}
	
	/** Set the headers and output the selected file
	 * 
	 * @throws NoPermissionsException
	 * @throws InvalidArgumentTypeException
	 */
	public function output(){
		/* get referer info */
		if ( count( $this->allowed_referrers ) > 0  ){
			if ( !isset( $_SERVER['HTTP_REFERER'] ) ) throw new NoPermissionsException( "Referrer not found" );
			if ( ! in_array( $this->allowed_referrers, $_SERVER['HTTP_REFERER'] ) ) throw new NoPermissionsException( "Downloading this file is not allowed.");
		}
		/* get file info */
		$file_size = filesize( $this->file );
		$file_name = basename( $this->file );
		$file_ext  = strtolower( substr( strrchr( $file_name, '.' ), 1 ) );
		/* get mime type */
		if ( !array_key_exists( $file_ext, $this->extensions ) ) {
			throw new InvalidArgumentTypeException( "Not allowed file type." );
		}
		if ( $this->extensions[$file_ext] === '' ){
			$m_type = "application/force-download";
			/* mime type is not set, get from server settings */
			if (function_exists('mime_content_type')) {
				$m_type = mime_content_type($file_path);
			} else if ( function_exists('finfo_file') ){
				$finfo = finfo_open( FILEINFO_MIME );
				$m_type = finfo_file( $finfo, $file_path );
				finfo_close($finfo);  
			}
		} else {
			$m_type = $this->extensions[$file_ext];
		}
		if ( $this->file_name !== null ){
			$file_name = $this->file_name;
		}
		/* set headers */
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: " . $m_type );
		header("Content-Disposition: attachment; filename=\"" . $file_name . "\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . $file_size );
		$readfile = @readfile( $this->file );
		if ( $readfile === false )
			throw new NoPermissionsException( "Cannot read the file requested." );
	}
	
}
