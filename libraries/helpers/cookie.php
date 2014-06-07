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
 * @brief User cookie wrapper.
 */
class Cookie{
	
	public
		/** 
		 * @var $name
		 * string : Name of the cookie
		 */
		$name,
		
		/** 
		 * @var $value
		 * string : Value of the cookie
		 */
		$value,
		
		/** 
		 * @var $expire
		 * int : Time the cookie expires in ( UNIX timestamp )
		 */
		$expire,
		
		/** 
		 * @var $path
		 * string : The path on the server in which the cookie will be available on.
		 * Set to '/' to make it available through the entire domain.
		 */
		$path,
		
		/** 
		 * @var $domain
		 * string : The domain that the cookie will be available to.
		 */
		$domain,
		
		/** 
		 * @var $secure
		 * bool : Transmit the cookie only if the connection is a secure HTTPS connection from the client.
		 * When set to true, the cookie will be set only if a secure connection exists.
		 */
		$secure,
		
		/** 
		 * @var $httponly
		 * bool : The cookie will only be made accessible through the HTTP protocol. The cookie cannot be
		 * accessed through scripting languages, like javascript.
		 */
		$httponly;
	
	/** Constructor
	 * 
	 * @param string $name                 : the name of the cookie
	 * @param string $value [optional]     : value of the cookie
	 * @param integer $expire [optional]   : time the cookie will expire
	 * @param string $path [optional]      : the path the server in which the cookie will be available on
	 * @param string $domain [optional]    : the domain the cookie is available to
	 * @param boolean $secure [optional]   : transmit the cookie through a secure https connection
	 * @param boolean $httponly [optional] : make the cookie accessible only through the HTTP protocol
	 */
	public function Cookie( $name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null ){
		if ( !is_string( $name ) ) throw new InvalidArgumentTypeException( 'Cannot set cookie name.' );
		$this->name = $name;
		if ( $value !== null ){
			$this->value = $value;
			$this->expire = $expire;
			$this->path = $path;
			$this->domain = $domain;
			$this->secure = $secure;
			$this->httponly = $httponly;
		} else {
			if ( isset( $_COOKIE[$name] ) ) 
				$this->value = $_COOKIE[$name];
			else $this->value = '';
		}
	}
	
	/** Set the cookie with the setcookie function
	 * 
	 * @return boolean : false if output exists prior to calling this function, true if the cookie has been saved
	 */
	public function save(){
		return setcookie( $this->name, $this->value, $this->expire, $this->path, $this->domain, $this->secure, $this->httponly );
	}
	
	/** Unset the cookie by calling the setcookie function
	 * 
	 * @return boolean : false if output exists prior to calling this function, true if the cookie has been saved
	 */
	public function delete(){
		return setcookie( $this->name, "", time() - 3600 );
	}
	
}
