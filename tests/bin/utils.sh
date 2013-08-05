#!/bin/bash

BASE_URL=presto.napkinware.test

OK='\033[38;1;32m[PASS]\x1b[0m'
F='\033[38;1;31m[FAIL]\x1b[0m'
SKIP='\033[38;1;31m[SKIP]\x1b[0m'
LINE='=================================================='

section() {
    TEXT="$1"
    printf "\n\033[38;1;34m%s [ %s ] %s\x1b[0m \n\n" ${LINE:${#TEXT}} "$TEXT" ${LINE:${#TEXT}}
}

tests() {
	TEXT="$1"
	printf "\n\033[1;33m%s \x1b[0m \n\n" "$TEXT"
}

# make a curl request (helper)
#
# METHOD URL [STATUS] [EXTRA SETTINGS] [TYPE]
curlr() {
    m=`echo "$1" | tr '[a-z]' '[A-Z]'`; x="-X ${m}"
    url="${BASE_URL}/$2"
    expected="$3"; expected=${expected:-200 application/json}
    extra=$4
    t="$5"; t=${t:-json}

    # get the status code (this results in running each test twice)
    code=$(curl -sL -o /dev/null -w "%{http_code} %{content_type}" -X ${m} ${extra} -s ${url})

    # display codes and test method
    if [ "${code}" ]; then printf "\n\033[38;1;34m[%s]\x1b[0m %s" "$m" "${url}"; fi
    
    # run the test
    cmd="curl -s -X ${m} ${extra} -s ${url}"
	response=$($cmd)

    printf "\n\033[38;1;34m[%s]\x1b[0m [expected: %s] [curl: %s] " "$code" "$expected" "$?"

    # Handle exit codes
	case $? in
		22 )
			echo -e "${F}" ; echo ${response} ;;
		6 )
			echo -e "${F} HOST NOT FOUND" ; echo ${response} ;;
		* )
			if [ "${code}" = "${expected}" ] ; then
                echo -e "${OK}"
            else
    			echo -e "${F}"
            fi ;;
	esac

	if [ ${DEBUG} ] ; then
        if [ ${TRACE} ] ; then echo ${response} ; fi
        
        if [ "$t" == 'json' ] ; then
            echo ${response} | python -mjson.tool
        fi
    fi
}

# A get request
get() { curlr 'GET' "$1" "$2" "$3" "$4" "$5"; }
# An options request
options() { curlr 'OPTIONS' "$1" "$2" "$3" "$4" "$5"; }
# A post
post() { curlr 'POST' "$1" "$2" "$3" "$4" "$5"; }
# A put
put() { curlr 'PUT' "$1" "$2" "$3" "$4" "$5"; }
# A delete
delete() { curlr 'DELETE' "$1" "$2" "$3" "$4" "$5"; }
# A trace
trace() { curlr 'TRACE' "$1" "$2" "$3" "$4" "$5"; }
