<?php 

class API extends REST {
		
	private $concepts;
	private $types;
	
	
	public function __construct($c) {	
		
		// guess valid REST concepts from class members
		
		foreach (get_class_methods($c) as $fn) {
		
			$method = strtok($fn, '_');
			$concept = strtok('');

			if (!empty($concept) && in_array($method, self::$METHODS))
				$this->concepts[] = $concept;
		
		}
	}
	
	public function validConcept($c) { return in_array($c, $this->concepts); }

}

?>