Presto is a toolkit for building RESTful APIs in PHP 5. It's lightweight, decoupled, and focused on making web apps the right way; using APIs and clean URLs to produce output in standard formats like JSON, HTML, and XML.  It encourages separating views completely from your model and controller code, and to animate your user interfaces in HTML, CSS, and JavaScript. It also uses existing tools and libraries for what they're good at, like letting Apache deal with routing, and letting PHP load and execute a mininimal amount of dynamic code.

How is Presto different?
========================

Presto does away with standard MVC. It focuses on APIs built from simple classes, relying on the web server for routing and on PHP autoloading for delegating requests to class member calls. User interfaces are left to static HTML views, animated with JavaScript and CSS.

### A quick example 

An API is a class with members named for each resource (or tree of resources). For example, an `apple` is a resource. You can request an `apple` over HTTP:

	GET apples/spartan.json&large+red


Presto maps the request to a file, class, and class member automatically. It loads the file, creates an instance of the class, and executes the member that best fits:


	/* Loaded from 'apples.php' */
	
	class apples extends API {
	
		public function get($ctx) {
			
			$thing = array(
				'name' => @$ctx->params[0],
				'size' => @$ctx->params[1]
				// etc
			);
			
			return $thing;
		}
	}


The `$thing` is automatically converted by Presto to the requested `Content-Type`, either implied by the request or the appropriate HTTP header. For formats not supported by default, *Output Adapters* can be defined and registered, or the type can be passed through for resources that are not based DOM style data.

Any HTTP request type is mapped automatically. For example, you can request a list of `apple` types available from the API:

	LIST apples.json&red

A LIST request is mapped to a function of the same name:

	public function list($ctx) { return array(); }


More complex resources are possible by either delegating (based on regex patterns), or by adding specific handlers. For example, you could add a `seeds` branch to the `apple` resource. Getting a list of seeds would map to:

	public function list_seeds($ctx) { array(); }

### What about errors?

Errors are handled by the toolkit as standard PHP exceptions and standard PHP errors are remapped (where possible) to exceptions to ensure that logic and code errors are returned to clients as valid API returns. This means that individual APIs do not need to check return values, nor do they need to `try` and `catch` unless they need to do something special. Presto maps the standard exceptions to HTTP statuses and output in whatever format was requested where possible.

For example, if you encounter a parameter error you can simply throw an exception:

	if (empty($param))
		throw new Exception("Missing required parameter", 400);
		
Presto translates the exception into a `400` with an appropriately encoded body.

The resulting API code is much more focused on carefully testing parameters, retrieving appropriate resources, and building rich DOMs instead of boilerplate code, managing responses, excessive error checking, routing, and other complex output generation.




Other interesting features
==========================

You can also:

* Add additional `content-types` by adding *output adapters* (`JSON`, simple `XML`, and simple `HTML` are built in)
* Add `content-type` filters to define what types of payloads a given resource supports.
* Add *custom delegation* for special resource types

**Note that PHP 5.3 or better is requried, as Presto relies on anonymous functions and other newer PHP features. This makes it possible to write very clean, simple web services.**
