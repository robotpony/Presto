<?php // Presto global config and constants



define('PRESTO_DEBUG', 0);
define('VERSION_HEADER', 'X-Api-Version');
define('API_VERSION', 0);
define('DEFAULT_RES_TYPE', '.html');

define('PRESTO_BASE', dirname(__FILE__));
define('API_BASE', realpath($_SERVER['DOCUMENT_ROOT']));

$builtIns = array('API', 'request', 'session');

set_include_path(get_include_path()
  . PATH_SEPARATOR . PRESTO_BASE
  . PATH_SEPARATOR . API_BASE
  . PATH_SEPARATOR . API_BASE . '/lib/');	

presto_check_install();


/* check the installation */
function presto_check_install() {
	
	if (!PRESTO_DEBUG) return; 
	
	$ver = explode('.', phpversion());
	if ($ver[0] != '5' && $ver[1] < 3) { 
		print 'Unsupported version of PHP. (' . phpversion() . '). '; die; 
	};
	
	if (!function_exists('curl_init'))
		throw new Exception('cURL required by Presto.lib.');

	if (!function_exists('json_encode'))
		throw new Exception('JSON extension required by.');
		 
}
?>