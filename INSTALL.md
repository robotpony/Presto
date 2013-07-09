# Test your setup

1. Point Apache at the `examples/` folder of presto.
2. Point your browser at the VHOST configured in #1.
3. All tests should pass.

# Using Presto in your own projects

1. Clone the Presto library repo to a functioning web root:

<pre>$ git clone git://github.com/robotpony/Presto.git lib/presto</pre>

The web root will need to be accessible by Apache, be able to execute PHP, and honour `.htaccess` rules.

2. Add default routing using the example `.htaccess` file:

<pre>$ cp lib/presto/lib/htaccess-example .htaccess</pre>
	

3. Link API delegator to your API root:

<pre>$ ln -s lib/presto/lib/delegator-index.php api.php</pre>

This file sets up Presto's delegator features, so that your classes are called in a predictable way via `HTTP` requests.

4. Copy the example API file and test:

<pre>$ cp lib/presto/setup-tests/info.php .
$ curl [YOUR WEBROOT]/info.json	
{"example":"This is some example information"}</pre>

The example API file writes a simple DOM (a `PHP` keyed array) as `JSON` back to the client (which is `cURL` in this case).

## Requirements

* PHP 5.3 or greater is required
* The JSON PHP extensions must be enabled
* `.htaccess` must be enabled in Apache (it's off by default in many installations)

### Installing on a DH PS

You need to enable the JSON extension using a custom RC (not a custom .ini). Find the instructions here:

* http://wiki.dreamhost.com/PHP.ini#Loading_PHP_5.3_extensions_on_all_domains_.28on_VPS_or_dedicated.29
