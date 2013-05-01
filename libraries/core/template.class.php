<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/** Wraps methods for the view files
 * 
 * @license     : http://dispersion.inevy.com/license
 * @namespace   : core
 * @file        : libraries/template.class.php
 * @extends     : Dispersion
 * @version     : 1.0
 */

class Template extends Dispersion{
	
	private
		/** Stores the loaded libraries
		 * 
		 * @var array
		 */
		$loaded_libs     = array(),
		
		/** Stores the locations to load the libs
		 * 
		 * @var array
		 */
		$loaded_libs_loc = array(),
		
		/** Stores the number of view files added
		 * 
		 * @var integer
		 */
		$content_counter = 0,
		
		/** Stores the location of the javascript files
		 * 
		 * @var string
		 */
		$js_file_loc     = BASEPATH,
		
		/** Stores the location of the css files
		 * 
		 * @var string
		 */
		$css_file_loc    = BASEPATH,
		
		/** Extension of the view files
		 * 
		 * @var string
		 */
		$view_file_ext   = '.php',
		
		/** Stores the location of the view files
		 * 
		 * @var string
		 */
		$view_file_loc   = '';
	
	private static
		/** Singleton instance
		 * 
		 * @var Template
		 */
		$instance        = null;
		
	/** Singleton class
	 * 
	 * @param string $view_file_location : the full path to the view files
	 * @param string $js_lib_location    : the full path to the js files
	 */
	public static function getInstance( $modelob = null, $debugob = null, $locations = array(), $autoload = array() ){
		if ( self::$instance === null ) {
			/* load model, debug handler */
			parent::$model_ob = $modelob;
			parent::$debug_ob = $debugob;
			parent::$_content = $autoload['viewfiles'];
			self::$instance   = new self( $locations, $autoload['libraries'] );
		}
		return self::$instance;
	}

	/** Constructor
	 * 
	 * @see getInstance 
	 */
	private function Template( $locations, $libraries ){
		parent::__construct();
		/* load the libraries */
		$this->loaded_libs_loc = array(
			$locations['libraries'] . DS . 'optional',
			$locations['libraries_custom'],
			$locations['libraries'] . DS . 'helpers',
			$locations['helpers_custom']
		);
		$this->loaded_libs = $libraries;
		/* set the view file location */
		$this->view_file_loc = $locations['views'];
	}
	
	/* 
	 * Library loaders
	 * ----------------------------------------- */
	
	public function getLoadedLibraries(){
		AutoLoad::setLocations( $this->loaded_libs_loc );
		return $this->loaded_libs;
	}
	
	/** Function used by controller to initialise the libraries
	 * 
	 * @param string $lib   : the library name
	 * @param object $value : library object
	 */
	public function initLib( $lib, $value ){
		$this->$lib = $value;
	}
	
	/* 
	 * View files
	 * ----------------------------------------- */
	
	/** Insert a view file
	 * 
	 * @override
	 * 
	 * @param string $name   : name of the file
	 * @param numeric $index : override required argument
	 * 
	 * @throws FileNotFoundException 
	 */
	public function insertView( $viewfile, $index = -1 ){
		extract(self::$_variables);
		if (file_exists( $this->view_file_loc . DS . $viewfile . $this->view_file_ext ))
			require_once( $this->view_file_loc . DS . $viewfile . $this->view_file_ext );
		else throw new FileNotFoundException( $this->view_file_loc . DS . $viewfile . $this->view_file_ext );
	}
	
	
	/** Loads the set of view files
	 * 
	 * @throws FileNotFoundException
	 */
	public function render() {
		extract(self::$_variables);
		if ( count( self::$_content ) > 0 ){
			foreach( self::$_content as $viewfile ){
				$this->content_counter++;
				if ( !is_numeric($viewfile) ){
					if ( file_exists( $this->view_file_loc . DS . $viewfile . $this->view_file_ext ) ){
						include ( $this->view_file_loc . DS . $viewfile . $this->view_file_ext );
					} else throw new FileNotFoundException( 'File not found (' . $this->view_file_loc . DS . $viewfile . $this->view_file_ext . ')' );
				}
			}
		}
	}

	/* 
	 * Js/css methods
	 * ----------------------------------------- */
	
	/** Set location for the css files
	 * 
	 * @param string $css_file_loc
	 */
	public function setCssPath( $css_file_loc ){
		$this->css_file_loc = $css_file_loc;
	}
	
	/** Set location for the javascript files
	 * 
	 * @param string $js_file_loc
	 */
	public function setJsPath( $js_file_loc ){
		$this->js_file_loc = $js_file_loc;
	}
	
	/** Print css file
	 * 
	 * @param string $name : css file name with relative path
	 */
	public function css( $name ) {
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->css_file_loc . $name . "\" />";
	}
	
	/** Print js file
	 * 
	 * @param string $name : js file name with relative path
	 */
	public function js( $name ) {
		echo "<script src=\"" . $this->js_file_loc . $name . "\"></script>";
	}
	
	
}