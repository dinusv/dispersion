<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/** Format to sql date or datetime
 * 
 * @license   : http://dispersion.inevy.com/license
 * @file      : libraries/helpers/sqldatetime.php
 * @parent    : PHP DateTime class
 * @version   : 1.0
 */

class SqlDateTime extends DateTime{
	
	/** Date mysql format
	 * 
	 * @var string
	 */
	const DATEFORMAT = "Y-m-d";
	
	/** DateTime mysql format
	 * 
	 * @var string
	 */
	const DATETIMEFORMAT = "Y-m-d H:i:s";
	
	/** Returns a string formated to the Date Mysql format
	 * 
	 * @return string            : the date returned as mysql date string format
	 */
	public function toSqlDate(){
		return $this->format( SqlDateTime::DATEFORMAT );
	}
	
	/** Returns a string formated to the DateTime Mysql format
	 * 
	 * @return string            : the date returned as mysql datetime string format
	 */
	public function toSqlDateTime(){
		return $this->format( SqlDateTime::DATETIMEFORMAT );
	}
	
}