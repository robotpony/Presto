<?php

/* Database wrapper

	See PDO docs for details: http://www.php.net/manual/en/class.pdo.php

	Adds a few extensions for common batched operations.
	
	See related `extra/tagged-sql.php` for extra post-processing magic.
*/
class db extends PDO {

	private $statement; 
	const USR_DEF_DB_ERR 	= '45000'; 
	const DB_NO_ERR 		= '00000';
	
	/* Returns an array of classes based on the given SQL and bound parameters (see PDO docs for details) */
	function select($sql, $bound_parameters = array()) {		
		$this->statement = $this->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$this->statement->execute($bound_parameters);
		$resultset = $this->statement->fetchAll(PDO::FETCH_CLASS);
		$this->errors();
		return $resultset;
	} 
	
	/* Wrapper for updates.
		$bound_parameters is an array of arrays with the 'value' member as the value to bind and the 'pdoType' as 
		that parameter's PDO datatype.
	 */
	function query($sql, &$bound_parameters = array()) {$this->update($sql, $bound_parameters);}
	function update($sql, &$bound_parameters = array()) {	
		$this->statement = $this->prepare($sql);	
		foreach ($bound_parameters as $key => &$param) {
			$v = $param['value']; $t = $param['pdoType'];
			if (!$this->statement->bindValue($key, $v, $t))
				throw new Exception("Unable to bind '$v' to named parameter ':$key'.", 413);
		}
		
		$this->statement->execute();
		$this->errors();	
	}
	
	/* Provide a wrapper for inserts which is really just an alias for the update function. */
	function insert($sql, &$bound_parameters = array()) {
		$this->update($sql, $bound_parameters);
	}
	
	/* Return the number of rows affected by the last INSERT, DELETE, or UPDATE.  */
	function affected_rows() {
		return $this->statement->rowCount();
	}
	
	/* Throw error info pertaining to the last operation. */
	private function errors() {
		$e = $this->statement->errorInfo();
		if (empty($e[0]) || $e[0] === self::DB_NO_ERR) return;
		if (!empty($e[0]) && $e[0] === self::USR_DEF_DB_ERR) {
			$msg = !empty($e[2]) ? $e[2] : 'Application defined SQL error occurred.';
			throw new Exception($msg, 412);
		}
		else if (!empty($e[0]) && !empty($e[2])) {
			throw new Exception($e[2], 500);
		}
		else throw new Exception('Update failed for unknown reason.', 500);
	}
}