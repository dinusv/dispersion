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
 * @version 1.2
 * @author DinuSV
 */

/** 
 * @ingroup libraries
 * @brief Minimizes form validation code.
 * 
 * The FormValidation library is a quick and easy way to validate the fields of a form. The process 
 * includes selecting the field, chaining the necessary methods in a try-catch block, and displaying
 * the exception. 
 * 
 * A list of the validation functions supported by this library :
 * 
 * @code
 * $fv = new FormValidation('post'); // Form submission type: post/get
 * $fv->field('field_name'); // field to validate
 * $fv->required(); // set this field as required
 * $fv->minLength(15); // minimum required characters
 * $fv->maxLength(30); // maximum allowed characters
 * $fv->rangeLength(15, 30); // field must have length between this interval
 * $fv->pregMatch('/abc/'); // match field against regular expression
 * $fv->email(); // check if this field is a valid email
 * $fv->multipleEmail(); // emails separated by comma
 * $fv->number(); // separators and numbers allowed
 * $fv->numeric(); // only numbers allowed
 * $fv->greaterThan(20); // field >= 20
 * $fv->lessThan(20);  // field =< 20
 * $fv->equals('abcd');  // field must have the specified value
 * $fv->equalsField('password_confirm'); // field must be equal to another field
 * $fv->alpha(); // allow only letters
 * $fv->trim(); // trim field value
 * $fv->toEntities(); // convert this fields entities
 * $fv->toXmlEntities(); // convert ( <, >, &, '' ) to entities
 * @endcode
 * 
 * When initializing the library, the constructor takes one optional argument representing the method
 * the form was submited by ( post / get ). The default value is post. If you're autoloading this 
 * library, and have a form submitted by get, make sure you change the value with `setMethod()`.
 * 
 * @code
 * $this->formvalidation->setMethod('get');
 * @endcode
 * 
 * This library can validate either selected field using the `field()` method, or values using the
 * `value()` method. All validation functions throw an InvalidFieldException if the field does not meet
 * specified criteria. The message can be received from the exception by using the `getMessage() function,
 * and the field name using getFieldName();
 * 
 * @code
 * try{
 *    $this->formvalidation->field('name')->required();
 * } catch ( InvalidFieldException $e ) {
 *    echo '<b>' . $e->getFieldName() . ' : </b>' . $e->getMessage();
 * }
 * @endcode
 * 
 * The exception is thrown at the first invalid field met. This makes for printing messages one by one, until
 * all fields have been filled properly. To output all messages within one validation, when selecting the
 * field, 'fieldStore()' can be used instead, making the messages store into an array that can be further
 * retrieved using `getMessages()`. Similarly for values, the `valueStore() method does the same thing. The
 * received messages are stored in a 2-key array, where the first key is the fields name, and the second is
 * the message counter for the field.
 * 
 * @code
 * try{
 *     $this->formvalidation->fieldStore('name')->required();
 *     $this->formvalidation->fieldStore('email')->required()->email();
 * } catch ( InvalidFieldException $e ) {
 *     $messages = $this->formvalidation->getMessages();
 *     foreach ( $messages as $field => $fieldmsgs )
 *         foreach( $fieldmsgs as $message )
 *             echo '<b>' . $e->getFieldName() . ' : </b>' . $e->getMessage();
 * }
 * @endcode
 * 
 */
class FormValidation{
	
	const
		/** Required field
		 */
		REQUIRED       = 0,
		/** Minimum characters required
		 */
		MINIMUM_LENGTH = 1,
		/** Maximum characters required
		 */
		MAXIMUM_LENGTH = 2,
		/** Both minimum and maximum characters required
		 */
		RANGE_LENGTH   = 3,
		/** Regular expression match
		 */
		PREG_MATCH     = 4,
		/** Email type field
		 */
		EMAIL          = 5,
		/** Multiple email type field
		 */
		EMAIL_MULTIPLE = 6,
		/** Only number-characters allowed
		 */
		NUMBER         = 7,
		/** Only number-characters and separator symbols allowed
		 */
		NUMERIC        = 8,
		/** Number field needs to be greater than value
		 */
		GREATER_THAN   = 9,
		/** Number field needs to be less than value
		 */
		LESS_THAN      = 10,
		/** Field equals value
		 */
		EQUALS         = 11,
		/** Field equals field
		 */
		EQUALS_FIELD   = 12,
		/** Field is only letters
		 */
		ALPHA          = 13,
		/** All form fields
		 */
		ALL_FIELDS     = 14;
	
	private
		/** 
		 * @var $method
		 * string : 'post'/'get'
		 */
		$method,
		
		/** 
		 * @var $current_field
		 * string : The field to be validated
		 */
		$current_field = null,
		
		/** 
		 * @var $current_field_val
		 * mixed : The field value
		 */
		$current_field_val = null,
		
		/** 
		 * @var $messages_store
		 * bool : Store messages and output the messages at the end or right after an invalid field has been
		 * identified. True to store, false otherwise.
		 */
		$messages_store = false,
		
		/** 
		 * @var $messages
		 * array : Messages to be stored
		 */
		$messages;
	
	/** Constructor
	 *
	 * @param string $method : [optional] get/post
	 */
	public function FormValidation( $method = 'post' ){
		$this->method = strtolower( $method );
	}
	
	/** Set the method of the form get/pos
	 *
	 * @param string $method
	 *
	 * @return FormValidation : current object
	 */
	public function setMethod( $method ){
		$this->method = $method;
		return $this;
	}
	
	/** Set the form field to be validated
	 *
	 * @param string $field : the form field name
	 *
	 * @return FormValidation : current object
	 */
	public function field( $field ){
		$this->current_field = $field;
		$this->messages_store = false;
		$this->messages = array();
		if ( $this->method === 'post' ){
			$this->current_field_val = $_POST[$this->current_field];
		} else 
			$this->current_field_val = $_GET[$this->current_field];
		return $this;
	}
	
	/** Set the form field to be validated. All the messages will be stored
	 * and no exceptions thrown
	 *
	 * @param string $field : the form field name
	 *
	 * @return FormValidation : current object
	 */
	public function fieldStore( $field ){
		$this->field( $field )->messages_store = true;
		$this->messages = array();
		return $this;
	}
	
	/** Set the value to be validated.
	 *
	 * @param mixed $val
	 *
	 * @return FormValidation : current object
	 */
	public function value( $val ){
		$this->messages_store = false;
		$this->messages = array();
		$this->current_field = '';
		$this->current_field_val = $val;
		return $this;
	}
	
	/** Set the value to be validated. All messages will be stored and no exceptions
	 * thrown.
	 *
	 * @param mixed $val
	 *
	 * @return FormValidation : current object
	 */
	public function valueStore( $val ){
		$this->value( $val )->message_store = true;
		$this->messages = array();
		return $this;
	}
	
	/** Get the value thats being validated
	 *
	 * @throws InvalidArgumentTypeException
	 *
	 * @return mixed
	 */
	public function getValue(){
		if ( $this->current_field === null && $this->current_field_val === null ) 
			throw new InvalidArgumentTypeException( "No field has been specified." );
		return $this->current_field_val;
	}
	
	/** Helper method used by this class in order to either store a value or throw
	 * an exception in case a field is not valid
	 *
	 * @param string $message_code : the message to be stored or reported
	 * @param string $required_value : [optional]
	 *
	 * @throws InvalidFieldException
	 */
	protected function report( $message_code, $required_value = '' ){
		if ( $this->messages_store && $message_code != FormValidation::ALL_FIELDS ) 
			$this->messages[$this->current_field][] = 
				InvalidFieldException::generateMessage( $message_code, $this->current_field, $required_value );
		else throw new InvalidFieldException( $message_code, $this->current_field, $required_value );
	}
	
	/** Function validating all fields that have been stored.
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function validate(){
		if ( count( $this->messages ) ){
			$this->report( FormValidation::ALL_FIELDS );
		}
		return $this;
	}
	
	/** Get the received messages when validating this form
	 *
	 * @return array : messages that have been stored, or null otherwise
	 */
	public function getMessages(){
		if ( count( $this->messages ) )
			return $this->messages;
		else return null;
	}
	
	/** Set this field as required
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function required(){
		if ( trim( $this->getValue() ) === '' ){
			$this->report( FormValidation::REQUIRED );
		}
		return $this;
	}
	
	/** Set this fields minimum length
	 *
	 * @param integer $length
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function minLength( $length ){
		if ( trim( $this->getValue() ) !== '' )
			if ( strlen( $this->getValue() ) < $length ) {
				$this->report( FormValidation::MINIMUM_LENGTH, $length );	
			}
		return $this;
	}
	
	/** Set this fields maximum length
	 *
	 * @param integer $length
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function maxLength( $length ){
		if ( trim( $this->getValue() ) !== '' )
			if ( strlen( $this->getValue() ) > $length ) {
				$this->report( FormValidation::MAXIMUM_LENGTH, $length );
			}
		return $this;
	}
	
	/** Set this fields allowed length
	 *
	 * @param integer $min : the minimum length this field needs to have in order to be valid
	 * @param integer $max : the maximum length this field needs to have in order to be valid
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function rangeLength( $min, $max ){
		if ( trim( $this->getValue() ) !== '' )
			if ( strlen( $this->getValue() ) < $min || strlen( $this->getValue() ) > $max ) {
				$this->report( FormValidation::RANGE_LENGTH, $min . ' and ' . $max );
			}
		return $this;
	}
	
	/** Set this field to match a regexp expression
	 *
	 * @param string $regexp
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function pregMatch( $regexp ){
		if ( !preg_match( $regexp, $field ) ){
			$this->report( FormValidation::PREG_MATCH, $regexp );
		}
		return $this;
	}
	
	/** Set this field to be a valid email adress
	 *
	 * @throws InvalidFieldException
	 * 
	 * @return FormValidation : current object
	 */
	public function email(){
		if ( trim( $this->getValue() ) !== '' )
			if ( !self::isEmail( $this->getValue() ) ){
				$this->report( FormValidation::EMAIL, '' );
			}
		return $this;
	}
	
	/** Set this field to be valid email adresses separated by commas
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function multipleEmail(){
		if ( trim( $this->getValue() ) !== '' ){
			$emails = explode( ",", $this->getValue() );
			foreach( $emails as $email ){
				if ( !self::isEmail( trim( $email ) ) ){
					$this->report( FormValidation::EMAIL_MULTIPLE, '' );
				}
			}
		}
		return $this;
	}
	
	/** Set this field to be a valid number, can be separated by spaces, commas, underscores
	 * lines, etc.
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function number(){
		if ( trim( $this->getValue() ) !== '' )
			if ( !self::isNumber( $this->getValue() ) ){
				$this->report( FormValidation::NUMBER, '' );
			}
		return $this;
	}
	
	/** Set this field to be a numeric value
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function numeric(){
		if ( trim( $this->getValue() ) !== '' )
			if ( is_numeric( $this->getValue() ) ){
				$this->report( FormValidation::NUMERIC, '' );
			}
		return $this;
	}
	
	/** Set this field to be greater than a given value
	 *
	 * @param integer $value
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function greaterThan( $value ){
		if ( trim( $this->getValue() ) !== '' ){
			if ( is_numeric( $this->getValue() ) ){
				if ( floatval( $this->getValue() ) <= $value )
					$this->report( FormValidation::GREATER_THAN, $value );
			} else {
				$this->report( FormValidation::NUMERIC, '' );
			}
		}
		return $this;
	}
	
	/** Set this field to be less than a given value
	 *
	 * @param integer $value
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function lessThan( $value ){
		if ( trim( $this->getValue() ) !== '' ){
			if ( is_numeric( $this->getValue() ) ){
				if ( floatval( $this->getValue() ) >= $value )
					$this->report( FormValidation::LESS_THAN, $value ); 
			} else {
				$this->report( FormValidation::NUMERIC, '' );
			}
		}
		return $this;
	}
	
	/** Set this field to be equal with another value
	 *
	 * @param mixed $value
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function equals( $value ){
		if ( $this->getValue() !== $value )
			$this->report( FormValidation::EQUALS, $value );
		return $this;
	}
	
	/** Set this field to be equal to another field
	 *
	 * @param string $name : the name of the field
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function equalsField( $name ){
		if ( $this->method === 'post' )
			$value = $_POST[$name];
		else $value = $_GET[$name];
		if ( $this->getValue() !== $value )
			$this->report( FormValidation::EQUALS_FIELD, $name );
		return $this;
	}
	
	/** Allow only letters for this field
	 *
	 * @throws InvalidFieldException
	 *
	 * @return FormValidation : current object
	 */
	public function alpha(){
		if ( trim( $this->getValue() ) !== '' )
			if ( !preg_match( '/^[a-zA-Z]*$/i', $this->getValue() ) )
				$this->report( FormValidation::ALPHA, '' );
		return $this;
	}
	
	/** Trim this field
	 *
	 * @return FormValidation : current object
	 */
	public function trim(){
		$this->current_field_val = trim( $this->getValue() );
		return $this;
	}
	
	/** Convert this fields entities
	 *
	 * @param integer $constants
	 *
	 * @return FormValidation : current object
	 */
	public function toEntities( $constants = null ){
		if ( $constants !== null ){
			$this->current_field_val = htmlentities( $this->getValue(), $constants );
		} else {
			$this->current_field_val = htmlentities( $this->getValue() );
		}
		return $this;
	}
	
	/** Convert this fields xml entities ( <, >, &, " )
	 *
	 * @return FormValidation : currentt object
	 */
	public function toXmlEntities(){
		$this->current_field_val = str_replace( array( '<', '>', '&', '"' ), array( '&lt;', '&gt;', '&amp;', '&quot;' ), $this->getValue() );
		return $this;
	}
	
	/** Validates an email address
	 * Confitions : 
	 *   - false if no '@' symbol
	 *   - false if length before '@' string is smaller than 1 or bigger than 100
	 *   - false if length after  '@' string is smaller than 1 or bigger than 255
	 *   - false if before '@' string starts or ends with '.'
	 *   - false if before '@' string has 2 consecutive dots
	 *   - false if before '@' string has illegal chars unless unqoted
	 *   - false if after  '@' string contains illegal chars
	 *   - false if after  '@' string has 2 consecutive dots 
	 * 
	 * @param string $email
	 * 
	 * @return boolean      : true if email is valid, false otherwise
	 */
	public static function isEmail( $email ){
		
		$at_index = strpos( $email, "@");
	
		if ( $at_index === false ) return false;
	
		$domain = substr( $email, $at_index + 1 );
		$local  = substr( $email, 0, $at_index  );
		
		if ( strlen( $local  ) < 1 || strlen( $local ) > 64 ) return false;
		if ( strlen( $domain ) < 1 || strlen( $domain) > 255) return false;
	
		if ( $local[0] == '.' || $local[strlen($local) - 1] == '.' ) return false;
		if (  preg_match('/\\.\\./', $local) )                       return false;
		if ( !preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain) )        return false;
		if (  preg_match('/\\.\\./', $domain ) )                     return false;
		if ( !preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
		/* Check if local part is quoted */
		if ( !preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local) ) ) return false;
		return true;
	}
	
	/** Check if a parameter is a valid number
	 * 
	 * @param string $number : the value to check
	 * 
	 * @return boolean      : true if it's a number, false otherwise
	 */
	public static function isNumber( $number ){
		return ( preg_match( '/^[0-9\,\.\ \-]+$/i', $number ) );
	}
	
}