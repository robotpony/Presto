# Quick install

1. Clone the Presto library repo to a functioning web root:

<pre>$ git clone git://github.com/robotpony/Presto.git lib/presto</pre>


2. Add default routing using the example `.htaccess` file:

<pre>$ cp lib/presto/lib/htaccess-example .htaccess</pre>
	

3. Link API delegator to your API root:

<pre>$ ln -s lib/presto/lib/delegator-index.php api.php</pre>
	

4. Copy the example API file and retest:

<pre>$ cp lib/presto/examples/info.php .
$ curl [YOUR WEBROOT]/info.json	
{"example":"This is some example information"}</pre>


## Requirements and special cases

* PHP 5.3 or greater is required
* JSON extensions must be enabled
* You must enable `.htaccess` processing in Apache (it's off by default in many installations)

### Installing on a DH PS

You need to enable the JSON extension using a custom RC (not a custom .ini). Find the instructions here:

* http://wiki.dreamhost.com/PHP.ini#Loading_PHP_5.3_extensions_on_all_domains_.28on_VPS_or_dedicated.29