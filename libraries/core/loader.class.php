<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/**  Manages the config file, loads classes and parses the url
 * 
 * @license     : http://dispersion.inevy.com/license
 * @namespace   : core
 * @file        : libraries/core/Loader.php
 * @version     : 1.0
 */

class Loader {
	
	private
		/** Url of the current page
		 * 
		 * @var string
		 */
		$url = '',
		
		/** Config object
		 * 
		 * @var Config
		 */
		$config = null,
		
		/** Locations for the frameworks files
		 * 
		 * @var array
		 */
		$locations = array();

	private static
		/** Object used for displaying errors, exceptions and debugging information
		 * 
		 * @var Debug
		 */
		$debug,
		
		/** Object for handling errors
		 * 
		 * @var Error
		 */
		$error,
		
		/** Object for handling class autoload
		 *
		 * @var Autoload
		 */
		$autoload;
		
	public static
		/** Minimum required version of php
		 * 
		 * @var string
		 */
		$required_version = '5.2.4';
	
	/** Constructor
	 * 
	 * @param array $locations : locations to autoload the classes from
	 */
	public function Loader( $locations ){
		$this->checkPhpVersion();
		$this->unregisterGlobals();
		$this->locations = $locations;
		$this->config = Config::getInstance();
		if ( isset( $_GET['url'] ) ) $this->url = $_GET['url'];
		$this->url = $this->config->getRoute( $this->url );
		$this->setErrorHandling();
		$this->setAutoLoad();
		$this->removeMagicQuotes();
	}
	
	/** Check the php version
	 */
	private function checkPhpVersion(){
		if ( version_compare(PHP_VERSION, self::$required_version ) < 0) {
			die( 'Your server is running version ' . PHP_VERSION . ' but this framework requires at least ' . self::$required_version );
		}
	}
	
	/** Check for magic quotes inside arrays and remove them
	 * 
	 * @param array $value : contains strings with magic quotes
	 * 
	 * @return             : array stripped for magic quotes
	 */
	private function stripSlashesDeep($value) {
		if ( is_array($value) ) {
			$value = array_map(array( $this, 'stripSlashesDeep' ), $value);
		} else $value = stripslashes($value);
		return $value;
	}
	
	/** Remove magic quotes in GET, POST and COOKIE
	 */	
	private function removeMagicQuotes(){
		if ( get_magic_quotes_gpc() ) {
			$_GET    = $this->stripSlashesDeep($_GET   );
			$_POST   = $this->stripSlashesDeep($_POST  );
			$_COOKIE = $this->stripSlashesDeep($_COOKIE);
		}
	}
	
	/** Check if there are any registered globals and remove them
	 */
	private function unregisterGlobals(){
		$register_globals = @ini_get( 'register_globals' );
		if ( $register_globals === "" || $register_globals === "0" || strtolower( $register_globals ) === "off")
			return;
		if ( isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'] ) ){
			 exit('Overwriting GLOBALS not allowed.');
		}
		$array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
		
		foreach ( $array as $value ) 
			if ( isset( $GLOBALS[$value] ) ){
				foreach ( $GLOBALS[$value] as $key => $var ){
					if ( $var === $GLOBALS[$value][$key] ) 
						unset ( $GLOBALS[$key] );
				}
			}
		
	}
	
	/* Set up error handling
	 */
	private function setErrorHandling(){
		$errorconfig = $this->config->getErrorSettings();
		self::$debug = Debug::getInstance(
			$errorconfig['stage'],
			$errorconfig['log_exceptions'],
			$errorconfig['log_exceptions_file']
		);
		self::$error = Error::getInstance( 
			self::$debug,
			$errorconfig
		);
		set_error_handler(array( &self::$error, "errorHandler" ));
		set_exception_handler(array( &self::$error, "exceptionHandler") );
	}
	
	/* Set up the autoload class
	 */
	private function setAutoLoad(){
		self::$autoload = AutoLoad::getInstance();
		AutoLoad::setExtensions( array('.php', '.class.php' ) );
		AutoLoad::setExceptions( array(
			$this->locations['exceptions_custom'],
			$this->locations['libraries'] . DS . 'exceptions'
		));
	}
	
	/* Parses the url and calls the given method
	 */
	public function callUrl(){
		define( 'BASEPATH', $this->config->getBaseUrl() );
		AutoLoad::setExtensions( array('.php', '.class.php', 'mysql.class.php' ) );
		AutoLoad::setLocations( array(
			$this->locations['libraries'] . DS . 'core',
			$this->locations['libraries'] . DS . 'database',
			$this->locations['models']
		));
		$url = Url::getInstance( $this->url, $this->config->autoloadSettings( 'defaultcontroller' ), $this->config->getUrls() );
		/* create the model and initiate parameters */
		try{
			$model_name = $this->config->getModelFor( $url->controller );
			$controller_suf = $url->controller . 'Controller';
			if ( !file_exists( $this->locations['controllers'] . DS . strtolower($controller_suf) . '.class.php' )
			  && !file_exists( $this->locations['controllers'] . DS . strtolower($controller_suf) . '.php' ) ){
				throw new PageNotFoundException();
			}
			$tpl_instance = Template::getInstance( 
				new $model_name( $this->config->getDbSettings() ),
				self::$debug,
				$this->locations,
				$this->config->autoloadSettings()
			);
			AutoLoad::setExtensions( array('.php', '.class.php') );
			AutoLoad::setLocations( array(
				$this->locations['libraries'] . DS . 'core',
				$this->locations['controllers']
			));
			/* create the controller */
			$dispatch = new $controller_suf();
			/* call the method */
			if ( method_exists($controller_suf, $url->action) ) {
				$callMethod  = new ReflectionMethod( $controller_suf, $url->action );
				if ( $callMethod->getNumberOfRequiredParameters() != 0 )
					if ( $callMethod->getNumberOfRequiredParameters() > count( $url->params ) )
						throw new PageNotFoundException();
				try {
					$callMethod->invokeArgs( $dispatch, $url->params );
				} catch ( Exception $e ){
					self::$debug->exception( $e );
				}
			} else {
				throw new PageNotFoundException();
			}
		} catch ( PageNotFoundException $e ){
			if ( isset( $tpl_instance ) )
				$tpl_instance->emptyLayout();
			$page_not_found = $this->config->pageNotFound();
			if ( $page_not_found['do'] === 'file' ){
				header("HTTP/1.0 404 Not Found");
				require_once( $page_not_found['param'] );
			} elseif ( $page_not_found['do'] === 'redirect'){
				header("Location: " . $page_not_found['param'] );
			} else {
				header("HTTP/1.0 404 Not Found");
				echo $page_not_found['param'];
			}
		}
	}
}