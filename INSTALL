# Quick install

1. Clone the repo to a local folder (like ~/).

	$ git clone git://github.com/robotpony/Presto.git
	
2. Create a new web root for your service, add to vhosts, and symlink in the library:

	$ ln -s ~/Presto/lib
	
3. Run the configuration script.

	$ ./lib/configure.sh
	
You should now have a web root with a linked .htaccess, index.html (for docs), and index.php (for delegating requests).

4. Now add your API as a set of classes in the web root, where the files are named the same as the class name:

	$ touch text.php status.php
	
5. Add skeleton classes based on the examples provided.


## Notes

### Installing on a DH PS

You will need to enable the JSON extension using a custom RC (not a custom .ini). Read the instructions here:

* http://wiki.dreamhost.com/PHP.ini#Loading_PHP_5.3_extensions_on_all_domains_.28on_VPS_or_dedicated.29