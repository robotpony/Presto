<aside style="border: 1px solid rgba(0,0,0,.15); padding: 1em .5em; background-color: rgba(0,0,0,.05); text-align: center; font-size: 16pt; letter-spacing: 2px; margin-top: 2em;">
What's changed in version 1.2? See [Github](https://github.com/robotpony/Presto/pulls?direction=desc&page=1&sort=created&state=closed) for details.
</aside>

# Presto is different

PrestoPHP makes building RESTful web services APIs trivial. It's a tiny toolkit that cuts down on what you need to learn and how much code it takes to build a solid web service. It relies on the right tool for each part of a request; the web server for routing and PHP for autoloading, annotating request parameters, and for delegating to class member calls.

Presto obsesses over APIs as a distinct, straightforward layer, and we think you should too.

## A quick example 

An API is built as a class with members named for each resource (or tree of resources). For example, an `apple` is a resource. You can request an `apple` over HTTP:

	GET fruit/apples/spartan.json?tags=large+red

Presto maps this request to a PHP file and class, as well as the specific class member. It loads the file, creates an instance of the class, and executes the member that relates to the request.

    Presto automatically loads 'fruit/apples.php'

The mapping is straightforward enough that navigating your code isn't a chore. Within the class file, it executes member functions based on the nature of the request, passing the parameters split by purpose.

	apples->get(
		array('spartan'), 
		array('tags' => array('large', ''red)),
 		array(), 'json');

How this mapping works can be configured simply in your Apache rewrite rules, though the built in mappings suffice for the majority of APIs we have encountered.

The entire class looks something like:

	
	class apples extends API {
	
		public function get($params, $options, $body, $type) {
			
			/* All error handling uses exceptions */

			if (count($params) === 0)
				throw new Exception('Missing required parameter', 400);
			
			
			/* Output is built up as PHP objects (like a DOM) */

			$dom = (object) array(
				'name' => $params[0],
				'tags' => $options['tags']
			);

			
			/* The output is converted to the requested content-type automatically */
		
			return $dom; // to the client in requested format
		}
	}

There are a few exciting things to notice in the example:

1.  The `$dom` is automatically converted to the requested `Content-Type`, if it's a type that maps easily. This means that `JSON` and `XML` APIs require no *view* code.
2. Status codes are returned automatically from all exceptions. This makes error handling trivial and incredibly clean. Presto maps the various requirements of each `HTTP` status to the exception code and message as required by the specification.
3. Any HTTP request *verb* is mapped to calls automatically. For example, you can request a `LIST` of `apple` types available from the API too. This makes crafting expressive APIs simpler.

Mapping verbs allows requests like:

	LIST apples.json?colour=red

The request is mapped to a function of the same name:

	public function list(/* … */) { return array(); }

More complex resources are possible using container requests a, multipart delegation, and custom rewrite rules. For example, you could add a `seeds` branch to the `apple` resource.

	/* Built in multipart delegation */
	public function list_seeds(/* … */) { return array(); }

Additional segments of a URI automatically map to parameters.

	/* GET apples/seeds/14421.json */
	public function get_seeds(array(14421), /*…*/) { return array(); }

Containers are just folders of APIs, which are mapped by specific `HTACCESS` rules. By default, all APIs are assumed to be at the API route or in containers. More specific rules can be added to improve performance, or just to specifically limit the responses your service gives.


## Errors and statuses

Errors are standard PHP exceptions. Additionally, built-in PHP errors are mapped to exceptions (where possible) so that all logic and code errors are returned to clients as valid API returns. This means that individual APIs do not need to check return values, nor do they need to `try` and `catch` unless they need to do something special.

For example, if you encounter a parameter error you can simply throw an exception:

	if (empty($param))
		throw new Exception('Missing required parameter', 400);
		
Presto translates the exception into an HTTP `400` status with an appropriately encoded body. This is especially interesting for built-in errors, as your code can ignore them entirely and allow PHP and Presto do their magic. Clients of your API will receive well-formed HTTP statuses and encoded bodies for every request.

The API code that results is much more focused on carefully testing parameters, retrieving appropriate resources, and building rich DOMs, rather than boilerplate code, managing responses, excessive error checking, routing, and other complex output generation.

## Version 1.2 and beyond

*These are some notes for 1.2 toolkit feature candidates and other hidden gems in the tools we've developed*

* Simple DB to object mappings
* File listings
* Mockup tools (simple JSON loading)
* Introspection
* Testing tools
* Profiling tools
* Documentation tools
* New website (INFO|EXAMPLE … long page)

The focus of 1.2 is in making standard API code simpler for production. This includes all API tasks, not just code. Development, debugging, deployment, testing, profiling, and documentation. The completion of these and solid install / examples is the goal.

### Database to object mappings

*Part of `lib/helpers/db.php`, a lightweight PDO wrapper that ensures useful exception handling, and adds a few handy helpers.*