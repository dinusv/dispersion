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
 * @ingroup libraries
 * @brief Maintains a users state while they browse the application.
 * 
 * Stores session information for each user as serialized data. Sessions will likely run
 * globally with each page load, so the session class mut either be initialized in each
 * controller, or it can be auto-loaded by the application.
 */
class Flash{
	
	/** Constructor
	 */
	public function Flash(){
		if ( !isset( $_SESSION ) ) session_start();
		foreach ( $_SESSION as $key => $value ){
			$seek = $key . ':c';
			if ( isset( $_SESSION[$seek] ) ){
				if ( $_SESSION[$seek] <= 0 )
					unset( $_SESSION[$key], $_SESSION[$seek]);
				else --$_SESSION[$seek];
			}
		}
	}
	
	/** Set flash data
	 * 
	 * @param string $key   : the key to set it at
	 * @param mixed  $value : value to set
	 * @param integer $life : number of pages to last
	 */
	public function set( $key, $value, $life = 1 ){
		$_SESSION[$key] = $value;
		$_SESSION[$key . ':c'] = $life;
	}
	
	/** Get flash data from a specified key
	 * 
	 * @param string $key
	 * 
	 * @return mixed      : null, if not found
	 */
	public function get( $key ){
		if ( isset( $_SESSION[$key] ) ) return $_SESSION[$key];
		else return null;
	}
	
	/** Extend life for a key
	 * 
	 * @param string $key
	 * @param integer $life
	 */
	public function extendLife( $key , $life = 1 ){
		$seek = $key . ':c';
		if ( isset( $_SESSION[$seek] ) ){
			$_SESSION[$seek] += $life;
		}
	}
	
	/** Get life for key
	 * 
	 * @param string $key
	 * 
	 * @return integer
	 */
	public function lifeFor( $key ){
		$seek = $key . ':c';
		if ( isset( $_SESSION[$seek] ) ){
			return $_SESSION[$seek];
		} else return null;
	}
	
	/** Remove flash data at a specified key
	 * 
	 * @param string $key
	 */
	public function remove( $key ){
		if ( isset( $_SESSION[$key] ) ) unset( $_SESSION[$key]);
		if ( isset( $_SESSION[$key . ':c'] ) ) unset( $_SESSION['c:'.$key]);
	}
	
}
