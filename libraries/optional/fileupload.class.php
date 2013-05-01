<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/** Main error handling class
 *
 * @license   : http://dispersion.inevy.com/license
 * @namespace : optional
 * @parent    : Dispersion
 * @file      : libraries/optional/error.class.php
 * @version   : 1.0
 */

class FileUpload extends Dispersion{
	
	const
		INI_MAX_SIZE  = 0,
		FORM_MAX_SIZE = 1,
		INCOMPLETE    = 2,
		REQUIRED      = 3,
		MAX_SIZE      = 4,
		MIN_SIZE      = 5,
		TYPE          = 6,
		UNKNOWN       = 7;
	
	/* 
	 * Restriction fields
	 * ----------------------------------------- */
	 
	private
		/** Restrict maximum size of uploaded files in bytes
		 * 
		 * @var integer
		 */
		$restrict_size_max,
		
		/** Restrict minimum size of uploaded files in bytes
		 * 
		 * @var integer
		 */
		$restrict_size_min,
		
		/** Restrict allowed types of uploaded files
		 * 
		 * @var array
		 */
		$restrict_type,
		
		/** Add required fields
		 * 
		 * @var array
		 */
		$required_files;
	
	/* 
	 * Upload succesfull
	 * ----------------------------------------- */
	 
	public
		/** Files that have been uploaded succesfully
		 * 
		 * @var array
		 */
		$upload_success = array();
	
	/** Constructor 
	 */
	public function FileUPload(){
		parent::__construct();
		$this->restrict_size_max = null;
		$this->restrict_size_min = null;
		$this->restrict_type = array();
	}
	
	/* 
	 * User configuration methods
	 * ----------------------------------------- */
	 
	/** Restrict file size in bytes
	 * 
	 * @param integer $min
	 * @param integer $max
	 */
	public function restrictSize( $min = null, $max = null ){
		$this->restrict_size_min = $min;
		$this->restrict_size_max = $max;
		return $this;
	}
	
	/** Restrict file type
	 * 
	 * @param string/array $type : type of files allowed
	 */
	public function restrictType( $type ){
		if ( is_array($type) ){
			$this->restrict_type = array_merge( $this->restrict_type, $type );
		} else {
			$this->restrict_type = array_merge( $this->restrict_type, array( $type ) );
		}
		return $this;
	}
	
	/** Files that are required for upload
	 * 
	 * @param string/array $file : file names that are required
	 */
	public function requiredFiles( $file = array() ){
		if ( is_array($file) ){
			$this->required_files = array_merge( $this->required_files, $file );
		} else {
			$this->required_files[] = $file;
		}
		return $this;
	}
	
	/** Checks if the file returned any errors upon uploading, and checks if the file matches the restrictions
	 * 
	 * @param string $filename : the filename to be checked
	 * 
	 * @return boolean         : true if the file is valid and uploaded, false otherwise
	 * 
	 * @throws UploadFileException
	 * @throws IncompleteActionException
	 * @throws InvalidFileTypeException
	 * @throws EmptyFieldException
	 */
	public function checkFile( $filename ){
		/* check upload errors */
		switch( $_FILES[$filename]['error']){
			case UPLOAD_ERR_INI_SIZE : 
				throw new UploadFileException( FileUpload::INI_MAX_SIZE, $filename );
			case UPLOAD_ERR_FORM_SIZE : 
				throw new UploadFileException( FileUpload::FORM_MAX_SIZE, $filename );
			case UPLOAD_ERR_PARTIAL : 
				throw new UploadFileException( FileUpload::INCOMPLETE, $filename );
			case UPLOAD_ERR_NO_FILE : 
				if ( $this->required_files !== null )
					foreach( $this->required_files as $reqfile ){
						if ( $reqfile === $filename ){
							throw new UploadFileException( FileUpload::REQUIRED, $filename );
						}
					}
				break;
			default: 
				/* check upload restrictions */
				if ( $this->restrict_size_max !== null ){
					if ( $_FILES[$filename]['size'] > $this->restrict_size_max ){
						throw new UploadFileException( FileUpload::MAX_SIZE, $filename );
					}
				}
				if ( $this->restrict_size_min !== null ){
					if ( $_FILES[$filename]['size'] < $this->restrict_size_min ){
						throw new UploadFileException( FileUpload::MIN_SIZE, $filename );
					}
				}
				if ( count($this->restrict_type) > 0 ) {
					foreach( $this->restrict_type as $type )
						if ( $_FILES[$filename]['type'] == $type ) return true;
					/* If the type of the file is not allowed, build a string of types allowed and throw them as exceptions */
					$types = '';
					foreach( $this->restrict_type as $type ){
						if ( $types !== '' ) $types .= ', ';
						$types .= $type;
					}
					throw new UploadFileException( FileUpload::TYPE, $filename );
				}
				return true;
		}
		
	}
	
	/** Move uploaded file(s)
	 * 
	 * @param string $location : the location to upload the file
	 * @param null   $filename : all the files will be uploaded
	 *        array  $filename : key => name of the field, value => name of the uploaded file
	 * 
	 * @throws UploadFileException
	 */
	public function moveTo( $location, $filename = null ){
		if ( $filename === null ) { //move all uploaded files
			foreach( $_FILES as $key => $value ){
				$this->moveTo( $location, array( $key => $value ) );
			}
		} else if ( is_array($filename ) ){
			foreach( $filename as $key => $value ){
				if ( $this->checkFile($key) ){
					if ( is_uploaded_file( $_FILES[$key]['tmp_name'] ) ){
						if ( $value === '' ) $uploadname = $_FILES[$key]['name'];
						else $uploadname = $value;
						if ( move_uploaded_file( $_FILES[$key]['tmp_name'], $location . $uploadname ) ){
							$this->upload_success[$key] = $_FILES[$key]['name'];
						} else {
							throw new UploadFileException( FileUpload::UNKNOWN, $filename );
						}
					}
				}
			}
		} else {
			throw new InvalidArgumentTypeException("FileUpload::moveTo method requires second argument to be a map(array of keys and values).");
		}
	}
	
}
