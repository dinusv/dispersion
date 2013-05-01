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
 * @namespace : core
 * @file      : libraries/debug.class.php
 * @version   : 1.0
 */

class Debug{
	
	protected
		/** Maps color of header with different error types
		 * 
		 * @var array
		 */
		$debug_head_color = array(),
		
		/** Style of the div containing the message
		 * 
		 * @var string
		 */
		$debug_div_style,
		
		/** Style of the title of the div containing the message
		 * 
		 * @var string
		 */
		$debug_h1_style,
		
		/** Stype of the paragraph containing the message
		 * 
		 * @var string
		 */
		$debug_p_style;
	
	private
		/** The stage of the project, set from the config file, 
		 * 
		 * @var boolean
		 */
		$dev_stage,
		
		/** Log exceptions setting, set from the config file
		 * 
		 * @var boolean
		 */
		$log_exceptions,
		
		/** File to log exceptions to, set from the config file
		 * 
		 * @var string
		 */
		$log_exceptions_file;
	
	private static
		/** Singleton instance
		 * 
		 * @var Debug
		 */
		$instance = null;
	
	/** Singleton class
	 * 
	 * @param boolean $dev_stage           : true, if it's development stage
	 * @param boolean $log_exceptions      : true, if exceptions should be logged       
	 * @param string  $log_exceptions_file : path to the log file
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
	 * @param string $level : the level of the error
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
	 * @param string/array $msg : the message(s) to be displayed
	 * @param string $level     : type of message(s) to be displayed
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
	 * @param string $name   : used in case of a recursive approach
	 * @param numeric $level : number of recursive approaches
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