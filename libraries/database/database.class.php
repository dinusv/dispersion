<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/**  Database abstraction, containing all the defined methods
 * 
 * @license     : http://dispersion.inevy.com/license
 * @namespace   : database
 * @file        : libraries/database/Database.php
 * @extends     : Dispersion
 * @version     : 1.0
 */

abstract class Database extends Dispersion{
	
	/** Constructor
	 */
	public function Database(){
		parent::__construct();
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
	abstract public function connect( $base, $server, $user, $pass );
	
}