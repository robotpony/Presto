Presto for PHP
==============

Presto is a small library of tools and scripts for hacking together RESTful APIs in PHP. It's minimalistic, decoupled, and focused on making web apps the right way (encouraging nice URLs, RESTfulness, documentation, testing, and so on).

In Presto we use the best parts of PHP and LAMP, as they're intended to be used. For example, we rely on .htaccess for routing, and built-in PHP behaviours for delegation and loading of classes. The idea is that relying on the existing, simple approaches results in less framework, fewer bugs, and less to learn that isn't useful elsewhere.

PHP5.3 and better is requried, as Presto relies on anonymous functions, and other newer PHP features. This makes it possible to write very clean, simple web services.

Specifically, Presto relies on:

* Classes for grouped APIs
* Exceptions for failures, directly mapped to HTTP status
* Simpler generation of JSON returns
* Auto loading and delegation


A trivial example
-----------------


	class information extends API {
		
		// Set up the API
		public function __construct() {
			parent::__construct(get_class());	
			if (!isSignedIn()) throw new Exception('Auth required', 401);
		}
		
		// GET the local machine time
		public function get_time($ctx) { return (object) getdate(); }
		
		// FUTURE interfaces
		public function put_time($ctx) { throw new Exception('Not implemented', 501); }
		public function post_time($ctx) { throw new Exception('Not implemented', 501); }
	}

The class promises a GET, PUT, and POST interface for time. All other requests will return a standard 404. The PUT and POST are not implemented yet, and return a 501 status.

Presto maps requests to PHP scripts and objects, providing parameters and other calling information in the $ctx parameter. A request like:

	GET /information/time.json
	
Will map to:

	information.php information->get_time($ctx);
	
The context include all of the call parameters, the requested content-type, and request type. Handling of content-type is built in for simple cases, so that any data returned from an API is automatically transformed into the required type. For our simple example, returning the result of `datetime()` to the request produces:

	{
		"seconds" => 40
	    "minutes" => 58
	    "hours"   => 21
	    "mday"    => 17
	    "wday"    => 2
	    "mon"     => 6
	    "year"    => 2003
	    "yday"    => 167
	    "weekday" => "Tuesday"
	    "month"   => "June"
	    "0"       => 1055901520
	}

The result uses built-in conversion functions where possible.