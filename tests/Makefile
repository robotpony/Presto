# PrestoPHP tests

DEBUG=1
TRACE=

all: startup dispatch-tests debug-tests done

startup:
	@clear; reset;
	
done:
	@echo;echo;
	
dispatch-tests:
	@./dispatching.sh ${DEBUG} ${TRACE}
	
debug-tests:
	@./debugging.sh ${DEBUG} ${TRACE}
	