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
  * @ingroup core
  * @brief Base for core classes and libraries within the framework
  *  
  * Extending this class offers access to the data model, debug object, all autoloaded
  * libraries and the ability to manipulate views. Extension should mainly be done
  * by custom libraries that require this access. Internally, the framework extends this
  * class for data accessors, some libraries and core components.
  */
class Dispersion{
	
	protected static
		/** 
		 * @var $model_ob
		 * Model : object to be initialised by inheriting classes
		 */
		$model_ob = null,
		
		/**
		 * @var $debug_ob
		 * Debug : Debug object to be initialised by inheriting classes 
		 */
		$debug_ob = null,
		
		/**
		 * @var $_variables
		 * array : Stores the variables that will be made available to the view files
		 */
		$_variables = array(),
		
		/** 
		 * @var $_content
		 * array : Stores the view files
		 */
		$_content = array();
	
	private static
		/** 
		 * @var $_content_count
		 * int : Keeps track of the number of view files added
		 */
		$_content_count = 0;
	
	private
		$models = array();
	
	protected
		/**
		 * @var $model
		 * Model : Current model
		 */
		$model,
		
		/** 
		 * @var $debug
		 * Debug : Current debug object
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
	 * @param string $file_name : the name of the file
	 * @param boolean $required : true if the file is needed, false if the file is optional
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
