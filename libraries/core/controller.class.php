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
  * @brief Base for all application controllers.
  *  
  * Extends the main Dispersion class, therefore provides access to all autoloaded
  * libraries. 
  */
class Controller extends Dispersion {
	
	protected
		/** 
		 * @var $template
		 * Template : Contains the template object
		 */
		$template,
		
		/** 
		 * @var $url
		 * Url : Contains the url object
		 */
		$url;
	
	/** Constructor
	 */
	public function Controller(){
		/* initialise the model */
		parent::__construct();
		
		$this->template   = Template::getInstance();
		$this->url        = Url::getInstance();
		$this->template->initLib( 'url', $this->url );
		
		if ( get_class( $this->model ) === 'Model' ) 
			$this->model->setTable( $this->url->controller );
		/* initialise the libraries for this controller and the template */
		$libraries = $this->template->getLoadedLibraries();
		if ( count( $libraries ) > 0 )
			foreach( $libraries as $lib ){
				$this->$lib = new $lib();
				$this->template->initLib( $lib, $this->$lib );
			}
		/* hook */
		$this->before();
	}
	
	/** 
	 * Hook to extend in controllers.
	 */
	protected function before() {}
	
	/** Get clients ip
	 *
	 * @return string
	 */
	protected function getIp(){
		if( getenv('HTTP_CLIENT_IP') && strcasecmp( getenv( 'HTTP_CLIENT_IP' ), 'unknown' ) ){
			return getenv('HTTP_CLIENT_IP');
		} elseif ( getenv('HTTP_X_FORWARDED_FOR') && strcasecmp( getenv('HTTP_X_FORWARDED_FOR'), 'unknown' ) ) {
			return getenv('HTTP_X_FORWARDED_FOR');
		} elseif ( getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown') ) {
			return getenv('REMOTE_ADDR');
		} elseif (
			isset($_SERVER['REMOTE_ADDR']) &&
			$_SERVER['REMOTE_ADDR'] &&
			strcasecmp( $_SERVER['REMOTE_ADDR'], 'unknown' )
		){
			return $_SERVER['REMOTE_ADDR'];
		} else return '';
	}
	
	/** Check if the request is an ajax request.
	 *
	 * @return boolean
	 */
	protected function isAjax(){
		return (
			isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
		);
	}
	
	/** Respond to a json request 
	 * 
	 * @param string $response 
	 */
	protected function jsonRespond( $response ){
		$this->emptyLayout();
		print json_encode( $response );
	}

	/** On destruct load view files 
	 */
	function __destruct() {
		try {
			$this->template->render();
		} catch ( FileNotFoundException $e ){
			$this->debug->exception($e);
		}
	}

}
