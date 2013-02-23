#!/bin/bash

BASE_URL=presto.test

OK='\033[38;1;32m[OK]\x1b[0m'
F='\033[38;1;31m[FAIL]\x1b[0m'
SKIP='\033[38;1;31m[SKIP]\x1b[0m'
LINE='=================================================='

section() {
    TEXT="$1"
    printf "\n\033[38;1;34m%s [ %s ] %s\x1b[0m \n\n" ${LINE:${#TEXT}} "$TEXT" ${LINE:${#TEXT}}
}

curlr() {
    m=`echo $1 | tr '[a-z]' '[A-Z]'`
    x="-X ${m}"
    extra=$4
    t=$3
    t=${t:-json}
    
    if [ "${x}" == 'GET' ] ; then x='' ; fi
    
    printf "\033[38;1;34m[%s]\x1b[0m %s " "$m" "$2"

    cmd="curl -X ${m} ${extra} -s ${BASE_URL}/$2"
    echo $cmd
	response=$($cmd)
	
	case $? in
		22 )
			echo -e " #22 ${F}" ;;
		* )
			echo -e " #$? ${OK}"			
	esac

    # TODO - check return type
    # TODO - get status

	if [ ${DEBUG} ] ; then
        if [ ${TRACE} ] ; then echo ${response} ; fi
        
        if [ "$t" == 'json' ] ; then
            echo ${response} | python -mjson.tool
        fi
    fi
}

get() { 
    curlr 'GET' $1 $2 $3 $4
}
options() {
    curlr 'OPTIONS' $1 $2 $3 $4
}
post() {
    curlr 'POST' $1 $2 $3 $4
}
put() {
    curlr 'PUT' $1 $2 $3 $4
}
delete() {
    curlr 'DELETE' $1 $2 $3 $4
}
trace() {
    curlr 'TRACE' $1 $2 $3 $4
}
