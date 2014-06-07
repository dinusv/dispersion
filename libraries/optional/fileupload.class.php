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
 * @ingroup libraries
 * @brief Html form file upload manager.
 */
class FileUpload extends Dispersion{
	
	const
		/** Maximum size defined by ini file error
		 */
		INI_MAX_SIZE  = 0,
		/** Maximum size defined by form error
		 */
		FORM_MAX_SIZE = 1,
		/** Incomplete / Interrupted upload
		 */
		INCOMPLETE    = 2,
		/** File required, yet field was empty
		 */
		REQUIRED      = 3,
		/** File exceeds the set maximum size
		 */
		MAX_SIZE      = 4,
		/** File is smaller than the set minimum size
		 */
		MIN_SIZE      = 5,
		/** Invalid file type
		 */
		TYPE          = 6,
		/** Unknown upload file problem
		 */
		UNKNOWN       = 7;

	/* 
	 * Restriction fields
	 * ----------------------------------------- */
	 
	private
		/** 
		 * @var $restrict_size_max
		 * int : Restrict maximum size of uploaded files in bytes
		 */
		$restrict_size_max,
		
		/** 
		 * @var $restrict_size_min
		 * int : Restrict minimum size of uploaded files in bytes
		 */
		$restrict_size_min,
		
		/** 
		 * @var $restrict_type
		 * array : Restrict allowed types of uploaded files
		 */
		$restrict_type,
		
		/** 
		 * @var $required_files
		 * array : Add required fields
		 */
		$required_files;
	
	/* 
	 * Upload succesfull
	 * ----------------------------------------- */
	 
	public
		/** 
		 * @var $upload_success
		 * array : Files that have been uploaded succesfully
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
	 * @param int $min
	 * @param int $max
	 */
	public function restrictSize( $min = null, $max = null ){
		$this->restrict_size_min = $min;
		$this->restrict_size_max = $max;
		return $this;
	}
	
	/** Restrict file type
	 * 
	 * @param $type string/array : type of files allowed
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
	 * @param string-array $file : file names that are required
	 * 
	 * @return current object
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
