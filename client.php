<?php
header('Content-Type: text/html; charset=utf-8');

if (isset($_GET['run'])){

	//Считываем переданные параметры
	$url=$_GET['url'];
	$keyword=$_GET['keyword'];
	$case_sensitive=isset($_GET['case_sensitive']) ? 'checked': '';

	//----------Запрос к веб-сервису. BEGIN-------------

		//Для правильной работы этот файл должен лежать в одной папке с index.php
		$service_url='http://'.dirname($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']).'/';
		$params=array('keyword'=>$keyword, 'url'=>$url);
		if ($case_sensitive) $params['case_sensitive']='true';
		
		$context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header' => array('Accept: application/json',
				'Content-Type: application/x-www-form-urlencoded'),
				'content' => http_build_query($params)
			)
		));
		$result = 'Result is:<br />'.file_get_contents($service_url, false, $context);
    //-----------Запрос к веб-сервису. END--------------
} 
//Иначе устанавливаем их по умолчанию
else {
	$url="http://example.com";
	$keyword='example';
	$case_sensitive='';
	$result='';
}

print
'<html>
 <head><style>input{margin:2;}</style></head>
 <body>
	
	<form action="'.$_SERVER['PHP_SELF'].'" method="get">
		Keyword: <input type="text" name="keyword" value="'.$keyword.'"><br/>
		URL: &nbsp; &nbsp; &nbsp; &nbsp;<input type="text" size="70" name="url" value="'.$url.'"><br/>

		<input type="checkbox" name="case_sensitive" value="1"  '.$case_sensitive.'>Case sensitive<br />
		<input type="submit" name="run" value="Run!">
	</form>
	<br/>
	'.$result.'
 </body>	
</html>';

?>