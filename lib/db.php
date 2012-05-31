<?php

/* Database wrapper

	See PDO docs for details: http://www.php.net/manual/en/class.pdo.php

	Adds a few extensions for common batched operations.
	
	See related `extra/tagged-sql.php` for extra post-processing magic.
*/
class db extends PDO {

	private $statement; 
	
	/* Returns an array of classes based on the given SQL and bound parameters (see PDO docs for details) */
	function select($sql, $bound_parameters = array()) {		
		$this->statement = $this->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$this->statement->execute($bound_parameters);
		return $this->statement->fetchAll(PDO::FETCH_CLASS);	
	} 
	
	/* Wrapper for updates.
		$bound_parameters is an array of arrays with the first member as the value to bind and the second as 
		that parameter's PDO datatype.
	 */
	function update($sql, &$bound_parameters = array()) {	
		$this->statement = $this->prepare($sql);	
		foreach ($bound_parameters as $key => &$param) {
			if (!$this->statement->bindParam($key, $param[0], $param[1]))
				throw new Exception("Unable to bind '{$param[0]}' to named parameter ':$key'.", 413);
		}

		return $this->statement->execute();	
	}
	
	/* Provide a wrapper for inserts which is really just an alias for the update function. */
	function insert($sql, &$bound_parameters = array()) {
		return $this->update($sql, $bound_parameters);
	}
	
	/* Return error info pertaining to the last operation. */
	function errors() {
		return $this->statement->errorInfo();
	}
	
	/* Return the number of rows affected by the last INSERT, DELETE, or UPDATE.  */
	function affected_rows() {
		return $this->statement->rowCount();
	}

}