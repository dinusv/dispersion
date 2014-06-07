<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of the Inevy Framework.         |
| http://inevy.com                                  |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */
 
/**
 * @version 1.1
 * @author DinuSV
 */

/** 
 * @ingroup exceptions
 * @brief Thrown when a file upload did not succeed for the reason present within the thrown exception..
 */
class UploadFileException extends Exception{
	
	protected static
		/**
		 * @var $messages
		 * array : map of upload-error-types and their messages
		 */
		$messages = array(	
			FileUpload::INI_MAX_SIZE  => 'File exceeds the maximum size',
			FileUpload::FORM_MAX_SIZE => 'File exceeds the maximum size supported by the form',
			FileUpload::INCOMPLETE    => 'File was not fully uploaded. Please try uploading the file again',
			FileUpload::REQUIRED      => 'No file specified at %name',
			FileUpload::MAX_SIZE      => 'File exceeds %required',
			FileUpload::MIN_SIZE      => 'File is smaller than %required',
			FileUpload::TYPE          => 'File must be of type %required',
			FileUpload::UNKNOWN       => 'There was a problem uploading the file'
		);
	
	private
		/** 
		 * @var $field_name
		 * string : Name of the field the exception was thrown for
		 */
		$field_name;
	
	/** Constructor
	 * 
	 * @param $message_code string   : [optional] message to output
	 * @param $field_name string     : [optional] name of the field the exception was thrown for
	 * @param $required_value string : [optional]
	 * @param $previous Exception    : [optional]
	 * @param $code int              : [optional]
	 * @see php exception
	 */
	public function UploadFileException( $message_code, $field_name = null, $required_value = '', $code = 0, Exception $previous = null) {
		$this->field_name = $field_name;
		if ( $field_name === NULL )
			$field_name = '';
		parent::__construct( self::generateMessage($message_code, $field_name, $required_value ), $code, $previous );
	}
	
	/** Generates form validation message according to the messagecode given
	 * 
	 * @param $message_code string   : code for the message
	 * @param $field_name string     : [optional] name of the field the exception was thrown for
	 * @param $required_value string : [optional] value required for the field
	 * 
	 * @return string
	 */
	 public static function generateMessage( $message_code, $field_name = '', $required_value = '' ){
		if ( isset( self::$messages[$message_code] )){
			return str_replace(array('%name', '%required'), array($field_name, $required_value), self::$messages[$message_code]);
		} else
			return '';
	}
	
	
	/** Get the field the exception was trown for
	 * 
	 * @return string
	 */
	public function getFieldName(){
		return $this->field_name;
	}
}