<?php
include 'simple_html_dom.php';
include 'UrlMatcher.php';

// Сделать всё это веб-сервисом (через SOAP) и написать к нему документацию.

header('Content-Type: text/html; charset=utf-8');


if (isset($_GET['run'])){
	$url=$_GET['url'];
	$keyword=$_GET['keyword'];
	$case_sensitive=isset($_GET['case_sensitive']) ? 'checked': '';
} 
else {
	$url="http://example.com";
	$keyword='example';
	$case_sensitive='';
}
print  '<html>
         <head></head>
         <body>
			
			<form action="'.$_SERVER['PHP_SELF'].'" method="get">
				<input type="text" name="keyword" value="'.$keyword.'"><br/>
				<input type="text" size="100" name="url" value="'.$url.'"><br/>
				<input type="checkbox" name="case_sensitive" value="1"  '.$case_sensitive.'>Case sensitive (works correctly for english keywords only)<br />
				<input type="submit" name="run">
			</form>
			<br/>
';

if (isset($_GET['run'])){
	//$matcher=new UrlMatcher;
	$result=UrlMatcher::count_keyword_at_url( $url,$keyword,(bool)$case_sensitive);
	echo json_encode($result);
}



?>		 </body>	
		</html>