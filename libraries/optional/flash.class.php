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
 * @license   : http://dispersion.inevy.com/license
 * @namespace : optional
 * @file      : libraries/optional/flash.class.php
 * @version   : 1.0 
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