<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
<meta charset="utf-8">

<script type="text/javascript" src="//use.typekit.net/vie4pvy.js"></script>
<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>

<title>Presto - installation checklist</title>
<meta name="description" content="A simple PHP REST toolkit">
<meta name="author" content="Bruce Alderson">

<meta name="viewport" content="width=device-width">
<link rel="stylesheet" href="/docs/styles/presto.css">

<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<script>window.html5 || document.write('<script src="js/libs/html5.js"><\/script>')</script>
<![endif]-->
</head>
<body>
<!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->

<a href="https://github.com/robotpony/Presto"><img style="position: absolute; position: fixed; top: 0; right: 0; border: 0;" src="https://s3.amazonaws.com/github/ribbons/forkme_right_red_aa0000.png" alt="Fork me on GitHub"></a>

<header><div>
	<h1><a href="/">Presto</a> <em>install checklist</em></h1>
	
<?php include_once('../nav.php'); ?>
	
</div></header>

<?php 
	include_once('inc.php');
	
	// NOTE: this would make a great default "api"	
?>
<section><div>

<details>
<?php 
	$ver = explode('.', phpversion());
	$ok = ($ver[0] >= '5' && $ver[1] >= 3);
?>
	<summary>PHP version ok? <var><?= $ok ? 'PASS' : 'FAIL' ?></var></summary>
	<p>Found PHP version <code><?= phpversion() ?></code></p>
	<p>PHP execution environment? <code><?= array_key_exists('REQUEST_URI', $_SERVER) ? 'FastCGI' : 'CGI' ?></code></p>
</details>

<details>
<?php 
	$ok = function_exists('curl_init');
?>
	<summary>cURL PHP installed? <var><?= $ok ? 'PASS' : 'FAIL' ?></var></summary>
<?php if ($ok) { ?><p>cURL details: <pre><?= print_r(curl_version()) ?></pre></p>
<?php } else { ?><p>cURL is missing from your PHP installation.</p><?php } ?>
</details>

<details>
<?php 
	$ok = function_exists('json_encode');
?>
	<summary>JSON PHP installed? <var><?= $ok ? 'PASS' : 'FAIL' ?></var></summary>
	<p>Determines if JSON support is enabled.</p>
</details>


<details>
<?php 
	$ok = array_key_exists('presto', $_GET);
?>
	<summary>HTACCESS enabled? <var><?= $ok ? 'PASS' : 'FAIL' ?></var></summary>
	<p>Validates that both .htaccess rules and non-API delegation are functional.</p>
<?php if (!$ok) { ?><pre>Missing Presto tag in GET request for this page.

$_GET <?php print_r($_GET); ?>
</pre><?php } ?>
</details>

<details class="delegate">
	<summary>Presto delegation ok? <var></var></summary>
	<p>Validates basic Presto delegation. This test looks for a valid Presto 404. <pre></pre></p>

<script>	
$(document).ready(function() {
	var display = $('details.delegate summary>var'), detail = $('details.delegate pre');
	
	$.ajax({
		url: 'status.json',
		success: function(response, status, xhr) {
			var ct = xhr.getResponseHeader("content-type") || "";
			
			if (ct.indexOf('html') > -1) {
				display.text('FAIL');
				detail.text('Invalid content type (HTML, expecting JSON).');
			}
			else
				display.text('PASS');					

		}, error: function(xhr, textStatus, errorThrown) {
			var ct = xhr.getResponseHeader("content-type") || "",
				isJSON = ct.indexOf('json') > -1;
			
			detail.text(!isJSON ? 'Response was in an unexpected format, delegation failed.' : 'Unexpected response.');			
			display.text('FAIL');
		}
	});	
});
</script>
</details>

<details class="delegate-actual">
	<summary>Presto delegate execution? <var></var></summary>
	<p>Validates sample Presto delegation. <pre></pre></p>

<script>	
$(document).ready(function() {
	var display = $('details.delegate-actual summary>var'), detail = $('details.delegate-actual pre');
	
	$.ajax({
		url: 'info.json',
		success: function(response, status, xhr) {
			var ct = xhr.getResponseHeader("content-type") || "",
				isJSON = ct.indexOf('json') > -1;
			
			if (!isJSON) {
				display.text('FAIL');
				detail.text('Invalid content type (HTML, expecting JSON).');
			}
			else {
				display.text('PASS');	
				detail.text(xhr.responseText);
			}

		}, error: function(xhr, textStatus, errorThrown) {
			var ct = xhr.getResponseHeader("content-type") || "",
				isJSON = ct.indexOf('json') > -1;
						
			detail.text(!isJSON ? 'Response was in an unexpected format, delegation failed.' : 'Unexpected response.');			
			display.text('FAIL');
		}
	});	
});
</script>
</details>

<details class="delegate-extras">
	<summary>Presto header magic? <var></var></summary>
	<p>Validates Presto header functions. <pre></pre></p>

<script>	
$(document).ready(function() {
	var display = $('details.delegate-extras summary>var'), detail = $('details.delegate-extras pre');
	
	$.ajax({
		url: 'info/header_test.json',
		success: function(response, status, xhr) {
			var ct = xhr.getResponseHeader("content-type") || "",
				isJSON = ct.indexOf('json') > -1;
			
			if (!isJSON) {
				display.text('FAIL');
				detail.text('Invalid content type (HTML, expecting JSON).');
			}
			else {
				display.text('PASS');		
				detail.text(xhr.responseText+'\n\nHTTP return: '+xhr.status+'\n\n'+ xhr.getAllResponseHeaders().toLowerCase() );
			}

		}, error: function(xhr, textStatus, errorThrown) {
			var ct = xhr.getResponseHeader("content-type") || "",
				isJSON = ct.indexOf('json') > -1;
						
			detail.text(!isJSON ? 'Response was in an unexpected format, delegation failed.' : 'Unexpected response.');			
			display.text('FAIL');
		}
	});	
});
</script>
</details>

<details>
	<summary>Presto debug mode <var><?= PRESTO_DEBUG ? 'ON' : 'OFF' ?></var></summary>
	<p>Debug mode adds trace and informational logging via syslog and extra detail in exceptions.</p>
</details>

<details>
<summary>Request details</summary>
<pre>
<?php print_r($_SERVER); ?>
<?php print_r($_GET); ?>
<?php print_r($_GET); ?>
<?php print_r($_COOKIE); ?>
</pre>
</details>

<details>
<summary>PHP environment</summary>
<?php phpinfo(); ?>
</details>

<!--

Inspecting for classes?

-->
</div>
</section>
</body>
</html>

