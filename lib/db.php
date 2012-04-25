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

}