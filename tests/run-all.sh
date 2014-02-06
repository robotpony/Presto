#!/bin/sh

PHPUNIT=../vendor/bin/phpunit

if [ ! -f ${PHPUNIT} ]; then
	echo "Missing PHPUnit, run composer from the root of this project"
	exit
fi

${PHPUNIT} -v