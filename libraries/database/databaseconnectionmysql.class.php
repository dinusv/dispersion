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
 * @ingroup database
 * @brief Database MySql connection
 * 
 * Connects to a mysql database. Also a wrapper for php's connection resource.
 */
class DatabaseConnectionMysql extends DatabaseConnection{
	
	private 
		/**
		 * @var $_connected
		 * bool : is set to true once the connection is established
		 */
		$_connected     = false,
		
		/**
		 * @var $_connection
		 * resource : mysql connection
		 */
		$_connection    = NULL;
	
	
	/** Constructor
	 */
	public function DatabaseConnectionMysql($db_settings){
		parent::__construct($db_settings);
		$this->connect($db_settings['database'], $db_settings['host'], $db_settings['user'], $db_settings['password']);
	}
	
	/** 
	 * Connect to mysql database
	 * Overrides DatabaseConnection::connect( $base, $server, $user, $pass )
	 * 
	 * @param string $base   : the database to connect to
	 * @param string $server : the server name
	 * @param string $user   : user for the database
	 * @param string $pass   : password for the database
	 */
	protected function connect( $base, $server, $user, $pass ){
		if ( !$this->_connected ){
			$this->_connection = mysql_connect( $server, $user, $pass );
			if ( !$this->_connection )
				Error::trigger( E_USER_ERROR, mysql_error() );
			if ( !mysql_select_db( $base ) ) 
				Error::trigger( E_USER_ERROR, 'Can\'t use the database : ' . mysql_error() );
		}
	}
	
	/**
	 * @return true if connected, false otherwise
	 */
	public function connected(){
		return $this->_connected;
	}
	
}