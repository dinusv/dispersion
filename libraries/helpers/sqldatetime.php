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
 * @version 1.2
 * @author DinuSV
 */

/** 
 * @ingroup helpers
 * @brief Date and date-time to sql format conversion.
 */
class SqlDateTime extends DateTime{
	
	/** 
	 * @var DATEFORMAT
	 * string : Date mysql format
	 */
	const DATEFORMAT = "Y-m-d";
	
	/** 
	 * @var DATETIMEFORMAT
	 * string : DateTime mysql format
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