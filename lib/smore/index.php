<? // smore css processing

// note: this will be split out soon (moved up to libs)

// base data
define('BASE', getenv('DOCUMENT_ROOT').'/');
define('IN', dirname(__FILE__).'/');
define('LIB', BASE.'/lib');
define('DEFAULT_TYPE', 'css');
define('DEFAULT_OBJECT', 'main');
$_rq =  @$_SERVER['REQUEST_URI'];

// helpers 
function _coalesce() { foreach (func_get_args() as $a) if (!empty($a)) break;  return $a; }
function _dump() { print '<pre class="debug">'; foreach (func_get_args() as $a) print_r($a); print '</pre>'; }

// split URI
preg_match('/^(?:\/([\w\d\/]+?)|)(?:\/([^\/\+\?\.]+)|)(?:\.([^\?\+]+)|)(?:\+([^\?]+)|)(?:\?(.*)|)$/', $_rq, $_g);

$_o = array(
	NULL, NULL, array(NULL, 1), 
	array(1, 2, NULL, NULL),
	array(1, 2, 3, NULL),
	array(1,2,3,4,5),
	array(1,2,3,4,5)
);
$l = count($_g);

$_s = array_reverse(preg_split('/\./', @$_SERVER['SERVER_NAME']));

// collect request bits
$_rq = array(
	'server'	=> @$_SERVER['SERVER_NAME'],
	'subsite'	=> @$_s[2],
	'site'		=> @$_s[1],
	'domain'	=> @$_s[0],
	'from'		=> @$_SERVER['HTTP_REFERER'],
	'return'	=> @preg_split('/^(?:.*?)\/(.*)$/', @$_SERVER['HTTP_REFERER']),
	'path' 		=> @$_rq,
	't'			=> @$_SERVER['REQUEST_TIME'],
	'action' 	=> @$_SERVER['REQUEST_METHOD'],
	'type' 		=> _coalesce(@$_o[1], DEFAULT_TYPE),
	'uri'		=> array(
		'flags' 	=> preg_split('/\&/', @$_g[@$_o[$l][4]]),
		'tags'		=> preg_split('/\+/', @$_g[@$_o[$l][3]]),
		'type'		=> _coalesce(@$_g[@$_o[$l][2]], DEFAULT_TYPE),
		'obj'		=> _coalesce(@$_g[@$_o[$l][1]], @$_g[@$_o[$l][0]], DEFAULT_OBJECT),
		'path'		=> preg_split('/\//', !empty($_g[$_o[$l][1]]) ? @$_g[@$_o[$l][0]] : '')
	)
);

_dump($_rq);
include IN."base.php";
$css = file_get_contents(BASE . "{$_rq['path']}");
header('Content-type: text/css');
@eval("print \"$css\";");
?>
