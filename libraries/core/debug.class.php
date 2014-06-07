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
 * @version 1.1
 * @author DinuSV
 */

 /** 
  * @ingroup core
  * @brief Offers support for debugging php errors, displaying variables, and other messages
  * within a web application. All these features are turned off when the website enters
  * production mode.
  *  
  * To debug variables, just use variable( $var, $level = 0 ) function : 
  * 
  * @code
  * $example = 20;
  * $this->debug->variable($example);
  * @endcode
  * 
  * Array-type variables can be structured into a multi-level ierarchy. Sometimes extending
  * them in order to visualize all their fields can become ugly due to their huge size. This
  * is where the $level parameter comes into play. You can limit the display of an array to
  * a certain number of levels, by setting the level parameter.
  * 
  * @code
  * $example = array(
  * 	'one' => 1,
  * 	'two' => 2,
  * 	'twoext' => array('twoA' => 20, 'twoB' => 21)
  * );
  * $this->debug->variable($example, 2); // shows 2 of it's subarrays( in this case all )
  * $this->debug->varialbe($example, 1); // shows only the main array ( 'one', 'two' )
  * @endcode
  * 
  */
class Debug{
	
	protected
		/** 
		 * @var $debug_head_color
		 * array : Maps color of header with different error types
		 */
		$debug_head_color = array(),
		
		/**
		 * @var $debug_div_style
		 * string : Style of the div containing the message
		 */
		$debug_div_style,
		
		/**
		 * @var $debug_h1_style
		 * string : Style of the title of the div containing the message
		 */
		$debug_h1_style,
		
		/** 
		 * @var $debug_p_style
		 * string : Stype of the paragraph containing the message
		 */
		$debug_p_style;
	
	private
		/** 
		 * @var $dev_stage
		 * bool : The stage of the project, set from the config file
		 */
		$dev_stage,
		
		/** 
		 * @var $log_exceptions 
		 * bool : Log exceptions setting, set from the config file
		 */
		$log_exceptions,
		
		/**
		 * @var $log_exceptions_file
		 * string : File to log exceptions to, set from the config file
		 */
		$log_exceptions_file;
	
	private static
		/**
		 * @var $instance
		 * Debug : Singleton instance
		 */
		$instance = null;
	
	/** Singleton class
	 * 
	 * @param $dev_stage boolean            : true, if it's development stage
	 * @param $log_exceptions boolean       : true, if exceptions should be logged       
	 * @param $log_exceptions_file string   : path to the log file
	 */
	public static function getInstance( $dev_stage = true, $log_exceptions = '', $log_exceptions_file = '' ){
		if ( self::$instance === null ) self::$instance = new self( $dev_stage, $log_exceptions, $log_exceptions_file );
		return self::$instance;
	}
	
	/** Constructor
	 * 
	 * @see getInstance
	 */
	public function Debug( $dev_stage, $log_exceptions, $log_exceptions_file ){
		$this->dev_stage           = $dev_stage;
		$this->log_exceptions      = $log_exceptions;
		$this->log_exceptions_file = $log_exceptions_file;
		$this->debug_head_color    = array(
			'error'              => '#CC3333',
			'fatal'              => '#CC3333',
			'database error'     => '#CC3333',
			'uncaught exception' => '#CC3333',
			'framework error'    => '#CC3333',
			'default'            => '#3333CC'
		);
		$this->debug_div_style = 
			"color: #333; text-align: left;font-family: Arial; padding: 10px 16px; background: #FAFBFC; border: 1px solid #AAA;" . 
			" box-shadow: 0 1px 8px rgba(0, 0, 0, 0.3);-webkit-box-shadow: 0 1px 8px rgba(0, 0, 0, 0.25);" . 
			"-moz-box-shadow: 0 1px 8px rgba(0, 0, 0, 0.25); margin: 20px; min-width: 500px; clear: both;";
		$this->debug_h1_style =
			"font-size: 18px; font-weight:bold; padding: 0px 0px 10px 0px; margin: 0px 0px 20px 0px;" .
			"border-bottom: 1px solid #CCC;";
		$this->debug_p_style =
			"font-size: 12px; font-weight: none; font-family: Arial;";
	}
	
	/** Display message helper. Gets the header color
	 * 
	 * @param $level string : the level of the error
	 * 
	 * @return string       : header color
	 */
	private function headColor( $level ){
		if ( isset( $this->debug_head_color[$level] ) ){
			return $this->debug_head_color[$level];
		} else return $this->debug_head_color['default'];
	}
	
	/** Display message
	 * 
	 * @param $msg string/array : the message(s) to be displayed
	 * @param $level string     : type of message(s) to be displayed
	 */
	public function display( $msg = 'Unknown', $level = 'debug' ){
		if ( $this->dev_stage === 'development' ){
			echo '<div style="' . $this->debug_div_style . '">';
				echo '<h1 style="' . $this->debug_h1_style . '">Message type: <span style="color: ' . $this->headColor($level) . '">' . $level . '</span></h1>';
				if ( is_array( $msg ) ) {
					foreach( $msg as $key => $value ){
						echo '<p style="padding: 0px; margin: 10px 5px;' . $this->debug_p_style . '"><em style="font-weight: bold; margin-right: 5px;">' . $key . ': </em>' . $value .  '</p>';
					}
				} else echo '<p style="' . $this->debug_p_style . '">' . $msg .  '</p>';
			echo '</div>';
		}
	}
	
	/** Helper for printing variables : uses a recursive approach to show the variable contents
	 * 
	 * @param $var           : in case of array or object passes it recursively
	 * @param $name string   : used in case of a recursive approach
	 * @param $level numeric : number of recursive approaches
	 * 
	 * @return array         : strings containing the specified variables contents, each key on one line 
	 */
	private function getVarContents( $var, $name = '', $level ){
		if ( is_array($var) || is_object($var) ) {
			if ( is_object($var) ){
				$buffer[] = $name . 'Object with class ' . get_class($var);
				$var = get_object_vars($var);
			} else {
				$buffer[] = $name . 'Array with ' . count($var) . ' element(s)';
			}
			if ( $level > 0 ){
				$count = 0;
				foreach( $var as $key => $value ){
					$buffer = array_merge( $buffer, $this->getVarContents( $value, $name . '[' . $key . '] ' . ' - > ', $level - 1 ) );
				}
				return $buffer;
			} else {
				return $buffer;
			}
		} else {
			if ( is_bool($var) ){
				$var = $var ? 'TRUE' : 'FALSE';
			} else if ( is_resource($var) ){
				$var = strval($var) .' ('. get_resource_type($var).')';
			} else if ( is_null($var) ){
				$var = 'NULL';
			}
			$buffer[]  = $name . $var;
			return $buffer;
		}
	}
	
	/** Prints the get $var contents using the 'getVarContents' function
	 * 
	 * @param $var           : the variable to be printed
	 * @param integer $level : variable deepness in case of object or array
	 */
	public function variable( $var, $level = 0 ){
		$buffer = $this->getVarContents( $var, '', $level );
		$this->display( $buffer );
	}
	
	/** Logs an exception to a file
	 * 
	 * @param string $file_name : the name of the file
	 * @param string $str       : the string to be written
	 * 
	 * @throws FileNotFoundException
	 * @throws NoPermissionException
	 */
	private function logExceptionTo( $file_name, $str ){
		if ( !file_exists( $file_name ) ) throw new FileNotFoundException ( 'The file ' . $file_name . 'hasn\'t been found.' );
		$fd = fopen( $file_name, "a" );
		if ( !$fd ) throw new NoPermissionException( 'Cannot open file ' . $file_name . ' for writing.' );
		fwrite( $fd, $str, "\n" );
		fclose( $fd );
	}
	
	/** Helper function for the exception output.
	 * 
	 * @param array $trace : the trace as an array
	 * 
	 * @return array       : returns the trace as an array of strings
	 */
	private function exceptionTrace( $trace = array() ){
		$out = '';
		$args = '';
		if ( isset( $trace['file']) ) $out .= $trace['file'];
		if ( isset( $trace['line'] ) ) $out .= '(' . $trace['line'] . '):';
		if ( isset( $trace['class']) ) $out .= $trace['class'] . '->';
		if ( isset( $trace['function'] ) ) $out .= $trace['function'];
		if ( isset( $trace['args'] ) ) {
			foreach( $trace['args'] as $argument ){
				if ( $args !== '' ) $args .= ',';
				$args .= $argument;
			}
			$out .= '(' . $args . ')';
		}
		return $out;
	}
	
	/** Function for outputing the exception
	 * 
	 * @param Exception $e : the exception to the output
	 */
	public function exception( Exception $e ){
		$display = array(
			'Message' => $e->getMessage(),
			'File'    => $e->getFile() . ' on line <em style="font-weight:bold">' . $e->getLine() . '</em>'
		);
		if ( $e->getCode() !== 0 ) $display['Code'] = $e->getCode();
		$display['Stack trace'] = '';
		$traces = $e->getTrace();
		$count = 0;
		foreach ( $traces as $trace ){
			$display['#' . $count] = $this->exceptionTrace( $trace );
			$count++;
		}
		if ( $this->dev_stage !== 'production' )
			$this->display( $display, "exception '" . get_class( $e ) . "'" );
		if ( $this->log_exceptions && $this->log_exceptions_file !== '' ){
			$this->logExceptionTo( 
				$this->log_exceptions_file,
				sprintf("PHP Debug Exception:  %s in %s on line %d", $e->getMessage(), $e->getFile(), $e->getLine() )
			);
		}
	}
	
	
}