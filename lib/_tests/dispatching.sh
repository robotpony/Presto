#!/bin/bash

DEBUG=$1||0
DIR="${BASH_SOURCE%/*}"
. "${DIR}/utils.sh"


section "Internal debugging and info API"

get "transmogrify/system/php/utf8.json"
get "transmogrify/system/php/version.json"
get "transmogrify/system/httpd/headers/content-type.json"
get "transmogrify/system/httpd/headers.json"


section "Delegation tests"

get "setup-tests/info.json"
get "setup-tests/info/header-test.json"
get "setup-tests/info/utf8.json"
get "transmogrify/system/httpd.json"
get "transmogrify/system/httpd/headers.json"
get "transmogrify/system/brains/thing.json"
get "transmogrify/no-container-test/headers/content-type.json"

