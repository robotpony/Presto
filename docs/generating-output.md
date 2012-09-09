Generating output
-----------------

All Presto API functions return a simple DOM object. This object (or array) is translated into output using a type handler, which is selected by the requested `content-type`.

For example:

	GET docs/README.md.json
	Accept:application/json, text/javascript, */*; q=0.01

The request implies a simple `get` member, which receives parameters as `$ctx`, loading its data by a private member (not shown here). Data is returned in APIs by simply returning the DOM object:

	// get a document
	function get($ctx) { $DOM = self::load($ctx->params); return $DOM; }
	
By the time the member function is called, Presto has marked the expected content-type as `application/json`, and will automatically attempt to convert the data returned by any API to JSON. By default, Presto supports `JSON` and simple `HTML`, but other types can be implemented and registered.

	
Registration can be performed globally for an API class in the constructor, or in each individual API member function:

	self::add_type_handler('application/xml', $encoder_fn, [$mapper_fn]);
	self::add_type_exception('application/json'); // mark JSON as not supported

Internally, Presto considers the encoder, mapper, and type as a `type_handler`, which maps directly to `content-types`. The result of encoding and mapping is simple string output, used as the payload for the HTTP request.


The basic flow is simple:

	           DOM --+
	                 |
	                 V
	              encoder                 \
	                 +      "text/html"   |--- type handler  
	               mapper                 /
	                 |
	                 V
	            text payload


After an API function returns its data, the data (or `DOM`) is filtered through the `type handler` that best matches the request.


## Custom type handlers

An `encoder` is a simple function that takes object or array input, and produces text output. It's passed the `DOM` and optional `mapper` function. The `mapper` receives a `NODE` and `DOM` path, and returns either a `NODE` or `text payload`.

The `encoder` is often a function that calls itself recursively, so that it can 

### Example: transforming a DOM into plain text

	function encode_simple_text($node, $path = null, $map = null) {		
		if (is_string($node)) return print $node . "\n";
		
		if (is_array($node)) {
			foreach ($node as $k => &$v) encode_simple_text($v, $k);
		}
	}

This simple encoder does not use a `mapper`, and it ignores any node names in the `DOM`.

We can expand the example to map headings to Markdown headings by adding a mapping function and extending the `DOM` traversal:


	function markdown_mapper($path, $node, $depth) {
		if ($path === 'title' && is_string($node)) 
			return str_repeat('#', $depth) . " $node";
	
		return $node;
	}
	
	function encode_simple_text($node, $map = null) {
		static $d = 0;
		static $p = '';
		
		if (is_string($node)) return print $node . "\n";
		
		if (is_array($node)) {
			$d++;
			foreach ($node as $k => &$v) {
				$p = $k;
				$node = $map($v, $d, $p);				
				encode_simple_text($v);
			}
			$d--;
		}
	}

Notice that the `DOM` mapper prints its output, done to minimize allocated memory. It also doesn't manage the `path` variable as a full path, though this can be done for more complex mapping problems.


### Additional examples:

1. An [example mapping function for simple HTML transformations](https://gist.github.com/2589593), including attribute remapping.
