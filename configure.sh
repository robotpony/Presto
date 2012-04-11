#!/bin/sh

DELEGATOR=./api.php
ROUTER=.htaccess

if [ -f ${DELEGATOR} ]; then
	echo "Cowardly refusing to overwrite existing files, aborting."
	exit 1 
fi
ln -s lib/delegator-index.php ${DELEGATOR}

if [ -f ${ROUTER} ]; then
	echo "Cowardly refusing to overwrite ${ROUTER}, aborting."
	exit 1 
fi
ln -s lib/${ROUTER} ${ROUTER}
