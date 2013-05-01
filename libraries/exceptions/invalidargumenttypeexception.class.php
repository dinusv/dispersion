<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/** Thrown when a file upload did not succeed for some reason.
 *
 * @license   : http://dispersion.inevy.com/license
 * @namespace : exceptions
 * @file      : libraries/exceptions/invalidargumenttypeexception.class.php
 * @version   : 1.0
 */
 
class InvalidArgumentTypeException extends Exception{
	
	/** Constructor
	 * 
	 * @param array $message  : you can give the types of arguments supported as an array to include into the message.
	 * @param string $message : @see php exception
	 */
	public function InvalidArgumentTypeException($message = array(), $code = 0, Exception $previous = null) {
		if ( is_array( $message ) ){
			$msg = '';
			foreach( $message as $value ){
				if ( $msg !== '' ) $msg .= ', ';
				$msg .= $value;
			}
			$msg = 'Allowed types : ' . $msg;
		} else $msg = $message;
		parent::__construct( $msg, $code, $previous );
	}
}