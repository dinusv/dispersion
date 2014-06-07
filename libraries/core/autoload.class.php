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
 * @version 1.1
 * @author DinuSV
 */

 /** 
  * @ingroup core
  * @brief Internal component handling automatic file inclusion when instantiating new classes.
  * 
  * This class is used mostly internally to configure php into automatically including helpers,
  * libraries and exceptions when instantiated. On load time, it's usage consists in requiring
  * controllers and modles. 
  */
class AutoLoad{
	
	/** 
	 * @var $instance
	 * Autoload : Singleton instance
	 */
	private static $instance = null;
	
	private static
		/** 
		 * @var $locations 
		 * array : Locations to load the classes from
		 */
		$locations = array(),
		
		/**
		 * @var $exceptions
		 * array : Locations to load the exceptions from
		 */
		$exceptions = array(),
		
		/** 
		 * @var $extensions
		 * array : Extensions of the files loaded
		 */
		$extensions = array();
	
	/** Singleton class
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
	 * @param $locations array : the locations to look for classes
	 */
	public static function setLocations( $locations ){
		self::$locations = $locations;
	}
	
	/** Extensions setter
	 * 
	 * @param $extensions array : the extensions of the files the classes will be in
	 */
	public static function setExtensions( $extensions ){
		self::$extensions = $extensions;
	}
	
	/** Exceptions setter
	 * 
	 * @param $exceptions array : the locations of the exceptions
	 */
	public static function setExceptions( $exceptions ){
		self::$exceptions = $exceptions;
	}
	
	/** Autoload class method
	 * 
	 * @param $class string : the name of the class to look for
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
