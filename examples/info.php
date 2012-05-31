<?php 

/* An API for returning information 

	Notice that the classname matches the filename, which will match the URLs it serves.
	
	This class will service requests like:
	
		example.com/info.json
		example.com/info/some/path.json
		
	The requests will be mapped to class functions named <method>_<thing>.<resource type>.
*/
class info extends API {
	
	public function __construct() {
		parent::__construct(get_class()); // required to automatically learn available routes via introspection

		// other startup here

	}
	
	// info.json (root get request)
	public function get($ctx) {

		if (count($ctx->params) > 1)
			throw new Exception('Too many parameters', 400); // will result in a proper 400 HTTP status

		return array('example' => 'This is some example information'); // will be returned as json, if json is requested
	}
	
	// 
	public function get_listing($ctx) {
	
	}
}