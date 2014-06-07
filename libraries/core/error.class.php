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
  * @brief Main error handling class
  *  
  * Redirects all php errors and exceptions here. Outputs errors only in development stage.
  * Uses Debug object to output errors.
  */
class Error{
	
	/** 
	 * @var $debug
	 * Debug : The object used for debugging
	 */
	private $debug = null;
	
	/** 
	 * @var $instance
	 * Error : Instance of this object
	 */
	public static $instance = null;
	
	/* 
	 * Configuration fields
	 * ----------------------------------------- */
	 
	private
		/** Stage of the project : development, production
		 * 
		 * @var $dev_stage
		 * bool : Stage of the project : development / production
		 */
		$dev_stage,
		
		/** 
		 * @var $code_line_count
		 * int : Used for reporting code from the source file
		 */
		$code_line_count,
		
		/** 
		 * @var $log_errors
		 * booL : Log errors to ini file if true
		 */
		$log_errors;
	
	/* 
	 * Error reporting constants
	 * ----------------------------------------- */
	 
	private
		/** 
		 * @var $e_error_all
		 * int : Contains all errors as bits
		 */
		$e_error_all,
		
		/** 
		 * @var $e_warning_all
		 * int : Contains all warnings as binary flags
		 */
		$e_warning_all,
		
		/** 
		 * @var $e_notice_all
		 * int : Contains all notices as binary flags
		 */
		$e_notice_all,
		
		/** 
		 * @var $e_debug
		 * int : Contains the debugging value as binary flag
		 */
		$e_debug,
		
		/** 
		 * @var $e_deprecated_all
		 * int : Contains the deprecated value as binary flag
		 */
		$e_deprecated_all;
	
	/** Class must be singleton 
	 * 
	 * @param Debug $debug_ob
	 * @param array $error_settings
	 * 
	 * @return Error : instance of this class
	 */
	public static function getInstance( $debug_ob = null, $error_settings ){
		if ( self::$instance === null ) self::$instance = new self( $debug_ob, $error_settings );
		return self::$instance;
	}
	
	/** Constructor
	 * 
	 * @see getInstance
	 */
	private function Error( $debug_ob, $error_settings ){
		$this->e_error_all      = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;
		$this->e_warning_all    = E_WARNING | E_USER_WARNING | E_CORE_WARNING | E_COMPILE_WARNING;
		$this->e_notice_all     = E_NOTICE | E_USER_NOTICE;
		$this->e_debug          = 0x10000000;
		$this->e_deprecated_all = E_DEPRECATED | E_USER_DEPRECATED;
		
		$this->debug            = $debug_ob;
		
		$this->code_line_count  = $error_settings['output_source_lines'];
		$this->dev_stage        = $error_settings['stage'];
		
		if ( $this->dev_stage === 'production' ) ini_set( 'display_errors', "Off" );
		else ini_set( 'display_errors', "On" );
		
		if ( !$error_settings['overwrite'] ) {
			if ( ini_get('log_errors') == "1" ) $this->log_errors = true;
			else $this->log_errors = false;
		} else {
			if ( $error_settings['log_errors'] ) {
				$this->log_errors = $error_settings['log_errors'];
				if ( isset( $error_settings['log_file'] ) ) {
					if ( $error_settings['log_file'] !== "" ){
						ini_set( 'error_log', $error_settings['log_file'] );
					}
				}
			} else 
				$this->log_errors = false;
		}
		
	}
	
	/** Reports code lines from the file
	 * 
	 * @param string $file   : path to the file 
	 * @param integer $line  : the line number the error occured at
	 * @param integer $level : number of lines to output
	 * 
	 * @throws NoPermissionException
	 * 
	 * @return array : the lines of code from the file, each line as a key
	 */
	private function getSource( $file, $line, $level ){
		/* Initialize fields */
		$level = $level / 2;
		$line = $line - 1; // since we are starting to count the lines from zero
		if ( $line - $level < 0 ) $from = 0;
		else $from = $line - $level;
		$return = array('Source report from file' => $file );
		
		$fp = fopen( $file, 'r' );
		if ( $fp ){
			/* Parse until required line */
			if ( $from !== 0 )
				for ( $i = 0; $i < $from; $i++ ){
					$buffer = fgets($fp, 4096);
				}
			/* Get the lines required */
			while ( ( $buffer = fgets($fp, 4096) ) !== false && ( $i < $line + $level ) ) {
        			$i++;
				$return['Line ' . $i] = $buffer;
   			}
    			fclose($fp);
		} else throw new NoPermissionsException( 'Cannot open file ' . $file . ' for parsing ');
		return $return;
	}
	
	/** The default error handler used by the framework
	 * 
	 * @param integer $errcode : the level of the error raised
	 * @param string $errstr   : error message
	 * @param string $errfile  : filename that the error was raised in
	 * @param integer $errline : the line number the error was raised in
	 * @param array $errvars   : points to the active symbol table at the point the error occured
	 * 
	 * @return boolean         : always true
	 */
	public function errorHandler( $errcode, $errstr, $errfile = '', $errline = 0, $errvars = array() ){
		
		$fatal = false;
		
		/* Handle the error code */
		if ( $errcode & $this->e_error_all ) {
			$type = 'error';
			$fatal = true;
		} else if ( $errcode & $this->e_warning_all ) {
			$type = 'warning';
		} else if ( $errcode & $this->e_notice_all ) {
			$type = 'notice';
		} else if ( $errcode & $this->e_debug ){
			$type = 'debug';
		} else if ( $errcode & $this->e_deprecated_all ){
			$type = 'deprecated';
		} else {
			$type = 'unknown';
		}
		
		/* Error text */
		if ( !is_array($errstr ) )
			$error_text = array( 'Cause' => $errstr );
		else $error_text = $errstr;
		if ( $errfile !== '' ) {
			$error_text['File'] = $errfile;
			if ( $errline !== 0  ) {
				$error_text['File'] .=  ' on line <em style="font-weight:bold">' . $errline . '</em>';
				/* Report from source */
				if ( $this->code_line_count > 0 ){
					if ( $this->code_line_count > 100 ) $this->code_line_count = 100;
					try {
						$error_text = array_merge( 
							$error_text,
							$this->getSource( $errfile, $errline, $this->code_line_count )
						);
					} catch ( NoPermissionsException $e ) {
						$error_text = array_merge(
							$error_text, 
							array ( 'Cannot get source' => $e->getMessage() )
						);
					}
				}
			}
		}
		if ( $this->dev_stage !== 'production')
			$this->debug->display( $error_text, $type );
		if ( $this->log_errors ) {
			if ( is_array( $errstr ) ) {
				$base = '';
				foreach( $errstr as $key => $value ){
					$base .= ' [' . $key . '] => ' . $value . ' ';
				}
				$errstr = $base;
			}
			error_log(sprintf("PHP %s:  %s in %s on line %d", $errcode, $errstr, $errfile, $errline));
		}
			
		if ( $fatal ) exit(1);
		return true;
	}
	
	/** The default exception handler for uncaught exceptions
	 * 
	 * @param Exception $e
	 */
	public function exceptionHandler( $e ){
		$display = array(
			'Exception' => get_class( $e ),
			'Message'   => $e->getMessage(),
			'Thrown at' => $e->getFile() . ' on line <em style="font-weight:bold">' . $e->getLine() . '</em>'
		);
		$trace = $e->getTrace();
		if ( isset( $trace[1]['file'] ) ){
			$display['Thrown at'] = $trace[1]['file'];
			if ( isset( $trace[1]['line']) ) {
				$display['Thrown at'] .= ' on line <em style="font-weight:bold">' . $trace[1]['line'] . '</em>';
				$display = array_merge(
					$display, 
					$this->getSource( $trace[1]['file'], $trace[1]['line'], $this->code_line_count ) );
			} 
		}
		if ( $this->dev_stage !== 'production' )
			$this->debug->display( $display, "uncaught exception" );
		if ( $this->log_errors )
			error_log(sprintf("PHP Uncaught Exception:  %s in %s on line %d", $e->getMessage(), $e->getFile(), $e->getLine() ) );
	}

	/** Static function to trigger an error. Sends the data to the error handler.
	 * 
	 * @see errorHandler
	 */
	public static function trigger( $errcode, $errstr, $errfile = '', $errline = 0 ){
		self::$instance->errorHandler( $errcode, $errstr, $errfile, $errline );
	}
	
	/** Restore default php.ini settings possibly affected by this class and return to the default php behavior
	 */
	public static function disable(){
		restore_error_handler();
		restore_exception_handler();
		ini_restore('log_errors');
		ini_restore('error_log');
		ini_restore('display_errors');
	}
	
}
