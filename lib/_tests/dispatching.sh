#!/bin/bash

DEBUG=$1||0
DIR="${BASH_SOURCE%/*}"
. "${DIR}/utils.sh"

get "setup-tests/info.json"
get "setup-tests/info/header-test.json"
get "setup-tests/info/utf8.json"
get "transmogrify/system/httpd.json"
get "transmogrify/system/httpd/headers.json"
get "transmogrify/system/httpd/headers/content-type.json"
