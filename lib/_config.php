<?php // Presto global config and constants

define('PRESTO_DEBUG', 0);
define('VERSION_HEADER', 'X-Api-Version');
define('DEFAULT_RES_TYPE', '.html');
define('PRESTO_BASE', dirname(__FILE__));
define('API_BASE', realpath($_SERVER['DOCUMENT_ROOT']));

$builtIns = array('API', 'request', 'session');

set_include_path(get_include_path()
  . PATH_SEPARATOR . PRESTO_BASE
  . PATH_SEPARATOR . API_BASE
  . PATH_SEPARATOR . API_BASE . '/lib/'
  . PATH_SEPARATOR . API_BASE . '/lib/extras/');	

presto_check_install();

/* check the installation */
function presto_check_install() {
	
	if (!PRESTO_DEBUG) return;
	
	$ver = explode('.', phpversion());
	if ($ver[0] != '5' && $ver[1] < 3)
		throw new Exception('Unsupported version of PHP. (' . phpversion() . '). ');
	
	if (!function_exists('curl_init'))
		throw new Exception('cURL required by Presto.lib.');

	if (!function_exists('json_encode'))
		throw new Exception('JSON extension required by Presto.lib.');
		 
}


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

assert_options(ASSERT_WARNING, 0);
ini_set('html_errors',false);
error_reporting(E_ALL);
set_error_handler(array("PrestoException", "errorHandlerCallback"), E_ALL);

?>