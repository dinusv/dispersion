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
 * @brief MySql query abstractions. Base for all database models.
 * 
 * ## Simple Queries
 * 
 * Query with one result
 * 
 * @code
 * $result = $this->model->query( 'SELECT * FROM `my_table`' );
 * $selectedRows = $this->model->numRows( $result );
 * @endcode
 * 
 * Execute query and get number of affected rows.
 * 
 * @code
 * $this->model->execute( 'UPDATE `my_table` SET `key`=1 ');
 * 
 * // Get number of affectd rows
 * $affectedRows = $this->model->affectedRows();
 * @endcode
 * 
 * Query with one result
 * 
 * @code
 * $row = $this->model->queryOneRow( 'SELECT * FROM `my_table` WHERE id=1' );
 * @endcode
 * 
 * ## Selection Methods
 * 
 * The table used with the following methods is by default the same as the 
 * name of the model, or can be set using the Model::setTable() method.
 * 
 * Selecting, or executing simple selection queries can be done using the
 * `select()` method :
 * 
 * @code 
 * // SELECT * FROM `current_table`
 * $this->model->select();
 * 
 * // SELECT * FROM `current_table` WHERE id=5 AND otherid=6
 * $this->model->select( array( 'id' => 5, 'otherid' => 6 ) ); // OR
 * $this->model->select( 'WHERE id=5 AND otherid=6' );
 *  
 * // SELECT * FROM `current_table` ORDER BY id DESC
 * $this->model->select( 'ORDER BY id desc' );
 * 
 * // SELECT id, otherid FROM `current_table`
 * $this->model->select( null, 'id, otherid' ); // OR
 * $this->model->select( null, array( 'id', 'otherid' ) );
 * @endcode
 * 
 * Advanced selection can be done by using Model::selectRows
 * 
 * Selecting a single row can be done either by a given primary key, or by a where clause, depending
 * on the type of the `$condition`. In case `$condition` is not an array, the primary key is obtained
 * automatically and compared with the given parameter. Otherwise the where clause is used. 
 * 
 * A table where the primary key is `id` will create the following queries :
 * @code
 * // SELECT * FROM `current_table` WHERE id=5 LIMIT 1
 * $row = $this->model->selectRow(5); // OR
 * $row = $this->model->selectRow( array( 'id' => 5 ) );
 *  
 * // SELECT * FROM `current_table` WHERE id=5 AND otherid=6 LIMIT 1
 * $row = $this->model->selectRow( array( 'id' => 5, 'otherid' => 5 ) );
 * 
 * // SELECT * FROM `current_table` WHERE id>1 AND otherid=6 LIMIT 1
 * $row = $this->model->selectRow( array( array( 'id', '>', 1 ), 'otherid' => 6 );
 * @endcode
 * 
 */
class Model extends DataBaseMySql {
	
	protected
		/** 
		 * @var $_primary_key
		 * string : Holds the primary key of the table, used by functions that request or change one row. Can be
		 * set, or fetched by getPrimaryKey() method.
		 */ 
		$_primary_key = null;
	
	public
		/** 
		 * @var $table
		 * string : Name of the table the model will be working with
		 */ 
		$table;
	
	/** Constructor 
	 * 
	 * @param $db_connection array : db connection information
	 */
	public function Model( $db_connection ) {
		parent::__construct( $db_connection );
		$class_name  = strtolower(get_class($this));
		$this->table = substr( $class_name, 0, strlen( $class_name ) - 5 );
	}
	
	/* 
	 * Options
	 * ----------------------------------------- */
	 
	/** Set the default table
	 * 
	 * @param $table string : The table name
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
			$result = $this->query("show index from `" . $this->table . "` where Key_name = 'PRIMARY'");
			if ( $this->numRows( $result ) > 0 ){
				$row = $this->nextObject($result);
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
	 * @return $value string : the value in quotes or as it is, escaped
	 */ 
	private function toQuery( $value ){
		if ( !is_numeric($value) ) return "'" . mysql_real_escape_string($value) . "'";
		else return $value;
	}
	 
	/** Generate a query where clause
	 * 
	 * @param $val string/array        : array of values, or a string containing the query
	 * @param $logical_operator string : the default operator to be used between comparisons
	 * @param $compare_sign string     : the default sign used for comparisons
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
	 * @param $value string   : the value to look for
	 * @param $invalues array : the values to look in
	 * @param $not boolean    : if set to true, the value will not be contained
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
	 * @param $values array/string 
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
	 * @param $values array/string : if array, the values must have keys to match upon
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
	 * @param $conditions string/array : passed to the 'where' method if given
	 * @param $values string/array     : passed to the 'values' method if given
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
	 * @param $primary_value string/array : if array, it will pe passed to the 'where' method, otherwise
	 * it will be matched with the primary key of the table
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
		if ( isset( $options['group_by'] ) ) $query .= " group by " . $options['group_by'] . " ";
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
	 * @param $values string/array : parsed by the 'setValues' method
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
	 * @param $values array/string          : parsed by the 'setValues' method
	 * @param $primary_value string/numeric : the value of the primary key
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
	 * @param $values array/string : parsed by the 'setValues' method
	 * @param $where array/string  : parsed by the 'where' method
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
	 * @param $primary_value string/numeric : value of the primary key
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
	 * @param $where string/array : parsed by the 'where' method
	 */
	public function deleteRows( $where = "" ){
		$where = $this->where( $where );
		$this->execute( "delete from `" . $this->table . "` " . $where );
	}
	
	/** Count the total rows in a table
	 * 
	 * @param $options array  : parsed by the 'selectRows' method
	 * @param $options string : added at the end of the query
	 * 
	 * @return numeric : number of rows
	 */
	public function countRows( $options = "" ){
		if ( is_array($options) ) {
			$options['values'] = " count(*) a ";
			$result = $this->selectRows( $options );
			$row = $this->nextObject($result);
		} else $row = $this->queryOneRow( "select count(*) a from `" . $this->table . "` " . $options );
		return intval($row->a);
	}
	
}
