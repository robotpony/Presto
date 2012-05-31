# Quick install

1. Clone the repo to a web root:

	$ git clone git://github.com/robotpony/Presto.git lib/presto
	
2. Add default routing using the example HTACCESS file:

	$ cp lib/presto/lib/htaccess-example .htaccess
	
3. Link API delegator to your API root:

	$ ln -s lib/presto/lib/delegator-index.php api.php
	
4. Copy the example API file and retest:

	$ cp lib/presto/examples/info.php .
	$ curl [YOUR WEBROOT]/info.json	
	{"example":"This is some example information"}




## Requirements and special cases

* PHP 5.3 or greater is required
* JSON extensions must be enabled
* You must enable .htaccess processing in Apache (it's off by default in most installations)

### Installing on a DH PS

You will need to enable the JSON extension using a custom RC (not a custom .ini). Read the instructions here:

* http://wiki.dreamhost.com/PHP.ini#Loading_PHP_5.3_extensions_on_all_domains_.28on_VPS_or_dedicated.29