#!/bin/bash

OK='\033[38;1;32m[OK]\x1b[0m'
F='\033[38;1;31m[FAIL]\x1b[0m'
SKIP='\033[38;1;31m[SKIP]\x1b[0m'
DEBUG=$1||0
BASE_URL=presto.test

get() {
	echo -n "GET $1"
	response=`curl -s ${BASE_URL}/$1`
	
	case $? in
		22 )
			echo -e " #22 ${F}" ;;
		* )
			echo -e " #$? ${OK}"			
	esac
	
	if [ ${DEBUG} ] ; then echo ${response} | python -mjson.tool; fi
}

get "setup-tests/info.json"
get "setup-tests/info/utf8.json"
get "transmogrify/system/httpd.json"
get "transmogrify/system/httpd/headers.json"
