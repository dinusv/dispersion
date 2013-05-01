<?php  if ( ! defined('ROOT')) exit('Script access is forbidden');
/*                                                 **
| This file is part of Inevy Dispersion Framework.  |
| http://dispersion.inevy.com                       |
|                                                   |
| License : http://dispersion.inevy.com/license     |
|                                                   |
| Copyright 2010-2011 (c) inevy                     |
** -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  */

/** Provides main queries used for working with the database
 * 
 * @license   : http://dispersion.inevy.com/license
 * @namespace : database
 * @file      : libraries/database/modelmysql.class.php
 * @extends   : DataBaseMySql
 * @version   : 1.0 
 */

class Model extends DataBaseMySql {
	
	
	protected
		/** Holds the primary key of the table, used by functions that request or change one row. Can be
		 *  set, or fetched by getPrimaryKey() method.
		 * 
		 * @var string
		 */ 
		$_primary_key = null;
	
	public
		/** Name of the table the model will be working with
		 * 
		 * @var string
		 */ 
		$table;
	
	/** Constructor 
	 * 
	 * @param array $db : db connection information
	 */
	public function Model( $db_settings ) {
		parent::__construct( $db_settings['debug_queries'] );
		$this->connect( $db_settings['database'], $db_settings['host'], $db_settings['user'], $db_settings['password'] );
		$this->table = strtolower(get_class($this));
	}
	
	/* 
	 * Options
	 * ----------------------------------------- */
	 
	/** Set the default table
	 * 
	 * @param string $table : The table name
	 */
	public function setTable( $table ){
		$this->table = $table;
		$this->_primary_key = null;
		return $this;
	}
	
	/** Helper, get the primary key of the table
	  * 
	  * @return string
	 */
	public function getPrimaryKey(){
		if ( $this->_primary_key === null ){
			$result = $this->model->query("show index from `" . $this->table . "` where Key_name = 'PRIMARY'");
			if ( $this->model->numRows( $result ) > 0 ){
				$row = $this->model->nextObject($result);
				$this->_primary_key = $row->Column_name;
				return $this->_primary_key;
			} else $this->error->displayAndDie("This table has no primary key to select upon.", "sql error");
		} else return $this->_primary_key;
	}
	
	/* 
	 * Query generation helpers
	 * ----------------------------------------- */
	
	/** Checks if values is string or numeric, and adds quotes if necessary
	 * 
	 * @return string : the value in quotes or as it is, escaped
	 */ 
	private function toQuery( $value ){
		if ( !is_numeric($value) ) return "'" . mysql_real_escape_string($value) . "'";
		else return $value;
	}
	 
	/** Generate a query where clause
	 * 
	 * @param string/array $val        : array of values, or a string containing the query
	 * @param string $logical_operator : the default operator to be used between comparisons
	 * @param string $compare_sign     : the default sign used for comparisons
	 * 
	 * @return string : the query where clause generated
	 */
	public function where( $val, $logical_operator = 'and', $compare_sign = '=' ){
		$linked = true;
		if ( is_array( $val ) ){
			$s = "where ";
			foreach ( $val as $key => $value ){
				if ( !is_numeric($key) ){
					if ( !$linked ) $s .= " " . $logical_operator . " "; 
					$s .= "`" . $key . "`" . $compare_sign . $this->toQuery($value);
					$linked = false;
				} else {
					if ( is_array( $value ) ){
							if ( !$linked ) $s .= " " . $logical_operator . " ";
						if ( isset($value[0]) && isset($value[1]) && isset($value[2]) ){
							$s .= "`" . $value[0] . "`" . $value[2] . $this->toQuery($value[1]);
						} else if ( isset($value[0]) && isset($value[1]) ){
							$s .= "`" . $value[0] . "`" . $compare_sign . $this->toQuery($value[1]);
						} else if ( isset( $value[0] ) ){
							$s .= $value[0];
						}
						$linked = false;
					} else {
						$s .= " " . $value . " ";
						$linked = true;
					}
				}
			}
		} else return $val;
		return $s;
	}
	
	/** Generate a query where_in clause
	 * 
	 * @param string $value   : the value to look for
	 * @param array $invalues : the values to look in
	 * @param boolean $not    : if set to true, the value will not be contained
	 * 
	 * @return string : the query generated as a string
	 */
	public function whereIn( $value, $invalues, $not = false ){
		$s = "`" . mysql_real_escape_string($value) . "`";
		if ( $not ) $s .= " not";
		$s .= " in (";
		$first = true;
		foreach( $invalues as $val ){
			if ( !$first ) $s .= ", ";
			else $first = false;
			$s .= $this->toQuery($val);
		}
		$s .= ")";
		return array($s);
	}
	
	/** Generate a query string with comma separated values
	 * 
	 * @param array/string $values
	 * 
	 * @return string : the comma separated values as string
	 */
	public function values( $values = null ){
		if ( $values === null ) $values = "*";
		if ( is_array($values) ){
			$s = "";
			foreach ( $values as $value ){
				if ( $s != "" ) $s .= ", "; 
				$s .= mysql_real_escape_string($value);
			}
		} else $s = $values;
		return $s;
	}
	
	/** Generate a query string with comma separated values to be set
	 * 
	 * @param array/string $values : if array, the values must have keys to match upon
	 * 
	 * @return string : the comma separated values
	 */
	public function setValues( $values ){
		if ( is_array($values) ){
			$s = "";
			foreach ( $values as $key => $value ){
				if ( $s != "" ) $s .= ", ";
				$s .= '`' . $key . '`' . "=" . $this->toQuery($value);
			}
			return $s;
		} else return $values;
	}
	
	/* 
	 * Selection methods
	 * ----------------------------------------- */
	
	/** Quick select
	 * 
	 * @param string/array $conditions : passed to the 'where' method if given
	 * @param string/array $values     : passed to the 'values' method if given
	 * 
	 * @return resource                : MySql resource
	 */
	public function select( $conditions = "", $values = null ){
		$s = $this->values( $values );
		$s_cond = $this->where( $conditions );
		return $this->query("select " . $s . " from `" . $this->table . "` " . $s_cond );
	}
	
	/** Select a row based on a where clause or a primary key
	 * 
	 * @param string/array $value : if array, it will pe passed to the 'where' method, otherwise
	 *                              it will be matched with the primary key of the table
	 * 
	 * @return object             : the row selected
	 */
	public function selectRow( $primary_value ){
		if ( is_array( $primary_value ) ){
			$cond = $this->where( $primary_value );
		} else {
			if ( !is_numeric( $primary_value ) ) 
				$primary_value = "'" . mysql_real_escape_string( $primary_value ) . "'";
			else $primary_value = mysql_real_escape_string( $primary_value );
			$cond = "where `" . $this->getPrimaryKey() . "`=" . $primary_value;
		}
		return $this->queryOneRow( "select * from `" . $this->table . "` " . $cond );
	}
	 
	/** Advanced select
	 * 
	 * @param array $options : an array with specified keys as options
	 *   - values : array of values that will be selected
	 *   - order_by : key to order by
	 *   - order_direction : asc / desc
	 *   - group_by : key to group by
	 *   - nr_items : number of items the page will be limited to
	 *   - start_from_items : the start value for the items
	 *   - where : array of keys that will be equal to values, using the 'and' separator
	 *   - table : select from a custom table
	 * 
	 * @return resource      : MySql resource
	 */
	public function selectRows( $options = array() ){
		/* values */
		$values = '*';
		if ( isset( $options['values'] ) ){
			$values = $this->values( $options['values'] );
		}
		/* table */
		if ( isset( $options['table'] ) ) $table = $options['table'];
		else $table = $this->table;
		/* start query */
		$query = "SELECT " . $values . " FROM `" . $table . "` ";
		/* where */
		if ( isset( $options['where'] ) ) $query .= " " . $this->where( $options['where'] );
		/* order by */
		if ( isset( $options['order_by'] ) ) $query .= " order by " . $options['order_by'];
		/* order direction */
		if ( isset( $options['order_direction'] ) ) $query .= " " . $options['order_direction'] . "";
		/* group by */
		if ( isset( $options['group_by'] ) ) $query .= " group by `" . $options['group_by'] . "` ";
		/* items count and start from */
		if ( isset( $options['nr_items'] ) ) {
			$query .= " LIMIT ";
			if ( isset( $options['start_from_items'] )  ) $query .= $options['start_from_items'] . ",";
			$query .= $options['nr_items'];
		}
		return $this->query( $query );
	}

	/* 
	 * Insertion methods
	 * ----------------------------------------- */
	
	/** Inserts given values
	 * 
	 * @param string/array $values : parsed by the 'setValues' method
	 */
	public function insert( $values ) {
		$values = $this->setValues($values);
		$this->execute( "insert into `" . $this->table . "` set " . $values );
	}
	
	/* 
	 * Update methods
	 * ----------------------------------------- */
	 
	/** Update one row by its primary key
	 * 
	 * @param array/string $values          : parsed by the 'setValues' method
	 * @param string/numeric $primary_value : the value of the primary key
	 */
	public function updateRow( $values, $primary_value ){
		if ( !is_numeric( $primary_value ) ) 
			$primary_value = "'" . mysql_real_escape_string( $primary_value ) . "'";
		else $primary_value = mysql_real_escape_string( $primary_value );
		$primary = $this->getPrimaryKey();
		$values = $this->setValues( $values );
		$this->execute( "update `" . $this->table . "` set " . $values . " where `" . $primary . "`=" . $primary_value );
	}
	
	/** Update rows
	 * 
	 * @param array/string $values : parsed by the 'setValues' method
	 * @param array/string $where  : parsed by the 'where' method
	 */
	public function updateRows( $values, $where = "" ){
		$values = $this->setValues($values);
		$cond = $this->where( $where );
		$this->execute( "update `" . $this->table . "` set " . $values . " " . $cond );
	}
	
	/* 
	 * Deletion methods
	 * ----------------------------------------- */
	 
	/** Delete row by its primary key
	 * 
	 * @param string/numeric $primary_value : value of the primary key
	 */
	public function deleteRow( $primary_value ){
		if ( !is_numeric( $primary_value ) ) 
			$primary_value = "'" . mysql_real_escape_string( $primary_value ) . "'";
		else $primary_value = mysql_real_escape_string( $primary_value );
		$primary = $this->getPrimaryKey();
		$this->execute( "delete from `" . $this->table . "` where `" . $primary . "`=" . $primary_value );	
	}
	
	/** Deletes rows selected by a where clause
	 * 
	 * @param string/array $where : parsed by the 'where' method
	 */
	public function deleteRows( $where = "" ){
		$where = $this->where( $where );
		$this->execute( "delete from `" . $this->table . "` " . $where );
	}
	
	/** Count the total rows in a table
	 * 
	 * @param array $options  : parsed by the 'selectRows' method
	 * @param string $options : added at the end of the query
	 * 
	 * @return numeric : number of rows
	 */
	public function countRows( $options = "" ){
		if ( is_array($options) ) {
			$options['values'] = " count(*) a ";
			$result = $this->selectRows( $options );
			$row = $this->nextObject($result);
		} else $row = $this->queryOneRow( "select count(*) a from `" . $this->table . "` " . $options );
		return $row->a;
	}
	
}
