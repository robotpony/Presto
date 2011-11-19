#!/bin/sh


if [ -f index.php ]; then
	echo "Refusing to overwrite existing files."
	exit 1 
fi

# link in the delegator
ln -s lib/delegator-index.php index.php
ln -s lib/htaccess .htaccess

# copy the service documentation template
if [ ! -f index.html ]
then
	cp lib/index.html .
fi
