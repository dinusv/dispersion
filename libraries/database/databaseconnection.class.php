<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2013 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/**  Database abstraction, containing all the defined methods
 * 
 * @license     : http://dispersion.inevy.com/license
 * @namespace   : database
 * @file        : libraries/database/Database.php
 * @extends     : Dispersion
 * @version     : 1.0
 */

abstract class DatabaseConnection{
	
	private 
		$_debug_queries = false,
		$_table_prefix  = '';
	
	/** Constructor
	 */
	public function DatabaseConnection($db_settings){
		$this->_debug_queries = $db_settings['debug_queries'];
		$this->_table_prefix  = $db_settings['table_prefix'];
	}
		
	/** Connect to mysql database 
	 * 
	 * @override
	 * 
	 * @param string $base   : the database to connect to
	 * @param string $server : the server name
	 * @param string $user   : user for the database
	 * @param string $pass   : password for the database
	 */
	abstract protected function connect( $base, $server, $user, $pass );
	
	/** 
	 * @return true if debug queries was configured to true, false otherwise
	 */
	public function debugQueries(){
		return $this->_debug_queries;
	}
	
	/**
	 * @return table prefix
	 */
	public function tablePrefix(){
		return $this->_table_prefix;
	}
	
}