#!/bin/bash

DEBUG=$1||0
TRACE=$2||0

DIR="${BASH_SOURCE%/*}"
. "${DIR}/bin/utils.sh"

section "Dispatch tests"

tests "Simple delegation ..."

get "setup-tests/info.json"
get "setup-tests/info/params/param.json"
get "setup-tests/info/params/param/param-2/another-param/4/5/6.json"
get "setup-tests/info/header-test.json" "201"
get "setup-tests/info/utf8.json"

post "setup-tests/info.json" "200" "--data @data/simple.json" 

tests "Package delegation ..."

get "transmogrify/system/httpd.json"
get "transmogrify/system/httpd/headers.json"

tests "Expected failures ..."

get "transmogrify/system/brains/thing.json"
get "transmogrify/no-container-test/headers/content-type.json"

echo