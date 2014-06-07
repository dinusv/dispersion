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
  * @brief Provides control over view files.
  *  
  * Contains all view files to be rendered. Stores default header and footer used throughout
  * the site. Offers access to loaded libraries and models for view files. 
  */
class Template extends Dispersion{
	
	private
		/** 
		 * @var $loaded_libs
		 * array : Stores the loaded libraries
		 */
		$loaded_libs     = array(),
		
		/** 
		 * @var $loaded_libs_loc
		 * array : Stores the locations to load the libs
		 */
		$loaded_libs_loc = array(),
		
		/**
		 * @var $content_counter
		 * int : Stores the number of view files added
		 */
		$content_counter = 0,
		
		/** 
		 * @var $js_file_loc
		 * string : Stores the location of the javascript files
		 */
		$js_file_loc     = BASEPATH,
		
		/** 
		 * @var $css_file_loc
		 * string : Stores the location of the css files
		 */
		$css_file_loc    = BASEPATH,
		
		/** 
		 * @var $view_file_ext
		 * string : Extension of the view files
		 */
		$view_file_ext   = '.php',
		
		/** 
		 * @var $view_file_loc
		 * string : Stores the location of the view files
		 */
		$view_file_loc   = '';
	
	private static
		/** 
		 * @var $instance
		 * Template : Singleton instance
		 */
		$instance        = null;
		
	/** Singleton class
	 * 
	 * @param $modelob string  : model object
	 * @param $debugob string  : the full path to the js files
	 * @param $locations array : path to view files
	 * @param $autoload array  : autoloaded files & libraries
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
	 * @param array $locations : path to view files
	 * @param array $libraries : autoloaded libraries
	 */
	private function Template( $locations, $libraries ){
		parent::__construct();
		/* load the libraries */
		$this->loaded_libs_loc = array(
			$locations['libraries'] . DS . 'optional',
			$locations['libraries_custom'],
			$locations['libraries'] . DS . 'helpers',
			$locations['helpers_custom'],
			$locations['models']
		);
		$this->loaded_libs = $libraries;
		/* set the view file location */
		$this->view_file_loc = $locations['views'];
	}
	
	/* 
	 * Library loaders
	 * ----------------------------------------- */
	
	/**
	 * @return : array of loaded libraries
	 */
	public function getLoadedLibraries(){
		AutoLoad::setLocations( $this->loaded_libs_loc );
		return $this->loaded_libs;
	}
	
	/** Function used by controller to initialise the libraries
	 * 
	 * @param $lib string   : the library name
	 * @param $value object : library object
	 */
	public function initLib( $lib, $value ){
		$this->$lib = $value;
	}
	
	/* 
	 * View files
	 * ----------------------------------------- */
	
	/** Insert a view file
	 * 
	 * Overrides Dispersion::insertView($viewfile, $index = -1)
	 * 
	 * @param string $viewfile : name of the file
	 * @param numeric $index   : override required argument
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
	 * @param $css_file_loc string
	 */
	public function setCssPath( $css_file_loc ){
		$this->css_file_loc = $css_file_loc;
	}
	
	/** Set location for the javascript files
	 * 
	 * @param $js_file_loc string
	 */
	public function setJsPath( $js_file_loc ){
		$this->js_file_loc = $js_file_loc;
	}
	
	/** Print css file
	 * 
	 * @param $name string : css file name with relative path
	 */
	public function css( $name ) {
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->css_file_loc . $name . "\" />";
	}
	
	/** Print js file
	 * 
	 * @param $name string : js file name with relative path
	 */
	public function js( $name ) {
		echo "<script src=\"" . $this->js_file_loc . $name . "\"></script>";
	}
	
	
}