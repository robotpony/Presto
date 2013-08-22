# REST API, interactive, and testing tools

## Command line usage

Imagine a web service for generating impsum text. As a developer you want to be able to test pieces of your API by hand as you developing, before you are able to run your unit tests to completion, and to diagnose pieces of your API.

So for an ipsum service, you would be able to poke it form the command line like this:

	$ ipsum get p/long.md

The `ipsum` script itself is a `bash` wrapper in your API that calls a Presto *Service* object. This object may be just the base *service* class itself or your own custom derivation of it.

Using your `ipsum` script, you would be able to make various types of REST requests:

	$ ipsum delete p/short.json
	$ ipsum get html/long.md
	$ ipsum post p/short.json

Of course, you don't want to POST or DELETE from an `ipsum` generator, so it would return an error status in those cases:

	403 - You can't do that, duh.

The command line version of the tool return the status as the exit code and as friendly text.

## API libraries

Your command line tool uses the Presto *Service* abstraction, either direcly (for simple cases) or by deriving from it. The base library wraps calling RESTful services using PHP magic methods, so that any call on the class is delegated via HTTP.

For your `ipsum` library would result in an interna call like:

	$service->get('p/long.md');

(Or some variant of that, depending on the usage you prefer).

If you derive from the *service* base, you can provide much more interesting (semantically useful) calls:

	$paragraphs = $ipsum->p(5, 'random'); // get some random paragraphs	

A custom API library based on the Presto *Service* base can be used as a public API for your RESTful service, simplifying using it from PHP.

## PHP unit usage

Your API service library can also be used from unit testing tools like [PHPUnit]()