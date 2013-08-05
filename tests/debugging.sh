#!/bin/bash

DEBUG=$1||0
TRACE=$2||0

DIR="${BASH_SOURCE%/*}"
. "${DIR}/bin/utils.sh"


section "Debugging API Tests"

get "introspector/system/php/version.json"
get "introspector/system/httpd/headers/content-type.json"  "201 application/json"
get "introspector/system/httpd/headers.json"  "201 application/json"

get "introspector/info/delegation.json"

trace "introspector/info/delegation.json" "405 text/html; charset=iso-8859-1"

options "introspector/info/delegation.json"

echo
