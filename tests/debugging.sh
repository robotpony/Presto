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

get "introspector/info/delegation.json" "200 application/json" "-H 'x-presto-option: trace'"

options "introspector/info/delegation.json"
