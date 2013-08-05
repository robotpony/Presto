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
get "setup-tests/info/header-test.json" "201 application/json"

tests "Encoding errors ...
"
get "setup-tests/info/utf8.json" "400 application/json"

tests "Simple post ..."

post "setup-tests/info.json" "200 application/json" "--data @data/simple.json" 

tests "Package delegation ..."

get "introspector/system/httpd.json"
get "introspector/system/httpd/headers.json" "201 application/json"

tests "Expected failures ..."

get "introspector/system/brains/thing.json" "404 application/json"
get "introspector/no-container-test/headers/content-type.json" "404 application/json"

echo