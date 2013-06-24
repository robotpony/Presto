#!/bin/bash

DEBUG=$1||0
TRACE=$2||0
DIR="${BASH_SOURCE%/*}"
. "${DIR}/utils.sh"


section "Debugging API Tests"

get "transmogrify/system/php/version.json"
get "transmogrify/system/httpd/headers/content-type.json"
get "transmogrify/system/httpd/headers.json"

get "transmogrify/info/delegation.json"
get "transmogrify/info/delegation/debug/transmogrify/system/php/version.json&method=get&type=json"
get "transmogrify/info/delegation.json" "json" "-H 'x-presto-option: trace'"

options "transmogrify/info/delegation.json"
