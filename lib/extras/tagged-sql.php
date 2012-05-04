<?php

/* A placeholder for tagged SQL post-processing (transforming result sets into object trees) */

class tagged_sql {

	private $rs = null;
	private $dom = null;
	
	function __construct(&$result_set) {
		$this->rs = &$result_set;
		$this->dominate();
	}
	
	/**
	 * Select hierarchically tagged SQL.
	 *
	 * Returns a DOM based on the tag structure in the derived column names
	 * in the form of a DOM (a multidimensional array).
	 *
	 * Tags are defined in the derived column names and are used to 
	 * build a hierarchical DOM resultset.
	 *
	 * Three types of tags:
	 * 1. Node Constructor tags:	`@id(classes)|path.to.node.nodeName|User Readable Label`
	 * 2. Attribute tags: 			`@attributeName|path.to.node.nodeName`
	 * 3. Simple node tags:			`nodeName(classes)|path.to.node|User Readable Label`
	 *
	 * The '(classes)' portion of tags, and the ':User Readable Label' portion, are optional.
	 *
	 * Constructor tags
	 * ----------------
	 * Nodes created with a constructor tag may have other nodes added to them through
	 * subsequent constructor tags.
	 * When a constructor tag is encountered a node is created at the specified level of the DOM.
	 * All constructor tags must be encountered first in the results (before attribute or simple nodes).
	 * Constructor tags must be ordered according to their depth in the resulting DOM:
	 *
	 * Example:
	 *
	 * Correct order:
	 *				AS `@id|rubric`
	 *				AS `@id|rubric.domain`
	 *
	 * Wrong order:
	 *
	 *				AS `@id|rubric.domain`
	 *				AS `@id|rubric`
	 * 
	 * Simple node tags and attribute tags
	 * -----------------------------------
	 *
	 * All attribute tags and simple node tags must be encountered after all node constructors
	 * are encountered.
	 *
	 * Attribute tags add an attribute at the level specified by the node path in the tag.
	 *
	 * Simple node tags add a node of the type:
	 * 		<nodeName @class="classes" @label="User Readable Label">
	 *			<value>$value</value>
	 *		</nodeName>
	 */
	private function dominate() {
		if (empty($this->rs)) return $this->dom;
		
		$r_constructorNode = 	'/^@id(?:\(.*\))?\|((?:\w+\.)*)(\w+)(?:\|(.+))?$/';
		$r_simpleNode = 		'/^(@?\w+)(?:\(.*\))?\|((?:\w+\.)*(?:\w+))(?:\|(.+))?$/';
		$r_classes = 			'/^.*\((.*)\).*$/';
		
		foreach ($this->rs as $row) {dump($row);
			$pathState = array();
			foreach ($row as $key => $value) {
				$currentNode = &$this->dom;
				
				if (!isset($value)) $value = ''; // clamp null values to '';
				
				// get the node level classes (if any)
				$classes = null;
				if (preg_match($r_classes, $key, $matches)) {
					$classes = $matches[1]; dump($classes);
				}
				
				if (preg_match($r_constructorNode, $key, $matches)) {
					// this tag will construct a node that may have other complex nodes as children.
					if ($value === '') {
						// no value means no ID ... we can't do anything with this
						continue;
					}
					
					$path = explode('.', $matches[1]);
					$nodeName = $matches[2];
					
					// navigate to the node defined by the path
					foreach ($path as $p) {
						if (isset($pathState[$p]) && isset($currentNode[$p.'-'.$pathState[$p]])) {
							$currentNode = &$currentNode[$p.'-'.$pathState[$p]];
						}
					}
					
					// create this node if it doesn't exist
					if (!isset($currentNode[$nodeName.'-'.$value])) {
						$currentNode[$nodeName.'-'.$value] = array();
					}
					
					// move into the node
					$currentNode = &$currentNode[$nodeName.'-'.$value];
					$pathState[$nodeName] = $value;
					
					// set the id for this node
					$currentNode['@id'] = $value;
					
					// set the node level class(es)
					if (isset($classes)) {
						$currentNode['@class'] = $classes;
					}
					
					// set the node label if there is one
					if (isset($matches[3]) && trim($matches[3]) != '') {
						$currentNode['@label'] = trim($matches[3]);
					}
				}
				else if (preg_match($r_simpleNode, $key, $matches)) {
					// this tag will construct a simple node (or attribute).
					$path = explode('.', $matches[2]);
					$nodeName = $matches[1];
					
					// navigate to the node defined by the path
					foreach ($path as $p) {
						if (isset($pathState[$p]) && isset($currentNode[$p.'-'.$pathState[$p]])) {
							$currentNode = &$currentNode[$p.'-'.$pathState[$p]];
						}
					}
					
					// create the node (or attribute)
					if (strpos($nodeName, '@') === 0) {
						// create the attribute
						$currentNode[$nodeName] = $value;
					}
					else {
						// create the simple node
						$currentNode[$nodeName] = array();
					
						// set the node label if there is one
						if (isset($matches[3]) && trim($matches[3]) != '') {
							$currentNode[$nodeName]['@label'] = trim($matches[3]);
						}
					
						// set the node level class(es)
						if (isset($classes)) {
							$currentNode[$nodeName]['@class'] = $classes;
						}
					
						// set the node value
						$currentNode[$nodeName]['value'] = $value;
					}
				}
			}
		}
		dump($this->dom);
		if (empty($this->dom)) return $this->dom;
		return (object) $this->dom;
	}
	
	public function &dom() {
		return $this->dom;
	}
}