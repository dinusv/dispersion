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
 * @file      : libraries/helpers/cookie.php
 * @version   : 1.0
 */
 
class Cookie{
	
	public
		/** Name of the cookie
		 * 
		 * @var string
		 */
		$name,
		
		/** The value of the cookie
		 * 
		 * @var string
		 */
		$value,
		
		/** The time the cookie expires. This is a UNIX timestamp.
		 * 
		 * @var int
		 */
		$expire,
		
		/** The path on the server in which the cookie will be available on.
		 * Set to '/' to make it available through the entire domain.
		 * 
		 * @var string
		 */
		$path,
		
		/** The domain that the cookie will be available to.
		 * 
		 * @var string
		 */
		$domain,
		
		/** Transmit the cookie only if the connection is a secure HTTPS connection from the client.
		 * When set to true, the cookie will be set only if a secure connection exists.
		 * 
		 * @var boolean
		 */
		$secure,
		
		/** The cookie will only be made accessible through the HTTP protocol. The cookie cannot be
		 * accessed through scripting languages, like javascript.
		 * 
		 * @var boolean
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