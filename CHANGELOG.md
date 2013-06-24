# Changelog

# 1.1 - *Sim Sala Bim*

**Presto 1.1 breaks compatibility with 1.0 in a few important areas:**

* Adds subfolder delegation
* Removes add_delegate*, URL, and other unused code (subfolder delegation is much simpler)
* Improves debugging (PHP errors to error log, added trace mode)
* Added unit tests
* Updated setup tests (to use internal API)
* `API` base startup has been simplified, removing the class pre-scanning mechanism. Presto will throw an exception if 1.0 usage is detected.
* API parameters are flattened to their obvious parts (get parameters, options, put/post body, resource type). This deprecates the `$ctx` object, which is still available via `self::$call`.
* `add_delegate` has been removed, as it has been replaced by folder delegation
* `add_filter` has been removed (a candidate 1.1 feature), as the other features provided enough utility on their own
* `Request::URI` has been removed in favour of a simpler builder function. URI parsing is now spread over `htaccess` for more obvious routing rules.

## Alpha 1 features

This patch adds a number of features planned for 1.1, including:

* Dispatching to folders of APIs
* Preflighting dispatch calls for parameter and model processing
* Adds internal testing and debugging APIs (only available when enabled)
* Adds command line unit testing tools
* Adds a trace mode, and better exception logging
* Adds routing information to exceptions (if trace is enabled)

## Updated parameter passing

The calling convention for delegate API functions has changed from:

	public function get($ctx) { }
	public function get_article($ctx) { }
	public function put_article($ctx) { }

To the more verbose form:
	
	public function get($params, $options, $body, $type) { }
	public function get_article($params, $options, $body, $type) { }
	public function put_article($params, $options, $body, $type) { }

This makes the call parameters clearer than they were in the past.	

## Example folder delegation

Folder delegation uses Apache rewriting to map folders of APIs to specific URLs. For example, Presto uses it for internal delegation to its `transmogrify` APIs:

    RewriteRule ^(transmogrify/(?:info|system|tests))/(.*)\.(.*)$ /lib/delegator-index.php?r=$2&t=$3&c=$1 [L]

This routes all requests to 3 specific folders within `transmogrify` to files within the folders `info/`, `system/`, and `tests/` in `transmogrify/`.

Requests like:

    get "transmogrify/system/httpd/headers/content-type.json"

Are mapped to:

    transmogrify/system/httpd.php::httpd->get_headers();

## Example preflight delegation

API calls can be split into two parts to simplify handling complex model creation and parameter processing. The split adds a preflight call to a *model* function of the same root name as the API itself.

Consider a simple GET:

    get "transmogrify/system/httpd/headers/content-type.json"

This call can optionally be split into the model creation and the API call itself.

The model creation preflight call would be:

    http.php::http->get_headers_model($p = array('content-type')) {
        return new Features($p);
    }

The call receives the standard API request parameters, returning a newly created model object of some sort. This call can be used to check parameters, check preconditions, and massage parameters into useful/predictable values for the API call itself:

    http.php::http->get_headers($featureModel) {
    }


## Example routing trace

If `PRESTO_TRACE` is enabled, exceptions will include:


	[OPTIONS] transmogrify/info/delegation.json  #0 [OK]
	{
	    "_presto_trace": {
	        "request": "/transmogrify/info/delegation.json", 
	        "routing_scheme": {
	            "action": "options", 
	            "class": "delegation", 
	            "container": "transmogrify/info", 
	            "file": "transmogrify/info/delegation.php", 
	            "method": "options", 
	            "options": [], 
	            "params": [], 
	            "resource": "", 
	            "type": "json"
	        }, 
	        "version": "presto-v1.10"
	    }, 
	    "code": 404, 
	    "message": "Can't find delegation->options"
	}


The `_presto_trace` element is added to make debugging easier. This trace is also available in the error log (in trace mode only):

    [Fri Feb 22 16:38:58 2013] [error] [client 127.0.0.1] PRESTO: _presto_trace, {"routing_scheme":{"container":"transmogrify\\/info","class":"delegation","file":"transmogrify\\/info\\/delegation.php","resource":"","type":"json","action":"get","method":"get","params":[],"options":[]},"request":"\\/transmogrify\\/info\\/delegation.json","version":"presto-v1.10"}


# 1.0 - A la peanut butter sandwiches 

* First public release
* Adds sub delegates and param filtering

# 0.8

* Adds basic URI -> class/member delegation and auto data return by type
