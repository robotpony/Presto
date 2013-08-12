<?php

/* PDO Database wrapper

	See PDO docs for details: http://www.php.net/manual/en/class.pdo.php

	Adds a few extensions for common batched operations.

	See related `extra/tagged-sql.php` for extra post-processing magic.
*/

class db extends PDO {

	private $statement;
	const USR_DEF_DB_ERR 	= '45000';
	const DB_NO_ERR 		= '00000';


	/* Create (or reuse) an instance of a PDO database */
	static function _instance($dsn, $user, $password, $config = null) {
		global $_db;
		if ($_db !== null) return $_db; // return cached

		if ($config === null)
			 $config = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' );

		try {
			 $_db = new db($dsn, $user, $password, $config);
		 } catch (Exception $e) {
			throw new Exception("Failed to connect to database.", 500, $e);
		 }

		return $_db;
	}

	/* Returns an array of classes based on the given SQL and bound parameters (see PDO docs for details) */
	function select($sql, $bound_parameters = array()) {

		// Expand any array parameters
		$this->expand_select_params($sql, $bound_parameters);

		$this->statement = $this->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$this->statement->execute($bound_parameters);
		$resultset = $this->statement->fetchAll(PDO::FETCH_CLASS);
		$this->errors();
		return $resultset;
	}
	/* Return a single row (throws on > 1 row) */
	function select_row($sql, $bound_parameters = array()) {
		$r = $this->select($sql, $bound_parameters);
		$c = count($r);
		if ($c === 0) throw new Exception("Found 0 rows", 500);
		elseif ($c !== 1) throw new Exception("Too many rows (".(count($r)).") returned from '$sql'", 500);
		return $r[0];
	}
	
	/* Return a simple type and object mapped set of records 
		
		Uses column aliases and type designation to generate object hierarchy.
		
		Features:
		
			* simple key format `any.number.of.subkeys:optional_type`
			* order of columns is not important
			* allows values to be type cast
			
		Not supported:
		
			* combining rows into sub-objects
			
		Example:
		
			SELECT
				SomeID AS `id:int`,
				FirstName AS `name.first`,
				LastName AS `name.last`,
				AnotherColumn AS `other`
			FROM SomeTable
			
		
			[{
				id: 1234,
				name: {
					'first': "Sideshow",
					'last': "Bob"
				},
				other: "some value"
			}, ...]
	*/
	function select_objects($sql, $bound_parameters = array()) {
	
		$rows = $this->select($sql, $bound_parameters);

		$d = '.'; $t = ':'; // delimiters for depth and type
		$o = array(); // output
		
		foreach ($rows as $r) { // each row in result set
			
			$row = array();
			$type = '';
			
			foreach ($r as $key => $value) { // each column

				// extract type :type if it exists
				if (strpos($key, $t)) {
					$p = explode($t, $key);
					$type = $p[1];
					$key = $p[0];
				} else { // Reset type to null if it isn't set to prevent type cascade
					$type = null;
				}
				// extract keys
			    $keys = strpos($key, $d) ? explode($d, $key) : array($key);

			    $ptr = &$row; // reduce re-allocs (using a ref)

				// create objects as needed
			    foreach ($keys as $k) {
			    
			        if (!isset($ptr[$k])) $ptr[$k] = array();
			        $ptr = &$ptr[$k];
			    }


				// adjust type if needed
				if (!empty($type)) settype($value, $type);
				
				// insert column value
			    if (empty($ptr)) $ptr = $value;
			    else $ptr[] = $value;
			}
			
			$o[] = $row; // add row to output
		}

		return $o;
	}	

	/* Provide a wrapper for updates which is really just an alias for the query function. */
	function update($sql, $bound_parameters = array()) {
		$this->query($sql, $bound_parameters);
	}
	/*
		General query. Use this when no specific results are expected. Example: attempting to delete a resource
		that may or may exist.

		$bound_parameters is an array of arrays with the 'value' member as the value to bind and the 'pdoType' as
		that parameter's PDO datatype.
	 */
	function query($sql, $bound_parameters = array()) {

		// Expand any array parameters
		$this->expand_query_params($sql, $bound_parameters);

		$this->statement = $this->prepare($sql);
		if (!empty($bound_parameters)) {
			foreach ($bound_parameters as $key => &$param) {
				$v = $param['value']; $t = $param['pdoType'];
				if (!$this->statement->bindValue($key, $v, $t))
					throw new Exception("Unable to bind '$v' to named parameter ':$key'.", 500);
			}
		}

		try {
			$this->statement->execute();
			$this->errors();
		} catch (Exception $e) {
			$this->errors(); // force errors() to run even after exceptions (for PDO::ERRMODE_EXCEPTION)
		}

	}

	/* Provide a wrapper for inserts which is really just an alias for the query function. */
	function insert($sql, $bound_parameters = array()) {
		$this->query($sql, $bound_parameters);
		if ($this->statement->rowCount() === 0)
			throw new Exception('Insert failed: no rows were inserted.', 409);
	}
	
	/* 
		Manage a multiple insertion
		
		* `$sql`: an `INSERT` statement of the form `INSERT INTO Foo (C1, ..., Cn) VALUES (v1, ..., vn)`
		* `$dataTypes`: an array containing the PDO data types of the data values of the form
			`array(0 => PDO::dataType, ..., n => PDO::dataType)`
		* `$data`: a 2D array of the form `array(0 => array(v01, ..., v0n), ..., m => array(vm1, ..., vmn))`
		
		1. Handled with `m` `INSERT` statements wrapped in a transaction.
		2. The `INSERT` is prepared first (via PDO).
		3. `m` `INSERT` statements are then executed, binding parameters at execution time. 
	*/
	public function multi_insert($sql, $dataTypes, $data) {
	
		if (empty($data))
			throw new Exception('Aborting: You provided no data to insert.', 409);
			
		if (empty($dataTypes))
			throw new Exception('Aborting: You did not provide types for data values (necessary for binding).', 409);
	
		try {
			$this->beginTransaction();
			
			$this->statement = $this->prepare($sql);
			
			foreach ($data as $i => $row) {
				if (count($row) !== count($dataTypes))
					throw new Exception("Transaction rolled back as parameter count does not match data value count for data row $i", 409);
					
				foreach ($row as $key => &$param) {
					$v = $param['value']; $t = $param['pdoType'];
					if (!$this->statement->bindValue($key, $v, $t))
						throw new Exception("Unable to bind '$v' to named parameter ':$key'.", 500);
				}
				$this->statement->execute();
				$this->errors();
			}
			
			$this->commit();
		}
		catch (Exception $e) {
			$this->rollBack();
			$m = $e->getMessage();
			throw new Exception("Transaction failed and rolled back: $m", 500);
		}
	}

	/* Provide a wrapper for deletes which is really just an alias for the query function. */
	function delete($sql, $bound_parameters = array()) {
		$this->query($sql, $bound_parameters);
		if ($this->statement->rowCount() === 0)
			throw new Exception('Delete failed: resource does not exist.', 404);
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

	/*
		Utility: expand any parameters passed in as an array (as PDO does not yet support this).

		For any parameter `p => array( k1 => v1, k2 => v2, ..., kn => vn)` in `$params`,
		`p` is expanded to `p_k1 => v1, p_k2 => v2, ..., p_kn => vn` all members of `$params`.

		For any label `:p` in `$sql`, `p` is replaced with `:p_k1, :p_k2, ..., :p_kn` in `$sql`.
	*/
	private function expand_select_params(&$sql, &$params) {

		$expanded = array(); // store expanded parameters until we're done
		$expandedKeys = array(); // store the keys of expanded arrays so we can unset them when we're done

		foreach ($params as $p => $arrayParam) {

			// only worry about arrays
			if (!is_array($arrayParam)) continue;
			
			// Empty arrays need to be handled explicitly so they don't cause array to string conversion exceptions at bind time.
			if (empty($arrayParam)) {
				$params[$p] = '';
				continue;
			}

			$expandedKeys[] = $p;
			$names = ''; // list of labels `:p_k1, :p_k2, ..., :p_kn` with which to replace `:p` in $sql
			foreach ($arrayParam as $k => $v) {

				// Ensure validity of members
				if ((!empty($v) && !is_scalar($v))) {
					$t = gettype($v);
					throw new Exception("Array parameter expansion error: members of array '$p' must be scalars, but '$k' is a '$t'.", 500);
				}

				$expanded["{$p}_{$k}"] = $v;
				$names .= ":{$p}_{$k}, ";
			}

			// replace :p in $sql with $names
			$names = rtrim($names, " ,");
			$sql = str_replace(":$p", $names, $sql);
		}

		// Nothing expanded
		if (empty($expandedKeys)) return;

		// Get rid of the now expanded array params
		foreach ($expandedKeys as $v)
			unset($params[$v]);

		// Merge in the expanded values
		$params = array_merge($params, $expanded);
	}

	/*
		Utility: expand any parameters passed in as an array (as PDO does not yet support this).

		For any parameter `p => array('value' => array( k1 => v1, k2 => v2, ..., kn => vn), 'pdoType' => PDO::SOME_PDO_TYPE)` in `$params`,
		`p` is expanded to
			`p_k1 => array('value' => v1, 'pdoType' => PDO::SOME_PDO_TYPE),
			p_k2 => array('value' => v2, 'pdoType' => PDO::SOME_PDO_TYPE),
			...,
			p_kn => array('value' => vn, 'pdoType' => PDO::SOME_PDO_TYPE)`
		all members of `$params`.

		For any label `:p` in `$sql`, `p` is replaced with `:p_k1, :p_k2, ..., :p_kn` in `$sql`.
	*/
	private function expand_query_params(&$sql, &$params) {

		$expanded = array(); // store expanded parameters until we're done
		$expandedKeys = array(); // store the keys of expanded arrays so we can unset them when we're done

		foreach ($params as $p => $arrayParam) {

			// only worry about arrays
			if (!is_array($arrayParam['value'])) continue;
			
			// Empty arrays need to be handled explicitly so they don't cause array to string conversion exceptions at bind time.
			if (empty($arrayParam['value'])) {
				$params[$p] = array('value' => '', 'pdoType' => PDO::PARAM_STR);
				continue;
			}

			$expandedKeys[] = $p;
			$names = ''; // list of labels `:p_k1, :p_k2, ..., :p_kn` with which to replace `:p` in $sql
			foreach ($arrayParam['value'] as $k => $v) {

				// Ensure validity of members
				if ((!empty($v) && !is_scalar($v))) {
					$t = gettype($v);
					throw new Exception("Array parameter expansion error: members of array '$p' must be scalars, but '$k' is a '$t'.", 500);
				}

				$expanded["{$p}_{$k}"] = array('value' => $v, 'pdoType' => $arrayParam['pdoType']);
				$names .= ":{$p}_{$k}, ";
			}

			// replace :p in $sql with $names
			$names = rtrim($names, " ,");
			$sql = str_replace(":$p", $names, $sql);
		}

		// Nothing expanded
		if (empty($expandedKeys)) return;

		// Get rid of the now expanded array params
		foreach ($expandedKeys as $v)
			unset($params[$v]);

		// Merge in the expanded values
		$params = array_merge($params, $expanded);
	}
}