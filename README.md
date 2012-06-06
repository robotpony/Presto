Presto - PHP REST toolkit
=========================

Presto is a small library for building simple, RESTful APIs using PHP. It's lightweight, decoupled, and focused on making web apps the right way; using APIs with clean URLs, which produce simple, clean output in standard formats like JSON, HTML, and XML.

How is it different?
--------------------

Presto has no views, no models, and no controllers[^1]. Instead it focuses on APIs built from simple classes. It encourages applications to be based on APIs, which feed web applications animated with HTML/CSS/JavaScript.

A Presto API is simply a PHP class that maps to a resource or tree of resources. It has:

* Public members that map to requests (`GET thing/details.json` would map to `$thing->get_details()`)
* Request parameters and input payloads that are packaged up and sanitized
* Each route returns data as DOMs, which are automatically adapted to the requested `ContentType`
* Errors are automatically converted into returned HTTP statuses

The resulting code is focused on resources and the rules for those resources, and not boilerplate, excessive error checking, routing, and output generation.

[^1]: Note that Presto avoids MVC by solving a smaller problem and relying on built in functionality for delegation and generating output. 

How it works
------------

Presto uses the best parts of PHP and Apache, as they're intended to be used. It relies on `.htaccess` for routing, and built-in PHP behaviours for delegation and class loading. The principle is that relying on existing, simple approaches results in less framework, fewer bugs, and less to learn that isn't useful elsewhere.

*Note: PHP 5.3 or better is requried, as Presto relies on anonymous functions, and other newer PHP features. This makes it possible to write very clean, simple web services.*

An example API
--------------

	class info 
		extends API {
		
		// Set up the API
		public function __construct() {
			parent::__construct(get_class());	
			if (!self::isSignedIn()) throw new Exception('Auth required', 401);
		}
		
		// GET info/time.json - gets the local machine time
		public function get_time($ctx) { 
			$dom = (object) getdate();
			return $dom; // <-- returns the DOM as JSON
		}
		
		// PUT info/time.json
		public function put_time($ctx) { throw new Exception('Not implemented', 501); }
		// POST info/time.json
		public function post_time($ctx) { throw new Exception('Not implemented', 501); }
	}

The class promises a GET, PUT, and POST interface for time. All other requests will return a standard 404. The PUT and POST are not implemented yet, so they return a 501 (not implemented).

Presto maps requests to PHP files and objects, providing parameters and other calling information in the `$ctx` parameter. A request like:

	GET /info/time.json
	
Maps to:

	[info.php] info->get_time($ctx);
	
The context includes the call parameters, the `content-type`, and request method. Handling of `content-type` is built in for simple cases, so that any data returned from an API is automatically transformed into the required type. For our simple example, returning the result of `datetime()` to the request produces:

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

Advanced features
-----------------

There are a number of ways you can extend Presto. You can:

* Add additional `content-types` by adding *output adapters* (`JSON`, simple `XML`, and simple `HTML` are built in)
* Add `content-type` filters to define what types of payloads a given resource supports.
* Add *custom delegation* for special resource types
