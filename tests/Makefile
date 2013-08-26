# PrestoPHP tests

DEBUG=
TRACE=

all: startup dispatch-tests debug-tests done

startup:
	@clear; reset;
	
done:
	@echo;echo;

unit:
	../vendor/bin/phpunit -v

dispatch-tests:
	@./dispatching.sh ${DEBUG} ${TRACE}
	
debug-tests:
	@./debugging.sh ${DEBUG} ${TRACE}
	
.PHONY: unit