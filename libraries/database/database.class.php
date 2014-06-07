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