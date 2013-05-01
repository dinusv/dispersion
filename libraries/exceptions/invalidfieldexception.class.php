<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/** Thrown when trying to access an index of an array that is not set.
 *
 * @license   : http://dispersion.inevy.com/license
 * @namespace : exceptions
 * @file      : libraries/exceptions/invalidfieldexception.class.php
 * @version   : 1.0
 */
 
class InvalidFieldException extends Exception{
	
	protected static
		$messages = array(
			FormValidation::REQUIRED       => 'This field is required',
			FormValidation::MINIMUM_LENGTH => 'Field must be at least %required characters long',
			FormValidation::MAXIMUM_LENGTH => 'Field must be maximum %required characters long',
			FormValidation::RANGE_LENGTH   => 'Field must be between %required characters long',
			FormValidation::PREG_MATCH     => 'Field does not match requirements',
			FormValidation::EMAIL          => 'Field must be a valid email address',
			FormValidation::EMAIL_MULTIPLE => 'Field must contain valid email adresses',
			FormValidation::NUMBER         => 'Field must be a valid number',
			FormValidation::NUMERIC        => 'Field must be numeric',
			FormValidation::GREATER_THAN   => 'Value must be greater than %required',
			FormValidation::LESS_THAN      => 'Value must be less than %required',
			FormValidation::EQUALS         => 'Field must match %required value',
			FormValidation::EQUALS_FIELD   => 'Field must match %required field',
			FormValidation::ALPHA          => 'Field must contain only letters'
		);
	
	private
		/** Name of the field the exception was thrown for
		 * 
		 * @var string
		 */
		$field_name;
	
	/** Constructor
	 * 
	 * @param string $message    : [optional] message to output
	 * @param string $field_name : [optional] name of the field the exception was thrown for
	 * @see php exception
	 */
	public function InvalidFieldException( $message_code, $field_name = null, $required_value = '', $code = 0, Exception $previous = null) {
		$this->field_name = $field_name;
		if ( $field_name === NULL )
			$field_name = '';
		parent::__construct( self::generateMessage($message_code, $field_name, $required_value ), $code, $previous );
	}
	
	/** Generates form validation message according to the messagecode given
	 * 
	 * @param string $message_code   : code for the message
	 * @param string $field_name     : [optional] name of the field the exception was thrown for
	 * @param string $required_value : [optional] value required for the field
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