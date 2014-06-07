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
 * @ingroup database
 * @brief Database MySql connection
 * 
 * Executes mysql queries, offers query debugging and advanced query error management.
 */
class DataBaseMySql extends Dispersion{
	
	private
		/** 
		 * @var $_connection
		 * resource : Connection to the mysql server
		 */
		$_connection = null,
		
		/** 
		 * @var $defaultDebug
		 * bool : Debug tables
		 */
		$defaultDebug,
		
		/** 
		 * @var $nr_queries
		 * int : Total number of queries executed
		 */
		$nr_queries,
		
		/** 
		 * @var $last_result
		 * resource : Result of the last query executed
		 */
		$last_result,
		
		/** 
		 * @var $sql_affected_rows
		 * int : Affected rows of the last query executed
		 */
		$sql_affected_rows;
	
	/** Constructor
	 * 
	 * @param $db_connection DatabaseConnection : Database connection
	 */
	public function DataBaseMySql( $db_connection ){
		parent::__construct();
		$this->nr_queries   = 0;
		$this->last_result  = null;
		$this->defaultDebug = $db_connection->debugQueries();
		$this->_connection  = $db_connection;
	}
	
	/**
	 * @return used database connection
	 */
	public function connection(){
		return $this->_connection;
	}
	
	/* 
	 * Query Database Methods
	 * ----------------------------------------- */
	
	/** Query database
	 * 
	 * @param string $query        : query to send to the database
	 * @return sqlresource $result
	 */
	public function query( $query ){
		$this->nr_queries++;
		$this->last_result = mysql_query($query);
		if ( !$this->last_result ) {
			$this->debugQuery ( "error", $query, $this->last_result );
		}
		if ( $this->defaultDebug == true ) $this->debugQuery( "debug", $query, $this->last_result );
		return $this->last_result;
	}
	
	/** Execute query with no result return value
	 * 
	 * @param string $query    : query to execute
	 */
	public function execute( $query ){
		$this->nr_queries++;
		$result = mysql_query($query);
		if ( !$result ) $this->debugQuery( "error", $query, $result );
		if ( $this->defaultDebug == true ) $this->debugQuery( "debug", $query );
		$this->sql_affected_rows = mysql_affected_rows();
	}
	
	/** Returns the next object in the database
	 * 
	 * @param sqlresource $result : optional, the last result will be used if none is added
	 * 
	 * @return object             : the next row
	 */
	public function nextObject( $result = null ){
		if ( $result === null ) $result = $this->last_result;
		return mysql_fetch_object($result);
	}
	
	/** Get the number of rows
	 * 
	 * @param sqlresource $result : optional, the last result will be used if none is added
	 * 
	 * @return numeric            : number of rows
	 */
	public function numRows( $result = null ){
		if ( $result == null ) return mysql_num_rows( $this->last_result );
		else return mysql_num_rows( $result );
	}
	
	/** Get the number of affected rows by the last query
	 * 
	 * @return numeric
	 */
	public function affectedRows(){
		return $this->sql_affected_rows;
	}
	
	/** Return unique result 
	 * 
	 * @param string $query : the query to fetch the object upon
	 * 
	 * @return object       : the result as a row object
	 */
	public function queryOneRow( $query ){
		$query = $query . " LIMIT 1";
		$this->nr_queries++;
		$result = mysql_query($query);
		if ( !$result ) $this->debugQuery( "error", $query, $this->last_result);
		if ( $this->defaultDebug == true ) $this->debugQuery( "debug", $query, $result );
		return mysql_fetch_object($result);
	}
	
	/** Get the last inserted value's id 
	 * 
	 * @return string/numeric
	 */
	public function lastId(){
		return mysql_insert_id();
	}
	
	/** Get number of queries executed on the database server 
	 * 
	 * @return numeric : number of queries
	 */
	public function getQueriesCount(){
		return $this->nr_queries;
	}
	
	/** Go back to the first element of the result line
	 *
	 * @param sqlresource $result 
	 */
	public function resetRow( $result = null ){
		if ( mysql_num_rows($result) > 0 ) mysql_data_seek($result, 0);
	}
	
	/** Function for debugging and error reporting 
	 * 
	 * @param string $reason      : 'error'/'debug'
	 * @param string $query       : the query used for the result
	 * @param sqlresource $result
	 */
	private function debugQuery( $reason, $query, $result = null ){
		if ( $reason === "error" ) Error::trigger( E_USER_ERROR, array('Query' => htmlentities($query), 'Sql' => mysql_error()));
		else {
			$display = array( 'Query' => htmlentities($query) );
			if ( $result === null ) $display['Affected Rows'] = mysql_affected_rows();
			else {
				$display['Result'] = "</p><table style=\"margin: 2px; border: 1px solid #000; font-size: 12px;\">";
				/* Table header */
				$display['Result'] .= "<thead style=\"margin: 2px; border: 1px solid #000;\">";
				$numFields = mysql_num_fields($result);
				$tables = array(); $nbTables = -1; $lastTable = ""; $fields = array(); $nbFields = -1;
				while ( $column = mysql_fetch_field($result) ){
					if ( $column->table !== $lastTable ){
						$nbTables++;
						$tables[$nbTables] = array( 'name' => $column->table, 'count' => 1 );
					} else {
						if ( !isset( $tables[$nbTables]['count'] ) ) $tables[$nbTables]['count'] = 0;
						$tables[$nbTables]['count']++;
					}
					$lastTable = $column->table;
					$nbFields++;
					$fields[$nbFields] = $column->name;
				}
				for ( $i = 0; $i <= $nbTables; $i++ )
					$display['Result'] .= "<th style=\"padding:2px 5px;\" colspan=\"" . $tables[$i]['count'] . "\">" . $tables[$i]['name'] . "</th>";
				$display['Result'] .= "</thead><thead>";
				for ( $i = 0; $i <= $nbFields; $i++ )
					$display['Result'] .= "<th style=\"padding:2px 5px; font-weight: bold;\">" . $fields[$i] . "</th>";
				$display['Result'] .= "</thead><tbody>";
				/* End header */
				while ( $row = mysql_fetch_array($result) ) {
					$display['Result'] .= "<tr>";
					for ( $i = 0; $i < $numFields; $i++ )
						$display['Result'] .= "<td style=\"padding:2px 5px;\">" . htmlentities($row[$i]) . "</td>";
					$display['Result'] .= "</tr>";
				}
				$display['Result'] .= "</tbody></table><p>";
				$this->resetRow($result); 
			}
			$this->debug->display($display, 'table debug');
		}
	}
	
     
	/** Close connection to the database 
	 */
	public function close(){
		mysql_close();
		$this->connection = null;
	}
	
}