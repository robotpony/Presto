<?php 
	include_once('inc.php');
	
	function g($k, $d = null) { return array_key_exists($k, $_GET) ? $_GET[$k] : $d; }
	
	$via = g('presto', 'presto');
	$code = g('error');
	$page = g('page', '');

	$name = 'Presto';
	$tagline = 'Simpler REST with PHP';
	$title = $page ? "$name - $page" : "$name - $tagline";
	
	if (empty($page)) $page = 'README.md';
	else $page = str_replace('.html', '.md', $page);
	
	$file = realpath(API_BASE."/$page");
	
?><!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
<meta charset="utf-8">

<script type="text/javascript" src="//use.typekit.net/vie4pvy.js"></script>
<script type="text/javascript">try{Typekit.load();}catch(e){}</script>

<title><?= $title ?></title>
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
	<h1><a href="/"><?= $name ?> <em><?= $tagline ?></em></a></h1>
	
<?php include_once('nav.php'); ?>
	
</div></header>

<section><div>
<article>
	
<?php
	
	if ($page == 'tutorial.md') {
		include_once(API_BASE.'/docs/tutorial.php');
	} elseif (!file_exists($file) || $code) { 
		if (empty($code)) $code = '404';
?>
	<h1><var><?= $code ?></var> Not found</h1>
	<p>The document you were looking for wasn't found.</p>
<?php } else {
	
	include_once('lib/markdown/markdown.php'); 
	$text = Markdown(file_get_contents($file));
	$text = str_replace('<pre>', '<pre class="highlight" style="border: 0; padding: .75em;">', $text);
?>
<?= $text ?>
<?php 
}	
?>
	
</article>
</div></section>

<footer><div>
<h5><strong>Presto</strong> was created by <a href="/LICENSE.html">Bruce Alderson</a>.</h5>
	
</div></footer>


<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
<script src="/docs/js/md.js"></script>
<script type="text/javascript" src="http://balupton.github.com/jquery-syntaxhighlighter/scripts/jquery.syntaxhighlighter.min.js"></script>
<script type="text/javascript">$.SyntaxHighlighter.init({'lineNumbers': false, 'theme': 'google'});</script>

<script>
	var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
	(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
	g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
	s.parentNode.insertBefore(g,s)}(document,'script'));
</script>

</body>
</html>
