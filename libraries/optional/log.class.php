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
 * @version 1.2
 * @author DinuSV
 */

/** 
 * @ingroup libraries
 * @brief Logs data to specified files.
 * 
 * The constructor takes the file location as its argument. If you want to autoload this library, 
 * you can set the file using the setFile() method. Both the method and the constructor throw a 
 * FileNotFoundException in case the file does not exist, or a NoPermissionsException in case the file 
 * does not have permissions for writing.
 * 
 * @code
 * // Creating an object
 * $logger = new Log("myfile.txt");
 *  
 * // Setting the file in the constructor in case the library was autoloaded
 * $this->log->setFile("myfile.txt");
 * @endcode
 * 
 * To output a line, you can use the line() or lines() methods. Both methods throw a NoPermissionsException 
 * in case the file is not writable.
 * 
 * @code
 * $this->log->line('first statement');
 * $this->log->lines( array( 'second statement', 'third statement' ) );
 * @endcode
 * 
 * By default, an example of line that was printed will look like this: \n
 * `2012-01-31 22:30:30 --> first statement`\n
 * 
 * So, the line is composed of a date and separator. The two are customizable :
 * 
 * @code
 * $this->log->setDateFormat( 'Y M d H:i:s' )->setSeparator( ' ====== ' )->line( 'forth statement' );
 * // 2012 Jan 20 22:30:30 ====== forth statement
 * @endcode
 * 
 */
class Log{
	
	private
		/** 
		 * @var $file_handle
		 * resource : Log file handler
		 */
		$file_handle = null,
		
		/** 
		 * @var $date_format
		 * string : Format of the date to output to the file
		 */
		$date_format = "Y-m-d G:i:s",
		
		/** 
		 * @var $separator
		 * string : Separator between date and data for each line
		 */
		$separator = ' --> ';
	
	/** Constructor
	 * 
	 * @param string $file : the location of the file
	 * 
	 * @throws NoPermissionsException
	 * @throws FileNotFoundException
	 */
	public function Log( $file = '' ){
		if( $file !== '' )
			$this->setFile( $file );
	}
	
	/** Set the format of the date
	 * 
	 * @param string $date_format
	 *
	 * @return current object
	 */
	public function setDateFormat( $date_format = "H-m-d G:i:s" ){
		$this->date_format = $date_format;
		return $this;
	}
	
	/** Set the separator between the date and data in each line
	 * 
	 * @param string $separator
	 *
	 * @return current object
	 */
	public function setSeparator( $separator = ' --> ' ){
		$this->separator = $separator;
		return $this;
	}
	
	/** Set the file to log to
	 * 
	 * @param string $file : location of the file to log to
	 * 
	 * @throws NoPermissionsException
	 * @throws FileNotFoundException
	 * 
	 * @return current object
	 */
	public function setFile( $file ){
		if( file_exists( $file ) ){
			if ( !is_writable( $file ) ){
				throw new NoPermissionsException( "File is not writable. Please check file permissions." );
			}
			$this->file_handle = fopen( $file, "a" );
			if ( !$this->file_handle ) {
				throw new NoPermissionsException( "Cannot open file for writing." );
			}
		} else throw new FileNotFoundException( "Cannot find the file specified." );
		return $this;
	}
	
	/** Formats the line, adding the date
	 * 
	 * @param string $line : the line to return
	 * 
	 * @return string      : the formated line
	 */
	protected function lineForm( $line ){
		return date( $this->date_format ) . $this->separator . $line . "\n";
	}
	
	/** Outputs the line using the lineForm function
	 * 
	 * @param string $line
	 * 
	 * @throws NoPermissionException
	 * 
	 * @return current object
	 */
	public function line( $line ){
		if ( $this->file_handle === null ) throw NewInvalidArgumentTypeException( "The file has not been specified" );
		if ( fwrite( $this->file_handle , $this->lineForm( $line ) ) === false ) {
			throw new NoPermissionsException( "Cannot write to the file. Please check permissions." );
		}
		return $this;
	}
	
	/** Same as line, only this can output multiple lines
	 * 
	 * @param array $lines  : the lines to output
	 * @param string $lines : the function also accepts one parameter
	 * 
	 * @throws NoPermissionException
	 * 
	 * @return current object
	 */
	public function lines( $lines ){
		if ( is_array( $lines ) ){
			foreach( $lines as $line ){
				$this->line( $line );
			}
		} else $this->line( $lines );
		return $this;
	}
	
	/** Destructor, closes the file
	 */
	public function __destruct(){
		if ( $this->file_handle )
			fclose( $this->file_handle );
	}
	
}