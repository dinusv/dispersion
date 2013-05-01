<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/** Contains url related fields and methods
 * 
 * @license     : http://dispersion.inevy.com/license
 * @namespace   : core
 * @file        : libraries/url.class.php
 * @version     : 1.0
 */
 
class Url{
	
	public 
		/** Name of the controller in use
		 * 
		 * @var string
		 */
		$controller = null,
		
		/** Method of controller in use
		 * 
		 * @var string
		 */
		$action     = null,
		
		/** Base url set in config file
		 * 
		 * @var string
		 */
		$config_url = null,
		
		/** Custom urls
		 * 
		 * @var array
		 */
		$custom     = array(),
		
		/** Params set to the current method of the controller
		 * 
		 * @var array
		 */
		$params     = array();
		
	private
		/** Temporary link to link to
		 * 
		 * @var string
		 */
		$link = '';
		
	private static
		/** Singleton instance
		 * 
		 * @var Url
		 */
		$instance = null;
	
	/** Singleton class
	 * 
	 * @param string $url                : the current url
	 * @param string $default_controller : the default controller set in the configuration
	 */
	public static function getInstance( $url = '', $default_controller = '', $custom_url = array() ){
		if ( self::$instance === null ) self::$instance = new self( $url, $default_controller, $custom_url );
		return self::$instance;
	}
	
	/** Constructor
	 * 
	 * @see getInstance()
	 */
	private function Url( $url, $default_controller, $custom_url ){
		$this->config_url = BASEPATH;
		$this->custom = $custom_url;
		/* add '/' if not present */
		if ( $this->config_url !== '' )
			if ( $this->config_url[strlen($this->config_url) - 1] !== '/' ) $this->config_url .= '/';
		/* get controller and action */
		if ( $url === '' ) $url = $default_controller;
		//get route settings
		$url_array = explode( '/', $url );
		$this->controller = $url_array[0];
		$this->action = 'index';
		if ( isset( $url_array[1] ) ) {
			if ( $url_array[1] !== '' )
				$this->action = $url_array[1];
			array_shift( $url_array );
		}
		array_shift( $url_array );
		$this->params = $url_array;
	}
	
	/** Return the base url
	 * 
	 * @return string
	 */
	public function base(){
		return $this->config_url;
	}
	
	/** Link to a page
	 * 
	 * @param string $controller : the controller to link to
	 * @param string $action     : optional
	 * @param array/string $args : optional, arguments to be added
	 * 
	 * @return Url object
	 */
	public function linkTo( $controller, $action = '', $args = '' ){
		if ( is_array( $args ) ){
			$argstr = '';
			foreach ( $args as $arg ){
				$argstr .= $arg . '/';
			}
		} else $argstr = $args;
		if ( $action !== '' ) $action .= '/';
		$this->link = $this->config_url . $controller . '/' . $action . $args;
		return $this;
	}
	
	/** Link to an external page
	 * 
	 * @param string $link
	 * 
	 * @return Url         : current object
	 */
	public function externalLinkTo( $link ){
		$this->link = $link;
		return $this;
	}
	
	/** Link to the current url
	 * 
	 * @return Url object
	 */
	public function linkToCurrent(){
		$this->link = '';
		return $this;
	}
	
	/** Simple page redirect.
	 * 
	 * @param string location
	 */
	public function redirect( $location = '' ){
		if ( $location === '' ) $location = $this->__toString();
		header("Location: " . $location);
	}
	
	/** Redirect using javascript ( can be used if headers are already sent )
	 * 
	 * @param string location
	 */
	public function jsRedirect( $location = '' ){
		if ( $location === '' ) $location = $this->__toString();
		echo '<script> window.location = "' . $location . '";</script>';
	}
	
	/** Creates a friendly title url
	 * 
	 * @param string $title      : the title
	 * @param boolean $lowercase : if true, the title will converted to lowercase
	 * @return string            : the new title
	 */
	public function fromTitle( $title, $lowercase = false ){
		$replace = '-';
		$title = strip_tags($title);
		$trans = array(
			'&\#\d+?;'       => '',
			'&\S+?;'         => '',
			'\s+'            => $replace,
			'[^a-z0-9\-\._]' => '',
			$replace.'+'     => $replace,
			$replace.'$'     => $replace,
			'^' . $replace	  => $replace,
			'\.+$'           => ''
		);
		foreach ( $trans as $key => $val ){
			$title = preg_replace( "#" . $key . "#i", $val, $title );
		}
		if ( $lowercase ) $title = strtolower( $title );
		return trim( stripslashes($title ) );
	}
	
	/** Check if the connection is SSL
	 * 
	 * @return boolean
	 */
	public function isSSL(){
		if ( !isset( $_SERVER['HTTPS'] ) )       return false;
		if ( $_SERVER['HTTPS']       === 1 )     return true;
		if ( $_SERVER['HTTPS']       === 'on' )  return true;
		if ( $_SERVER['SERVER_PORT'] === 443 )   return true;
		return false;
	}
	
	/** toString method override
	 * 
	 * @return string
	 */
	public function __toString(){
		if ( $this->link === '' )
			return $this->config_url . $this->controller . '/' . $this->action;
		else {
			return $this->link;
		}
	}
	
}
