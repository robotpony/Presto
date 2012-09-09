Frequently asked things
-----------------------

## Q: Why no MVC?

MVC is a poor match for stateless web APIs and web applications, as it implies a large slice of functionality for what are generally very narrow requests. Presto assumes that API requests are narrow, and minimises the amount of framework loaded (and dependencies implied by) each request. This simplifies your code and your ability to get the most performance out of your web services.

## Q: How do I create a view?

You're probably looking for one of two things: user interface views or API views.

For user interfaces, we recommend using static (or mostly static) HTML and PHP files, and animating them with Javascript and RESTful APIs. This approach removes the need for a complex view template library, and various helpers/framework tools it implies.

For API views, Presto uses output adapters, which transform your PHP objects into the target `content-type`. By default, Presto includes JSON, simple HTML, and simple XML adaptors. For more complex data transformations, you can write your own adapters. TODO: link here.

## Q: How do I create a model?

## Q: How does routing work?

<!-- 
Questions:

* Views
	* Direct object mapping
	* Templated view (mostly unused)
		* Partial templated (hopefully not)
* Application state
	* session
	* other data
* Configuration
	* DB connectivity
* Parameter checking
	* POST
	* GET
		* built in
		* like post?
* API calls


* Where to look for the various pieces
	* above pieces (param filtering)
	
-->