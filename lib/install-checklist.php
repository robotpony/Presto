<!doctype html>
<html lang=en>
<head>
<meta charset=utf-8>
<title>Presto install checklist</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<style>
body {
	font-family: 'Helvetica Neue', Helvetica, sans-serif;
}
body>header, body>section, body>footer {
	width: 800px; margin: 0	auto;
}
body>header>h1 {
	font-size: 18pt;
}
body>header>h1>em {
	display: block;
	font-style: normal; font-weight: 200;
	font-size: 10pt;
}
details {
	width: 50%;
	border: 1px solid #999;
	padding: 1em; margin:  1em;
	box-shadow: 0px 0px 15px rgba(0,0,0,.1);
	border-radius: 15px;
}

details>summary {
	margin-bottom: 1em;
	font-size:  larger;
	font-weight: bold;
}
::-webkit-details-marker {
 	list-style-type: none;
 }
details>summary>var {
	float: right;
	font-style: normal;
	font-weight: bold;
	background-color: rgba(0,0,0,.75); color: #fff;
	padding: 4px 6px;
	border-radius: 3px;
}
details>p {
	font-size: 13pt; font-weight: 200;
}
pre {
	word-wrap: break-word; 
}
</style>
</head>
<body>

<header>
	<h1>Presto <em>install checklist</em></h1>
</header>

<?php 
	include_once('../lib/inc.php');
	
	// NOTE: this would make a great default "api"	
?>
<section>

<details>
	<summary>Presto debug mode <var><?= PRESTO_DEBUG ? 'ON' : 'OFF' ?></var></summary>
	<p>Debug mode adds trace and informational logging via syslog and extra detail in exceptions.</p>
</details>

<details>
<?php 
	$ver = explode('.', phpversion());
	$ok = ($ver[0] >= '5' && $ver[1] >= 3);
?>
	<summary>PHP version ok? <var><?= $ok ? 'PASS' : 'FAIL' ?></var></summary>
	<p>Found PHP version <code><?= phpversion() ?></code></p>
</details>

<details>
<?php 
	$ok = function_exists('curl_init');
?>
	<summary>cURL/PHP installed? <var><?= $ok ? 'PASS' : 'FAIL' ?></var></summary>
	<p>cURL details: <pre><?= print_r(curl_version()) ?></pre></p>
</details>

<details>
<?php 
	$ok = function_exists('json_encode');
?>
	<summary>JSON/PHP installed? <var><?= $ok ? 'PASS' : 'FAIL' ?></var></summary>
	<p>Determines if JSON support is enabled.</p>
</details>


<details>
<?php 
	$ok = array_key_exists('presto', $_GET);
?>
	<summary>HTACCESS enabled? <var><?= $ok ? 'PASS' : 'FAIL' ?></var></summary>
	<p>Validates that both .htaccess rules and non-API delegation are functional.</p>
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
		
			detail.text(xhr.responseText);
			
			if (xhr.status === 404) {
				display.text('PASS');
				console.log('Ignoring 404, marks delegation success');
			} else { 
				display.text('FAIL');
			}
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
			var ct = xhr.getResponseHeader("content-type") || "";
			
			if (ct.indexOf('html') > -1) {
				display.text('FAIL');
				detail.text('Invalid content type (HTML, expecting JSON).');
			}
			else
				display.text('PASS');	
				
			detail.text(xhr.responseText);				

		}, error: function(xhr, textStatus, errorThrown) {
		
			detail.text(xhr.responseText);
			
			if (xhr.status === 404) {
				display.text('PASS');
				console.log('Ignoring 404, marks delegation success');
			} else { 
				display.text('FAIL');
			}
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
			var ct = xhr.getResponseHeader("content-type") || "";
			
			if (ct.indexOf('html') > -1) {
				display.text('FAIL');
				detail.text('Invalid content type (HTML, expecting JSON).');
			}
			else
				display.text('PASS');	
				
			detail.text(xhr.responseText+'\n\nHTTP return: '+xhr.status+'\n\n'+ xhr.getAllResponseHeaders().toLowerCase() );
			
			console.log(xhr.status());		

		}, error: function(xhr, textStatus, errorThrown) {
		
			detail.text(xhr.responseText);
			
			if (xhr.status === 404) {
				display.text('PASS');
				console.log('Ignoring 404, marks delegation success');
			} else { 
				display.text('FAIL');
			}
		}
	});	
});
</script>
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
<!--

Inspecting for classes?

-->
</section>
</body>
</html>

