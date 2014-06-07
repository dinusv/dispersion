<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2013 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/**
 * @version 1.1
 * @author DinuSV
 */

/** 
 * @ingroup database
 * @brief Database abstraction, containing all the defined methods
 */
abstract class DatabaseConnection{
	
	private 
		/**
		 * @var $_debug_queries
		 * bool : true if query-debug is enabled
		 */
		$_debug_queries = false,
		
		/**
		 * @var $_table_prefix
		 * string : In case the database contains tables from other web applications, it's good practice to add a common
		 * prefix to all tables used within this application
		 */
		$_table_prefix  = '';
	
	/** Constructor
	 */
	public function DatabaseConnection($db_settings){
		$this->_debug_queries = $db_settings['debug_queries'];
		$this->_table_prefix  = $db_settings['table_prefix'];
	}
		
	/**  
	 * Connection method
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