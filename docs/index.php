<?php 
	include_once('inc.php');
	
	function g($k, $d = null) { return array_key_exists($k, $_GET) ? $_GET[$k] : $d; }
	
	$via = g('presto', 'presto');
	$code = g('error');
	$page = g('page', '');

	$name = 'pRESTo';
	$tagline = 'PHP + REST';
	$title = $page ? "$name - $page" : "$name - $tagline";
	
	if (empty($page)) $page = 'README.md';
	
	$file = realpath(API_BASE."/$page");
	
?><!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<script type="text/javascript" src="//use.typekit.net/dbf3vfc.js"></script>
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

<header><div>
	<h1><a href="/"><?= $name ?></a></h1>
	
	<nav>
		<a class="disabled" href="#prev"><i class="icon-caret-left"></i></a>
		<a href="#about"><i class="icon-question-sign"></i></a>
		<a href="http://twitter.com/robotpony"><i class="icon-twitter"></i></a>
		<!-- <a href="/feed.xml"><i class="icon-rss"></i></a> -->
		<a class="disabled" href="#next"><i class="icon-caret-right"></i></a>
	</nav>
	
</div></header>

<section><div>
<article>
	
<?php 
	include_once('lib/markdown/markdown.php');
	
	if (!file_exists($file) || $code) { 
		if (empty($code)) $code = '404';
?>
	<h1><var><?= $code ?></var> Not found</h1>
	<p>The document you were looking for wasn't found.</p>
<?php } else {
	
	$text = Markdown(file_get_contents($file));	
?>
<?= $text ?>
<?php 
}	
?>
	
</article>
</div></section>

<footer><div>
<h5><strong>pRESTo</strong> is <a href="#about">loosely Copyrighted &copy; 2009-2012 Bruce Alderson</a></h5>

	<nav>
		<a class="disabled" href="#prev"><i class="icon-caret-left"></i></a>
		<a href="#about"><i class="icon-question-sign"></i></a>
		<a href="http://twitter.com/robotpony"><i class="icon-twitter"></i></a>
		<!-- <a href="/feed.xml"><i class="icon-rss"></i></a> -->	
		<a class="disabled" href="#next"><i class="icon-caret-right"></i></a>
	</nav>
	
</div></footer>


<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/libs/jquery-1.7.2.min.js"><\/script>')</script>

<script src="/docs/js/md.js"></script>
<script>
	var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
	(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
	g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
	s.parentNode.insertBefore(g,s)}(document,'script'));
</script>

</body>
</html>
