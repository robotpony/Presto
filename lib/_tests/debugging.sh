#!/bin/bash

DEBUG=$1||0
DIR="${BASH_SOURCE%/*}"
. "${DIR}/utils.sh"


section "Internal debugging and info API"

get "transmogrify/system/php/utf8.json"
get "transmogrify/system/php/version.json"
get "transmogrify/system/httpd/headers/content-type.json"
get "transmogrify/system/httpd/headers.json"

get "transmogrify/info/delegation.json"
