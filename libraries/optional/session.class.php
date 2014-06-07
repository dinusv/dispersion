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
 * @brief User session manager.
 */
class Session extends Dispersion {
	
	const
		/** 
		 * string : Predefined password field
		 */
		PASSWORD_FIELD = 'password',
		
		/** 
		 * string : Predefined last_used field
		 */
		LASTUSED_FIELD = 'last_used';
	
	protected
		/** 
		 * @var $_table_restore
		 * string : Table to restore in the model after the class finishes working with the model.
		 */
		$_table_restore,
	
	/* 
	 * Configuration fields
	 * ----------------------------------------- */
		
		/** 
		 * @var $_table
		 * string : Table used for holding user data.
		 * Example : table = 'users'
		 */
		$_table,
		
		/** 
		 * @var $_table_column
		 * array : Map table columns with predefined fields. There are 2 predefined fields, 'password' and
		 * 'last_used'. The 'password' field is used to alert the session class to encrypt data before 
		 * matching it with the table field, and the 'last_used' stores the last time the user has logged in.
		 * 
		 * Example : $_table_column = array( 'password' => 'mypassword', 'last_used' => 'last_login' )
		 */
		$_table_column = array(),
		
		/** 
		 * 
		 * @var $_session_keys
		 * array : Map table data with the $_SESSION variables. Each key represents the name of session key to
		 * hold the data retrieved from the table, and the value represents the table column to get
		 * the data from.
		 * 
		 * Example : The following will make the password and the id of the user available in the $_SESSION
		 * variables ( $_SESSION['pwd'] = 'some password', $_SESSION['id'] = '1234' )
		 * $_session_keys = array( 'pwd' => 'password', 'id' => 'id' )
		 */
		$_session_keys = array(),
	
	/* 
	 * Session storage
	 * ----------------------------------------- */
	 	
	 	/** 
		 * @var $_use_db
		 * bool : Set this to true if you want to enable sessions to be stored in a database instead of a file
		 * in the system( linux stores them in the 'tmp' directory by default ). If you set this to true,
		 * you also need to set the table in which the sessions will be stored.
		 */
		$_use_db = false,
		
		/** 
		 * @var $_session_table
		 * string : Table to store sessions in. Before using this, you need to enable session storage in a database,
		 * by using the field ( $_use_db ) above. The table must have the following columns and respect
		 * their properties :
		 *     sid : CHAR(32), PRIMARY
		 *     expiration : INT NOT NULL
		 *     value : TEXT NOT NULL
		 */
		$_session_table = '',
	
	/* 
	 * Password encryption
	 * ----------------------------------------- */
	
		/** 
		 * @var $_algo
		 * string : Algorithm to be used. By default, blowfish is selected with : $2a
		 */
		$_algo = '$2a',
		
		/** 
		 * @var $_cost
		 * string : Number of times the encryption algorithm is used. Default : $10
		 */
		$_cost = '$10';
	
	/** Constructor 
	 */
	public function Session() {
		parent::__construct();
		$this->_table_restore = $this->model->table;
		if ( $this->_use_db ){
			session_set_save_handler(
				array(&$this, 'sqlSessionOpen'), 
				array(&$this, 'sqlSessionClose'),
				array(&$this, 'sqlSessionSelect'),
				array(&$this, 'sqlSessionWrite'),
				array(&$this, 'sqlSessionDestroy'),
				array(&$this, 'sqlSessionGarbageCollect')
			);
		}
	}
	
	/** Used to restore the table in the model after the class has finished working with it
	 * 
	 * @param mixed $return_value : the value to return after the table has been restored
	 * 
	 * @return mixed
	 */
	protected function restoreAndReturn( $return_value ){
		$this->model->setTable( $this->_table_restore );
		return $return_value;
	}
	
	/** Sets table for users
	 * 
	 * @param string $table
	 */
	public function changeTable( $table ){
		$this->_table = $table;
	}
	
	/* 
	 * Session helpers
	 * ----------------------------------------- */
	
	/** Get value from the session
	 * 
	 * @param string $key  : index from which to return the value
	 * 
	 * @return mixed
	 * 
	 * @throws IndexOutOfBoundsException
	 */
	public function valueAt( $key ){
		if ( isset( $_SESSION[$key] ) ){
			return $_SESSION[$key];
		} else throw new IndexOutOfBoundsException();
	}
	
	/** Set the value at the key specified
	 * 
	 * @param string-numeric $key : index to set the value to
	 * @param mixed $value : value to set
	 */
	public function setvalueAt( $key, $value ){
		$_SESSION[$key] = $value;
	}
	
	/* 
	 * Encryption helpers
	 * ----------------------------------------- */

	/** Generate a random string at the size specified
	 * 
	 * @param numeric $size : string length
	 * 
	 * @return string
	 */
	public function generateRnd( $size = 22 ){
		return substr(sha1( mt_rand() ), 0, $size);
	}
	
	/** Generate a crypted password using the blowfish algorithm
	 * 
	 * @param string $password : given string to be crypted
	 * @param string $salt     : salt to crypt by, auto-generated if param is missing
	 * 
	 * @return string          : crypted value
	 */	
	public function generatePass( $password, $salt = null ){
		if ( version_compare(PHP_VERSION, '5.3.0' ) < 0)
			throw new PhpVersionException(
				'Blowfish altorithm requires PHP version 5.3.0 or later.' . 
				'Please rewrite the generatePass method in the extended class of Session.'
			);
		if ( $salt == null ) $salt = $this->_algo . $this->_cost . "$" . $this->generateRnd();
		return crypt( $password, $salt );  
	}
	
	/** Compare the password with the hash ( this can be extended for using other crypting algorithms )
	 * 
	 * @param string $hash     : the hash value to compare with
	 * @param string $password
	 * 
	 * @return boolean         : true if passwords match
	 */
	public function checkPass( $hash, $password ){
		return ( $this->generatePass( $password, substr( $hash, 0, 29 ) ) === $hash );
	}
	
	/* 
	 * Session handlers
	 * ----------------------------------------- */
	 
	/** Creates a new session
	 * 
	 * @param array $data : needs to be present in order to start the new session, containing keys and values 
	 *                      to match with current ones
	 *
	 * @return boolean    : true, if data is valid and the session is created, false otherwise
	 */
	public function newSession( $data = null ){
		if ( $data != null && is_array($data) ){
			$have_pwd = false;
			$password_field = '';
			$password_row = $this->_table_column['password'];
			/* check if we have a password field */
			if ( isset($this->_table_column['password'] ) ){
				if ( $this->_table_column['password'] !== '' ){
					$have_pwd = true;
				}
			}
			/* check for the data in the database */
			$query = array();
			foreach ( $data as $key => $value ){
				if ( $have_pwd && $key === $this->_table_column['password'] ) {
					$password_field = $value;
				} else {
					$query[$key] = $value;
				}
			}
			/* select fields from database */
			$row = $this->model->setTable( $this->_table )->selectRow($query, $this->_table );
			if ( $row ){
				if ( $have_pwd )
					if ( $row->$password_row !== $this->generatePass( $password_field, substr( $row->$password_row, 0, 29 ) ) ) 
						return $this->restoreAndReturn( false );
				if ( session_id() == '' ) session_start();
				foreach( $this->_session_keys as $key => $value ){
					$_SESSION[$key] = $row->$value;
				}
				if ( isset($this->_table_column['last_used'] ) ){
					if ( $this->_table_column['last_used'] !== '' ){
						$this->model->setTable( $this->_table )->updateRows(
							array( $this->_table_column['last_used'] => date("Y-m-d H:i:s") ), $query
						);
					}
				}
				return $this->restoreAndReturn( true );
			} else return $this->restoreAndReturn( false );
		}
		/* function can be extended from here */
	}
	
	/** Checks the session according to the given session keys
	 * 
	 * @return boolean : true if the session exists and is valid, false otherwise
	 */
	public function checkSession(){
		if ( session_id() == '' ) session_start();
		if ( count($_SESSION) === 0 ) return $this->restoreAndReturn(0);
		foreach( $this->_session_keys as $key => $value ){
			if ( !isset( $_SESSION[$key] ) )
				return $this->restoreAndReturn(0);
		}
		return $this->restoreAndReturn(true);
	}
	
	/** Ends the session
	 */
	public function endSession(){
		if ( session_id() == '' ) session_start();
		/* unset variables */
		$_SESSION = array();
		/* delete the session cookie */
		if ( ini_get("session.use_cookies") ){
			$params = session_get_cookie_params();
			setcookie( session_name(), '', time() - 42000, 
				$params["path"], $params["domain"], 
				$params["secure"], $params["httponly"]
			);
		}
		/* destroy the session */
		session_destroy();
	}
	
	/* 
	 * Session database handlers
	 * ----------------------------------------- */
	
	/** Opens the connection and check if the table is set for the session handler
	 */
	public function sqlSessionOpen(){
		if ( $this->_session_table === '' ) {
			$this->error->displayAndDie("The table for storring sessions hasn't been set");
		}
	}
	
	/** Function that needs to be defined, in our case it does nothing
	 * 
	 * @return int
	 */
	public function sqlSessionCLose(){
		return 1;
	}
	
	/** Reads the session data from the database
	 * 
	 * @param integer $sid : the id of the session
	 * 
	 * @return string      : the value of the session in case it's found, or empty string otherwise
	 */
	public function sqlSessionSelect( $sid ){
		$row = $this->model->setTable( $this->_session_table )->selectRow( array( 
			'sid' => $sid,
			array('expiration', time(), '>' )
		));
		if ( $row ) return $this->restoreAndReturn( $row->value );
		else return $this->restoreAndReturn( "" );
	}
	
	/** Writes the session data to the database
	 * 
	 * @param string $sid   : the session id
	 * @param string $value : the value to write to the database
	 */
	public function sqlSessionWrite( $sid, $value ){
		/* get the maximum session lifetime */
		$lifetime = get_cfg_var("session.gc_maxlifetime");
		
		/* set the expiration date */
		$expires = time() + $lifetime;
		
		$row = $this->model->setTable( $this->_session_table )->selectRow( $sid, 'sid', $this->_session_table );
		if ( $row ){
			$this->model->updateRows(
				array(
					'expiration' => $expires,
					'value' => $value
				),
			 	array( 'sid' => $sid )
			);
		} else {
			$this->model->setTable( $this->_session_table )->insert(array( 
				'sid' => $sid,
				'expiration' => $expires,
				'value' => $value
			));
		}
		$this->model->setTable( $this->_table_restore );
	}
	
	/** Deletes all session information having the session id $sid
	 * 
	 * @param string $sid
	 */
	public function sqlSessionDestroy( $sid ){
		$this->model->setTable( $this->_session_table )->deleteRows( array( 'sid' => $sid),$this->_session_table );
		$this->model->setTable( $this->_table_restore );
	}
	
	/** Deletes all sessions that have expired
	 * 
	 * @param integer $lifetime : the lifetime of a session in seconds
	 */
	public function sqlSessionGarbageCollect( $lifetime ){
		$this->model->setTable( $this->_session_table )->deleteRows( array(
			array( 'expiration', time() - $lifetime, '<' )
		));
		return $this->restoreAndReturn( $this->model->affectedRows() );
	}
}