COOKIE=/tmp/rubrix.test.cookie
ACCEPT="Accept: application/json"
CONTENT="Content-Type: application/json; charset=utf-8"

SEP="--------------------------------------------------------------------------------"

all:



login:
	@echo ${SEP}; echo "/token.json (valid) ..." ; echo ${SEP}; \
	curl --include --cookie-jar ${COOKIE} -X POST \
			--data @data/test-user.json -H ${ACCEPT} -H ${CONTENT} ${LOGIN}/token.json 2>&1 ; \
	echo

# Get a listing of all rubrics accessible by the test user
get-all:
	@echo ${SEP}; echo "/${API}/listing.json (valid) ..." ; echo ${SEP}; \
	curl --cookie ${COOKIE} -X GET \
		   -H ${ACCEPT} -H ${CONTENT} "${CORE}/${API}/listing.json" | python -mjson.tool 2>&1 ; \
	echo

# Get a listing of all rubrics accessible by the test user filtered by a list of orgs
get-filter-orgs:
	@echo ${SEP}; echo "/${API}/listing.json?inOrgs=1,2,3 (valid) ..." ; echo ${SEP}; \
	curl --cookie ${COOKIE} -X GET \
		   -H ${ACCEPT} -H ${CONTENT} "${CORE}/${API}/listing.json?inOrgs=1,2,3" | python -mjson.tool 2>&1 ; \
	echo
	
