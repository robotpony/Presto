<?php 
include(dirname(__FILE__).'/../lib/auth_token.php');

set_error_handler('handleError');

try {

    if (count($argv) < 2) throw new Exception('Missing expected parameters.', 500);
    
    $token = trim(file_get_contents("php://stdin"));
    $creds = trim($argv[1]);
    
    $TLD = ''; // disable
    require($creds);
    
    $t = new auth_token($token);
    
    print_r($t);
    
} catch (Exception $e) { 
    $c = $e->getCode();
    
    print $c . ': ' . $e->getMessage() . "\n\n";
    
    if ($c == 500) { ?>
     
Usage:

    cat $token-file | php <?= $argv[0] ?> $path-to-token-variables
        
<?php    }
}

function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
{
    if (0 === error_reporting())
        return false;

    throw new ErrorException($errstr, 500 + 0, $errno, $errfile, $errline);

}
