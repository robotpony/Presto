COOKIE=/tmp/rubrix.test.cookie
ACCEPT="Accept: application/json"
CONTENT="Content-Type: application/json; charset=utf-8"

BASE=http://presto.napkinware.test/setup-tests

SEP="--------------------------------------------------------------------------------"

all: get-tests
	
get-tests: get-delegation-root get-delegation-object get-delegation-object-param get-delegation-object-params

get-delegation-root:
	@echo ${SEP}; echo "GET delegation ..." ; echo ${SEP}; \
	curl -X GET -H ${ACCEPT} -H ${CONTENT} ${BASE}/info.json 2>&1 ; \
	echo

get-delegation-object:
	@echo ${SEP}; echo "GET delegation ..." ; echo ${SEP}; \
	curl -X GET -H ${ACCEPT} -H ${CONTENT} ${BASE}/info/test.json 2>&1 ; \
	echo

get-delegation-object-param:
	@echo ${SEP}; echo "GET delegation ..." ; echo ${SEP}; \
	curl -X GET -H ${ACCEPT} -H ${CONTENT} ${BASE}/info/params/param.json 2>&1 ; \
	echo
	
get-delegation-object-params:
	@echo ${SEP}; echo "GET delegation ..." ; echo ${SEP}; \
	curl -X GET -H ${ACCEPT} -H ${CONTENT} ${BASE}/info/params/param/param-2/another-param/4/5/6.json 2>&1 ; \
	echo	
