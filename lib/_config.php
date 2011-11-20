<?php 


$builtIns = array('API', 'request', 'session');

define('PRESTO_DEBUG', 0);
define('VERSION_HEADER', 'X-Api-Version');
define('API_VERSION', 0);
define('DEFAULT_RES_TYPE', '.html');
define('API_BASE', $_SERVER['DOCUMENT_ROOT']);

set_include_path(get_include_path() . PATH_SEPARATOR . PRESTO_BASE . PATH_SEPARATOR . API_BASE);	

?>