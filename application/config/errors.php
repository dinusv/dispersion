<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
	
	/*
	 * Inevy Dispersion -  errors configuration file
	 * 
	 * @version : 1.0
	 * @file    : application/config/config.php
	 * 
	 * --------------------------------------------
	 */
	
	/** Debug queries. 
	 * 
	 * True : All the results from queries will be displayed.
	 * False: Only database errors will be displayed.
	 */
	Config::db( 'debug_queries', false );
	
	/** Set the project stage. 
	 * 
	 * development : error reporting will be set to show all errors and notices.
	 * production  : error reporting as well as the framework debugger will be disabled.
	 */
	Config::errors( 'stage', 'development' );
	
	/** The 'phpini' settings overwrite the ones from the php.ini file. If you want to use the 
	 * ones from the .ini file, you can disable these here by setting the 'overwrite' to false.
	 */
	Config::phpini( 'overwrite',  true );
	
	/** Set this to true if you want to log your errors. This setting will be taken into account
	 * only if you enable overwriting your php.ini file.
	 */
	Config::phpini( 'log_errors', true );
	
	/** Set the path for the logfile, if you leave this empty, the default path from the php.ini
	 * file will be used.
	 */
	Config::phpini( 'log_file', ROOT . DS . 'tmp' . DS . 'errorlog.txt' );
	
	/** Whenever php encounters an error, you can set the framework to print the lines of code 
	 * where the error occured. The number can be any number smaller than 100, and represents 
	 * the number of lines to be displayed around the line of code where the error occured. If
	 * you set this to 0, the lines woun't be printed. The default value is 3.
	 */
	Config::errors( 'output_source_lines', 3 );
	
	/** Most exceptions are caught and displayed to be debugged. All exceptions thrown in the
	 * controller methods are caught, and in case the project is in the development stage, these
	 * exceptions will be displayed for debugging. In case the project is in production stage,
	 * and the displaying of errors is disabled, these caught exceptions can be logged to a file.
	 * If you set this to true, you need to specify the file to log them to below.
	 */
	Config::errors( 'log_exceptions', false );
	
	/** Set the file to log the exceptions to
	 */
	Config::errors( 'log_exceptions_file', '' );
	
	/** In an attempt to visit a page that does not exist, you can set the file or message to be
	 * displayed, or you can redirect the user to a specified page.
	 * 
	 * @example 
	 * Display a message
	 * Config::errors( 'page_not_found', 'msg' );
	 * Config::errors( 'page_not_found_param', '<h1>The page you are trying to access does not exist.</h1>' );
	 * 
	 * @example
	 * Redirect
	 * Config::errors( 'page_not_found', 'redirect' );
	 * Config::errors( 'page_not_found_param', 'home/index' );
	 * 
	 * @example
	 * Display file
	 * Config::errors( 'page_not_found', 'file' );
	 * Config::errors( 'page_not_found_param', 'ROOT .  DS . 'pagenotfound.html' );
	 */
	 Config::errors( 'page_not_found', 'msg' );
	 Config::errors( 'page_not_found_param', 'The page you are trying to access does not exist.' );
	