<?php

//include 'UrlMatcher.php';

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
         <head><style>input{margin:2;}</style></head>
         <body>
			
			<form action="'.$_SERVER['PHP_SELF'].'" method="get">
				Keyword: <input type="text" name="keyword" value="'.$keyword.'"><br/>
				URL: &nbsp; &nbsp; &nbsp; &nbsp;<input type="text" size="70" name="url" value="'.$url.'"><br/>

				<input type="checkbox" name="case_sensitive" value="1"  '.$case_sensitive.'>Case sensitive (works correctly for english keywords only)<br />
				<input type="submit" name="run" value="Run!">
			</form>
			<br/>
';

if (isset($_GET['run'])){
	$service_url='http://'.dirname($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']).'/';	

	$params=array('keyword'=>$keyword, 'url'=>$url);
	if ($case_sensitive) $params['case_sensitive']=true;

	//Делаем запрос к веб-сервису
	$context = stream_context_create(array(
		'http' => array(
			'method' => 'POST',
			'header' => array('Accept: application/json',
			'Content-Type: application/x-www-form-urlencoded'),
			'content' => http_build_query($params)
		)
	));
	$result = file_get_contents($service_url, false, $context);

	echo $result;//Выводим результат
}


print '	 </body>	
		</html>';

?>