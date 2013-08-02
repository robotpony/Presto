COOKIE=/tmp/rubrix.test.cookie
ACCEPT="Accept: application/json"
CONTENT="Content-Type: application/json; charset=utf-8"

BASE=http://presto.napkinware.test/setup-tests/

SEP="--------------------------------------------------------------------------------"

all:

get-delegation:
	@echo ${SEP}; echo "GET delegation ..." ; echo ${SEP}; \
	curl --include --cookie-jar ${COOKIE} -X GET \
			--data @data/test-user.json -H ${ACCEPT} -H ${CONTENT} ${LOGIN}/info.json 2>&1 ; \
	echo

