<?php

// Presto global config and constants

define('PRESTO', 'presto.php');
define('PRESTO_VERSION', 'presto-v1.10');
define('PRESTO_DEBUG', 0);
define('PRESTO_TRACE', 1);
define('PRESTO_TRACE_KEY', '_presto_trace');
define('VERSION_HEADER', 'X-Api-Version');
define('DEFAULT_RES_TYPE', '.html');
define('PRESTO_BASE', dirname(__FILE__));
define('API_BASE', realpath($_SERVER['DOCUMENT_ROOT']));
if (empty($_SERVER['HOST'])) $_SERVER['HOST'] = 'localhost';

// Helpful shortcut constants

define('HOST', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');
define('PROTOCOL', isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443 ? 'https://': 'http://'); // TODO: handle common load balancers?
define('BASE_URL', 	PROTOCOL.HOST);


// Set up paths for simple auto class loading
set_include_path(get_include_path()
	. PATH_SEPARATOR . PRESTO_BASE
	. PATH_SEPARATOR . API_BASE
	. PATH_SEPARATOR . API_BASE . '/lib/'
	. PATH_SEPARATOR . API_BASE . '/lib/extras/'
	. PATH_SEPARATOR . API_BASE . '/lib/encoders/');
	
if (PRESTO_DEBUG)	set_include_path(get_include_path()
	. PATH_SEPARATOR . '/lib/transmogrify/');

// Set up a base exception for PHP errors (redirects most PHP errors as Exeptions for more consistent handling from APIs)
class PrestoException extends Exception {
	public static function errorHandlerCallback($code, $string, $file, $line, $context) {

		if (error_reporting() != 0)
			return;

		$e = new self($string, $code);

		$e->line = $line;
		$e->file = $file;

		throw $e;
	}
}

// Set up PHP error handling (note these settings are overriden by explicit PHP ini settigns, we should address this)

assert_options(ASSERT_WARNING, 0);
ini_set('html_errors', false);
error_reporting(E_ALL);
set_error_handler(array("PrestoException", "errorHandlerCallback"), E_ALL);

