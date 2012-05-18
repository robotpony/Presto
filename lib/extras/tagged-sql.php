<?php

/* Tagged SQL transformer.
 *
 * Post-processing for a result set where column names follow some tagging rules.
 * The tagging rules are used to traform the flat result set into a hierarchical
 * DOM. 
 */

class tagged_sql {
	
	/**
	# Hierarchical transformation for a tagged SQL result set.
	 
	Returns a DOM based on the tag structure in the derived column names.
	The returned DOM is either an object or an array based on the $asObj
	parameter.
	 
	Tags are column names and there are three types:
	
	1. Complex node tags:	`#nodeName(classes)[User Readable Label]`
	2. Attribute tags:		`@attributeName`
	3. Simple node tags:	`nodeName(classes)[User Readable Label]`
	 
	The '(classes)' portion of tags, and the '[User Readable Label]' portion, are optional.
	 
	## Complex tags
	
	Nodes created with a complex tag may have other nodes added to them be they complex, 
	simple nodes, or attributes. They are branches in the resulting DOM tree.
	 
	For efficiency the DOM is constructed in place and in the order that rows and columns 
	are encountered in the result set. When a constructor tag is encountered a branch is 
	created at the current level of the DOM if it does not already exist. Subsequent nodes 
	are created within the branch (leaves or other branches).
	 
	Simple node tags must directly follow the constructor tag they apply to.
	 
	Examples:
	~~~
	Correct order:
				r.RubricID 		AS `#rubric`,
				r.Title			AS `title[Rubric Title]`,
				d.DomainID		AS `#domain`,
				d.Title			AS `title[Domain Title]`
	 
	Wrong order:
	 			r.RubricID 		AS `#rubric`,
				d.Title			AS `title[Domain Title]`,
				d.DomainID		AS `#domain`,
				r.Title			AS `title[Rubric Title]`
	
	Wrong order:
	 			r.RubricID 		AS `#rubric`,
				d.DomainID		AS `#domain`,
				r.Title			AS `title[Rubric Title]`,
				d.Title			AS `title[Domain Title]`
	~~~
	
	## Simple node tags and attribute tags
	 
	Attribute tags add an attribute at the current node in the DOM.
	
	Example:
	~~~
	[currentnode] => Array
	(
		[@attributeName] => attributeValue
	)
	~~~
	
	Simple node tags add a simple node to the current (complex) node of the
	DOM. Simple nodes will have a value and may have a label or classes, but that's it,
	they do not contain complex nodes or other attributes. They are leaves in the DOM:
	~~~
	[simpleNode] => Array
	(
		[@label] => Human readable label,
		[@class] => class1 class2 class3 ... classn
		[value] => The value pulled from the DB
	)
	~~~
	
	## Result set row ordering
	
	The transform depends on the row ordering of the result set. The purpose of this
	transform is to take flat result sets and transform them into the tree they represent.
	
	For example the result set:
	~~~
	| RubricID | DomainID | ElementID |
	| -------- | -------- | --------- |
	|        1 |        1 |         1 |
	|        1 |        1 |         2 |
	|        1 |        2 |         3 | 
	|        1 |        2 |         4 |
	~~~
	
	Is transfomed into the DOM:
	~~~
	
	|¯¯¯¯¯¯¯¯| 
	| Rubric |
	|  @id:1 |
	|________|
	    |
	    |        |¯¯¯¯¯¯¯¯|
	    |------->| Domain |------------->|¯¯¯¯¯¯¯¯¯|
	    |        | @id:1  |       |      | Element |
	    |        |________|	      |      |  @id:1  |
	    |                         |      |_________|
	    |                         |
	    |                         |----->|¯¯¯¯¯¯¯¯¯|
	    |                                | Element |
	    |                                |  @id:2  |
	    | 								 |_________|
	    |
	    |        |¯¯¯¯¯¯¯¯|              
	    |------->| Domain |------------->|¯¯¯¯¯¯¯¯¯|
	             | @id:2  |       |      | Element |
	             |________|	      |      |  @id:3  |
	                              |      |_________|
	                              |
	                              |----->|¯¯¯¯¯¯¯¯¯|
	                                     | Element |
	                                     |  @id:4  |
	     								 |_________|
	   		 
						  
							  	  
	~~~
	
	Note that if you do not order the rows in your result set in this way then the
	there is no guarantee about the shape of the resulting DOM, except that it will
	be wrong.
	
	*/
	public function &dominate(&$rs, $asObj = true) {
		$dom = array();
		if (empty($rs)) return $dom;
		
		$r_constructorNode = 	'/^#(\w+)(?:\(.*\))?(?:\[(.+)\])?$/';
		$r_simpleNode = 		'/^(@?\w+)(?:\(.*\))?(?:\[(.+)\])?$/';
		$r_classes = 			'/^.*\((.*)\).*$/';
		
		foreach ($rs as $row) {
			$currentNode = &$dom;
			foreach ($row as $key => $value) {
				
				if (!isset($value)) $value = ''; // clamp null values to '';
				
				// get the node level classes (if any)
				$classes = null;
				if (preg_match($r_classes, $key, $matches)) {
					$classes = $matches[1];
				}
				
				if (preg_match($r_constructorNode, $key, $matches)) {
					// this tag will construct a node.
					if ($value === '') {
						// no value means no ID ... we can't do anything with this
						break;
					}
				
					$nodeName = $matches[1];
					
					// move into the node
					$currentNode = &$currentNode[$nodeName][$value];
					
					// set the node level class(es)
					if (isset($classes)) {
						$currentNode['@class'] = $classes;
					}
					
					// set the node label if there is one
					if (isset($matches[2]) && trim($matches[2]) != '') {
						$currentNode['@label'] = trim($matches[2]);
					}
				}
				else if (preg_match($r_simpleNode, $key, $matches)) {
					// this tag will construct a simple node (or attribute).
					$nodeName = $matches[1];
					
					// create the node (or attribute)
					if (strpos($nodeName, '@') === 0) {
						// create the attribute
						$currentNode[$nodeName] = $value;
					}
					else {
						// create the simple node
						$currentNode[$nodeName] = array();
					
						// set the node label if there is one
						if (isset($matches[2]) && trim($matches[2]) != '') {
							$currentNode[$nodeName]['@label'] = trim($matches[2]);
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
		if (empty($dom)) return $dom;
		if ($asObj) $dom = (object) $dom;
		return $dom;
	}
}