#!/bin/bash

BASE_URL=presto.test

OK='\033[38;1;32m[OK]\x1b[0m'
F='\033[38;1;31m[FAIL]\x1b[0m'
SKIP='\033[38;1;31m[SKIP]\x1b[0m'

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

