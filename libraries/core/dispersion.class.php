<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/** Main class
 * 
 * @license     : http://dispersion.inevy.com/license
 * @namespace   : core
 * @file        : libraries/dispersion.class.php
 * @version     : 1.0
 */

class Dispersion{
	
	protected static
		/** Model object to be initialised by inheriting classes
		 * 
		 * @var Model
		 */
		$model_ob = null,
		
		/** Debug object to be initialised by inheriting classes
		 * 
		 * @var Debug
		 */
		$debug_ob = null,
		
		/** Stores the variables that will be made available to the view files
		 * 
		 * @var array
		 */
		$_variables = array(),
		
		/** Stores the view files
		 * 
		 * @var array
		 */
		$_content = array();
	
	private static
		/** Keeps track of the number of view files added
		 * 
		 * @var integer
		 */
		$_content_count = 0;
	
	private
		$models = array();
	
	protected
		/** Current model
		 * 
		 * @var Model
		 */
		$model,
		
		/** Current debug object
		 * 
		 * @var Debug
		 */
		$debug;
	
	/** Constructor
	 */
	public function Dispersion(){
		$this->model =& self::$model_ob;
		$this->debug =& self::$debug_ob;
	}
	
	/** Empty the standard layout 
	 */
	public function emptyLayout(){
		self::$_content = array();
	}
	
	/** Set variables for the view files
	 * 
	 * @param string $name  : name of the variable
	 * @param string $value : value of the variable
	 */
	public function set($name,$value) {
		self::$_variables[$name] = $value;
	}
	
	/** Different name for the set method in case the name is needed for a controller action
	 * 
	 * @see 'set' method
	 */
	 public function _set( $name, $value ){
	 	self::$_variables[$name] = $value;
	 }
	
	/** Add content
	 * 
	 * @param string  $name  : name of the view file
	 * @param numeric $index : (optional)add the view file at the specified position
	 * 
	 * @throws IndexOutOfBoundsException
	 */
	public function insertView( $name, $index = -1 ){
		if ( $index == -1 ) $index = self::$_content_count;
		if ( count(self::$_content ) > 0 ){
			$i = 0;
			while ( $i < count(self::$_content) && $index !== self::$_content[$i] ) $i++;
			if ( $i < count( self::$_content ) ) {
				self::$_content[$i] = $name;
				self::$_content_count++;
			} else {
				throw new IndexOutOfBoundsException( $index );
			}
		} else {
			self::$_content[] = $name;
			self::$_content_count++;
		}
	}
	
	/** Require a configuration file. Used by libraries which require configuration.
	 * 
	 * @param string file_name : the name of the file
	 * @param boolean required : true if the file is needed, false if the file is optional
	 * 
	 * @throws FileNotFoundException
	 * 
	 * @return boolean : true if the file was found and included, false otherwise
	 */ 
	protected function requireConfigFile( $file_name, $required = false ){
		if ( file_exists( APPFILESROOT . DS . 'config' . DS . $file_name . '.php' ) ){
			include_once(  APPFILESROOT . DS . 'config' . DS . $file_name . '.php' );
			return true;
		} else {
			if ( $required ) throw new FileNotFoundException( 'The library requires a configuration file that was not found : ' . $file_name . '.php' );
			return false;
		}
	}
	
}
