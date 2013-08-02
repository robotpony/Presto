# Presto 1.2 

*A simple PHP + REST toolkit*

Presto is a library for building RESTful APIs in PHP `5.3+`. It's lightweight, decoupled, and focused on making web apps  using APIs and clean URLs that produce output in standard formats like JSON, HTML, and XML. It encourages separating APIs from user interfaces, and views, model, and controller code. It also uses existing tools and libraries for what they're good at, like letting Apache deal with routing, and letting PHP load and execute a minimal amount of dynamic code.

<aside style="border: 1px solid rgba(0,0,0,.15); padding: 1em .5em; background-color: rgba(0,0,0,.05); text-align: center; font-size: 16pt; letter-spacing: 2px; margin-top: 2em;">
What's changed in 1.2? See [Github](https://github.com/robotpony/Presto/pulls?direction=desc&page=1&sort=created&state=closed) for details.
</aside>

## How is Presto different?

Presto simplifies MVC to fit REST requests in PHP, minimizing the amount of framework code implied by each resource request. It allows you to build up an API from classes, relying on the web server for routing and on PHP autoloading for delegating requests to class member calls. User interfaces are left to other toolkits, though Presto can serve up fragments of HTML with as an API. Presto focuses on APIs as a distinct layer, and we think you should too.

### A quick example 

A RESTful API is built as a class with members named for each resource (or tree of resources). For example, an `apple` is a resource. You can request an `apple` over HTTP:

	GET fruit/apples/spartan.json?tags=large+red


Presto maps this request to a PHP file and class, as well as the specific class member. It loads the file, creates an instance of the class, and executes the member that relates to the request:


	/*	1. Presto automatically loads 'fruit/apples.php'

		2. It then executes a specific member function:
 
				apples->get(array('spartan'), 
						array('tags' => array('large', ''red)),
 						array(), 'json');

		3. What it executes can be configured using Apache rewrite rules
	*/
	
	class apples extends API {
	
		public function get($params, $options, $body, $type) {
			
			/* All error handling uses exceptions */

			if (count($params) === 0)
				throw new Exception('Missing required parameter', 400);
						
			/* The exception becomes a valid HTTP response, including
			a status code and body in the requested content-type */ 

			/* Output is built up as PHP objects (like a DOM) */

			$thing = (object) array(
				'name' => $params[0],
				'tags' => $options['tags']
			);

			/* The output is turned into the requested content-type automatically */
		
			return $thing; // to the client in requested format
		}
	}

The `$thing` is automatically converted by Presto to the requested `Content-Type`, either implied by the request or the appropriate HTTP header. For formats not supported by default, *Output Adapters* can be defined and registered, or the type can be passed through for resources that are not based DOM style data.

Any HTTP request type is mapped automatically. For example, you can request a `LIST` of `apple` types available from the API too:

	LIST apples.json?colour=red

The request is mapped to a function of the same name:

	public function list(/* … */) { return array(); }

More complex resources are possible using container requests, custom rewrite rules, and by adding specific handlers. For example, you could add a `seeds` branch to the `apple` resource. Getting a list of seeds would map to:

	public function list_seeds(/* … */) { array(); }

### What about errors?

Errors are standard PHP exceptions. Additionally, built-in PHP errors are mapped to exceptions (where possible) so that all logic and code errors are returned to clients as valid API returns. This means that individual APIs do not need to check return values, nor do they need to `try` and `catch` unless they need to do something special.

For example, if you encounter a parameter error you can simply throw an exception:

	if (empty($param))
		throw new Exception('Missing required parameter', 400);
		
Presto translates the exception into an HTTP `400` status with an appropriately encoded body.

The API code that results is much more focused on carefully testing parameters, retrieving appropriate resources, and building rich DOMs, rather than boilerplate code, managing responses, excessive error checking, routing, and other complex output generation.


Other interesting features
==========================

You can also:

* Add additional `content-types` by adding *output adapters* (`JSON`, simple `XML`, and simple `HTML` are built in)
* Add `content-type` filters to define what types of payloads a given resource supports.
* Add *custom delegation* for special resource types

**Note that PHP 5.3 or better is required, as Presto relies on anonymous functions and other newer PHP features. This makes it possible to write very clean, simple web services.**
