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
 * @version 1.1
 * @author DinuSV
 */

/** 
 * @ingroup exceptions
 * @brief Thrown when a file upload did not succeed for some reason.
 */
class InvalidArgumentTypeException extends Exception{
	
	/** Constructor
	 * 
	 * @param array-string $message  : you can give the types of arguments supported as an array to include into the message.
	 * @param int $code              : [optional]
	 * @param Exception $previous    : [optional]
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