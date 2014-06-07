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
  * @brief Stores all configuration options of an application
  * 
  * Gets accessed through configuration files found within the "application" directory
  * of each web-app.
  */
class Config{
	
	/* 
	 * Set from the main config file
	 * ----------------------------------------- */
	
	private static
		/** 
		 * @var $db
		 * array : Database options
		 */
		$db        = array(),
		
		/** 
		 * @var $base_path
		 * string : Url of the website
		 */
		$base_path = '',
		
		/** 
		 * @var $url
		 * array : Custom urls
		 */
		$url = array(),
		
		/** 
		 * @var $autoload
		 *  array : Autoload settings : defaultcontroller, libraries, viewfiles
		 */
		$autoload  = array(),
		
		/** 
		 * @var $models
		 * array : Model-controller mapping
		 */
		$models    = array(),
		
		/** 
		 * @var $routes
		 * array : Store routes from the config
		 */
		$routes    = array();
	
	/* 
	 * Set from the error config files
	 * ----------------------------------------- */
	 
	private static
		/** 
		 * @var $errors
		 * array : Settings on displaying errors : 
		 *      stage, output_source_lines, log_exceptions, log_exceptions_file, page_not_found, page_not_found_param
		 */
		$errors       = array(),
		
		/** 
		 * @var $phpini
		 * array : Settings to overwrite php ini : 
		 *      overwrite, log_errors, log_file
		 */
		$phpini       = array();
	
	/* 
	 * Custom fields
	 * ----------------------------------------- */
	
	private static
		/**
		 * @var $custom
		 * array : Stores custom settings
		 */
		$custom = array();
	
	private static
		/**
		 * @var $instance
		 * Config : Singleton instance
		 */
		$instance     = null;
	
	/* 
	 * Routing
	 * ----------------------------------------- */
	
	private
		/**
		 * @var $new_route
		 * Store routing
		 */
		$new_route = array(),
		
		/**
		 * @var $new_route_size
		 * int : Size of the new_rout field
		 */
		$new_route_size = 0;
	
	/** Singleton class with only one instance returned
	 * 
	 * @return Config : instance of this class, or null otherwise
	 * 
	 * @throws Exception
	 */
	public static function getInstance(){
		if ( self::$instance === null ) {
			self::$instance = new self();
			return self::$instance;
			
		} else {
			throw new Exception('Object instance not available.');
			return null;
		}
	}
	
	/** Constructor
	 */
	private function Config(){
		if ( !isset( self::$errors['stage'] ) ) self::$errors['stage'] = 'development';
		if ( !isset( self::$phpini['overwrite'] ) ) self::$phpini['overwrite'] = false;
		if ( !isset( self::$errors['source_output_lines'] ) ) self::$errors['source_output_lines'] = 0;
		if ( !isset( self::$errors['log_exceptions'] ) ) self::$errors['log_exceptions'] = false;
	}
	
	/* 
	 * Setters
	 * ----------------------------------------- */
	
	/** Database settings
	 * 
	 * @param string $setting_name
	 * @param string $setting_value
	 */
	public static function db( $setting_name, $setting_value ){
		self::$db[$setting_name] = $setting_value;
	}
	
	/** Base url
	 * 
	 * @param string $url
	 */
	public static function baseurl( $url ){
		self::$base_path = $url;
	}
	
	/** Urls to use
	 * 
	 * @param string $url_key
	 * @param string $url_val
	 */
	public static function url( $url_key, $url_val ){
		self::$url[$url_key] = $url_val;
	}
	
	/** Set the default timezone
	 * 
	 * @param string $timezone
	 */
	public static function timezone( $timezone ){
		date_default_timezone_set($timezone);
	}
	
	/** Autoload settings
	 * 
	 * @param string $setting_name
	 * @param array $setting_value
	 */
	public static function autoload( $setting_name, $setting_value ){
		self::$autoload[$setting_name] = $setting_value;
	}
	
	/** Model settings
	 * 
	 * @param string $current_model
	 * @param string $new_model
	 */
	public static function models( $current_model, $new_model ){
		self::$models[$current_model] = $new_model;
	}
	
	/** Disable models ( shortcut for the models function )
	 * 
	 * @param array $models
	 */
	public static function disablemodels( $models = array() ){
		foreach ( $models as $model ){
			self::$models[$model] = '';
		}
	}
	
	/** Error settings
	 * 
	 * @param string $setting_name
	 * @param string $setting_value
	 */
	public static function errors( $setting_name, $setting_value ){
		self::$errors[$setting_name] = $setting_value;
	}
	
	/** Php ini settings
	 * 
	 * @param string $setting_name
	 * @param string $setting_value
	 */
	public static function phpini( $setting_name, $setting_value ){
		self::$phpini[$setting_name] = $setting_value;
	}
	
	/* 
	 * Routing
	 * ----------------------------------------- */
	
	/** Route helper, creates a quick match for the route
	 * 
	 * @param string $url
	 * 
	 * @return string  : if found
	 * @return boolean : false if not found
	 */
	private static function quickMatch( $url ){
		foreach( $url as $param ){
			if ( $param !== '' && strpos( $param, '[*' ) === false ) return $param;
		}
		return false;
	}
	
	/** Add routes
	 * 
	 * @param string $url      : the url to match
	 * @param string $newroute
	 */
	public static function addroute( $url, $newroute ){
		$base = array();
		if ( $url[0] === '/' ) $url = substr( $url, 1 );
		if ( $url[strlen( $url ) - 1] === '/' ) 
			$base['url'] = substr( $url, 0, strlen($url) - 1 );
		else $base['url'] = $url;
		$match = self::quickMatch( explode( '/', $url ) );
		if ( $match !== false ) $base['match'] = $match;
		if ( !is_array($newroute) ){
			if ( $newroute[0] === '/' ) $newroute = substr( $newroute, 1 );
			if ( $newroute[strlen( $newroute ) - 1] === '/' )
				$base['new'] = explode( '/', substr( $newroute, 0, strlen($newroute) - 1 ) );
			else $base['new'] = explode( '/', $newroute );
		} else $base['new'] = $newroute;
		self::$routes[] = $base;
	}

	/** Recursive method to match the url with a route. Helper for the getRoute method
	 * 
	 * @param string $route          : the route from the url
	 * @param string $match          : the string from the config class
	 * @param integer $route_counter : counter for the route array, used recursively
	 * @param integer $match_counter : counter for the match array, used recursively
	 * 
	 * @return boolean               : true, if the routes match, otherwise false
	 */
	private function fullMatch( $route, $match, $route_counter, $match_counter ){
		/* look for a word */
		$i = $match_counter;
		while ( $i < count( $match ) && ( strpos( $match[$i], '[*' ) !== false ) ) $i++;
		if ( $i !== count($match) ){
			$j = $route_counter;
			while ( $j < count( $route ) && $route[$j] !== $match[$i] ) $j++;
			if ( $j === count( $route ) ) {
				/* word $match[$i] not found */
				return false;
			}
		} else {
			if ( $i === count($match) )
			$j = count( $route );
		}
		/* number of parameters to match */
		$param_count = $j - $route_counter;
		if ( $i === $match_counter && $param_count !== 0 ) return false;
		if ( $match_counter === count($match) && $param_count === 0) return false;
		$min = 0; $max = 0;
		for ( $k = $match_counter; $k < $i; $k++ ){
			$value = substr( $match[$k], 2, strlen($match[$k]) - 3 );
			if ( $value !== '' ){
				if ( $value[0] === '<' ){
					$max = (int)substr( $value, 1 );
				} else if ( $value[0] === '>' ){
					$min = (int)substr( $value, 1 );	
				} else {
					$min = (int)$value;
					$max = (int)$value;
				}
			}
		}
		if ( $route_counter !== $j ) $this->new_route_size++;
		for ( $k = $route_counter; $k < $j; $k++ ) {
			$this->new_route[$this->new_route_size][] = $route[$k];
		}
		/* Values between min and max */
		if ( $max !== 0 && $param_count > $max ) return false;
		if ( $param_count < $min ) return false;
		
		/* If we're not at the end, repeat process, otherwise match is found */
		if ( $i + 1 >= count( $match ) && $j + 1 >= count( $route ) ) return true;
		else return $this->fullMatch( $route, $match, $j + 1, $i + 1 );
	}
	
	/** Main route method to get the new route.
	 * 
	 * @param string $url : the url to check routing for
	 * 
	 * @throws IllegalArgumentTypeException
	 * 
	 * @return string     : the new url 
	 */
	public function getRoute( $url ){
		/* Remove '/' from the end of the route */
		if ( strlen( $url ) > 0 )
			if ( $url[strlen( $url ) - 1] === '/' ) $url = substr( $url, 0, strlen($url) - 1 );
		/* Quick match */
		if ( count(self::$routes) > 0 ){
			foreach( self::$routes as $route ){
				$this->new_route = array();
				$this->new_route_size = 0;
				$fullmatch = false;
				if ( isset( $route['match'] ) ){
					if ( strpos( $url, $route['match'] ) !== false ){
						$fullmatch = true;				
					}
				} else $fullmatch = true;
				/* Full match */
				if ( $fullmatch && $this->fullMatch( explode( '/', $url ), explode( '/', $route['url'] ), 0, 0 ) ){
					return $this->newUrl( $route['new'] );
				}
			}
		}
		return $url;
	}
	
	/** Creates the new url by parsing the syntax and adding the values where necessary.
	 * 
	 * @param string $newurl
	 * 
	 * @throws IllegalArgumentTypeException
	 * 
	 * @return string
	 */
	private function newUrl( $newurl ){
		$base = '';
		foreach ( $newurl as $param ){
			if ( $base !== '' ) $base .= '/';
			if ( strpos( $param, '[*' ) !== false ){
				$temp_param = substr( $param, 2, strlen( $param ) - 3 );
				if ( strpos( $temp_param, '(' ) ){
					$primary_key = substr( $temp_param, 0, strpos( $temp_param, '(' ) );
					$secondary_key = substr( 
						$temp_param, 
						strpos( $temp_param, '(') + 1, 
						strpos( $temp_param, ')' ) - strpos( $temp_param, '(') - 1
					);
					$base .= $this->new_route[(int)$primary_key][(int)$secondary_key];
				} else if ( $temp_param === '' ){
					foreach ( $this->new_route as $primary_value )
						foreach ( $primary_value as $secondary_value ){
							$base .= $secondary_value . '/';
						}
					if ( $base[strlen( $base ) === '/' ] ) $base = substr( $base, 0, strlen($base) - 1 );
				} else {
					foreach( $this->new_route[(int)$temp_param] as $value )
						$base .= $value . '/';
					if ( $base[strlen( $base ) === '/' ] ) $base = substr( $base, 0, strlen($base) - 1 );
				}
			} else {
				$base .= $param;
			}
		}
		return $base;
	}
	
	/** Set custom values
	 * 
	 * @param string $setting_name
	 * @param mixed $setting_value
	 */
	public static function set( $setting_name, $setting_value ){
		$this->custom[$setting_name] = $setting_value;
	}
	
	/* 
	 * Getters
	 * ----------------------------------------- */
	
	/** Db settings
	 * 
	 * @return array
	 */
	public function getDbSettings(){
		return self::$db;
	}
	
	/** Base url
	 * 
	 * @return string
	 */
	public function getBaseUrl(){
		return self::$base_path;
	}
	
	/** Get the model for the controller
	 * 
	 * @param string $model_name
	 * 
	 * @return string
	 */
	public function getModelFor( $model_name ){
		if ( isset( self::$models[$model_name] ) )
			if ( self::$models[$model_name] !== '' )
				return self::$models[$model_name] . 'Model';
			else return 'Model';
		else return strtolower( $model_name ) . 'Model';
	}
	
	/** Error settings
	 * 
	 * @return array
	 */
	public function getErrorSettings(){
		$base = self::$errors;
		if ( self::$phpini['overwrite'] === true ){
			foreach ( self::$phpini as $key => $value ){
				$base[$key] = $value;
			}
		} else {
			$base['overwrite'] = false;
		}
		return $base;
	}
	
	/** Get settings for page not found
	 *
	 * @return array
	 */
	public function pageNotFound(){
		return array(
			'do'    => self::$errors['page_not_found'],
			'param' => self::$errors['page_not_found_param']
		);
	}
	
	/** Get custom urls
	 * 
	 * @return array
	 */
	public function getUrls(){
		return self::$url;
	}
	
	/** Get custom settings
	 * 
	 * @return mixed
	 * 
	 * @throws IndexOutOfBoundsException
	 */
	public function get( $setting_name ){
		if ( isset( $this->custom[$setting_name] ) ) return $this->custom[$setting_name];
		else throw new IndexOutOfBoundsException('Cannot find setting : ' . $setting_name ); 
	}
	
	/** Autoload settings
	 * 
	 * @param string $index : get a specific setting
	 * 
	 * @throws IndexOutOfBoundsException
	 * 
	 * @return array
	 */
	public function autoloadSettings( $index = null ){
		if ( $index === null ) return self::$autoload;
		else if ( isset( self::$autoload[$index] ) )
			return self::$autoload[$index];
		throw new IndexOutOfBoundsException('Cannot find value for ' . $index . ' in the autoload settings');
	}
}
