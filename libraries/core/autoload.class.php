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
 * @namespace : core
 * @file      : libraries/autoload.class.php
 * @version   : 1.0
 */

class AutoLoad{
	
	private static
		/** Singleton instance
		 * 
		 * @var Autoload
		 */ 
		$instance = null;
	
	private static
		/** Locations to load the classes from
		 * 
		 * @var array
		 */
		$locations = array(),
		
		/** Locations to load the exceptions from
		 * 
		 * @var array
		 */
		$exceptions = array(),
		
		/** Extensions of the files loaded
		 * 
		 * @var array
		 */
		$extensions = array();
	
	/** Class must be singleton 
	 * 
	 * @return : instance of this class
	 */
	public static function getInstance(){
		if ( self::$instance === null ) self::$instance = new self();
		return self::$instance;
	}
	
	/** Constructor 
	 */
	private function AutoLoad(){
		spl_autoload_register( array( $this, 'load' ) );
	}

	/** Locations setter
	 * 
	 * @param array $locations : the locations to look for classes
	 */
	public static function setLocations( $locations ){
		self::$locations = $locations;
	}
	
	/** Extensions setter
	 * 
	 * @param array $extensions : the extensions of the files the classes will be in
	 */
	public static function setExtensions( $extensions ){
		self::$extensions = $extensions;
	}
	
	/** Exceptions setter
	 * 
	 * @param array $exceptions : the locations of the exceptions
	 */
	public static function setExceptions( $exceptions ){
		self::$exceptions = $exceptions;
	}
	
	/** Autoload class method
	 * 
	 * @param string $class : the name of the class to look for
	 */
	private function load( $class ){
		$count = 0;
		$found = false;
		while ( $count < count(self::$locations) && !$found ) {
			foreach ( self::$extensions as $ext ){
				if ( file_exists( self::$locations[$count] . DS . strtolower($class) . $ext ) ) {
					require_once( self::$locations[$count] . DS . strtolower($class) . $ext );
					$found = true;
				}
			}
			$count++;
		}
		if ( !$found && strpos( $class, 'Exception' )){
			foreach ( self::$exceptions as $e ){
				if ( file_exists( $e . DS . strtolower($class) . '.class.php' ) ){
					require_once( $e . DS . strtolower($class) . '.class.php' );
					$found = true;
				}
			}
		}
		if ( !$found ) {
			/* Backtrace to the 1st level */
			$trace = debug_backtrace();
			Error::trigger( E_USER_ERROR, 'Class not found : ' . $class, $trace[1]['file'], $trace[1]['line']  );
		}
	}
	
	
	
}
