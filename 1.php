<?php

// Задачи:
// 1)Дописать проверку на валидность УРЛа
// 2)Обрезать в заголовках ХТТП все ответ серверов, кроме последнего (их несколько, когда идёт перенаправление)

header('Content-Type: text/html; charset=utf-8');
include 'simple_html_dom.php';


if (isset($_POST['run'])){
	$url=$_POST['url'];
	$keyword=$_POST['keyword'];
	$case_sensitive=isset($_POST['case_sensitive']) ? 'checked': '';
} 
else {
	$url="http://example.com";
	$keyword='example';
	$case_sensitive='';
}
print  '<html>
         <head></head>
         <body>
			
			<form action="'.$_SERVER['PHP_SELF'].'" method="post">
				<input type="text" name="keyword" value="'.$keyword.'"><br/>
				<input type="text" size="100" name="url" value="'.$url.'"><br/>
				Case sensitive<input type="checkbox" name="case_sensitive" value="1"  '.$case_sensitive.'><br />
				<input type="submit" name="run">
			</form>
			<br/>
';

if (isset($_POST['run'])){
	print_r(count_keyword_at_url( $url,$keyword,!empty($case_sensitive) ));
}


function count_keyword_at_url($url,$keyword,$case_sensitive=true){

	//--------------Получение страницы. BEGIN-----------------
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER,		 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 		 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,        10);   

		$page_content=curl_exec($ch);
		$curl_error=curl_error($ch);

		$content_type=curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
		curl_close($ch);
	//--------------Получение страницы. END-----------------
	
	//Если	произошла ошибка при получении страницы, возвращаем её
	if($curl_error!='')
		return array('success'=>false, 'message'=>$curl_error);

	$page=str_get_html($page_content);
	
	//-----------------Определение кодировки страницы. BEGIN-------------
		//1. Если есть мета-тег с указанием кодировки, запоминаем её
		$meta=$page->find('meta[http-equiv]',0);
		if ($meta){
			preg_match('#charset=(.+)#i', $meta->content, $res);
		    $page_encoding=$res[1];
		}
		//2. Иначе, если страница сама в заголовках указала кодировку, запоминаем её
		else if (preg_match('#charset=(.+)#i', $content_type,$res))
			$page_encoding=$res[1];
		//3. Иначе пытаемся определить кодировку текста. Надо проверить, не вернёт ли функция определения кодировки false.
		else{
			$page_encoding=mb_detect_encoding($page_content);
			if (!$page_encoding) $warnings[]='Page encoding wasn\'t identified, search results can be wrong';
		}

		//Если полученная кодировка страницы - не utf-8, то меняем её на utf-8.
		if ( !in_array(strtolower($page_encoding), array('utf8','utf-8', 'utf 8')) ){
		  $encoded_text=iconv($page_encoding,'utf-8//TRANSLIT',$page_content);
 		  //В случае успешного конвертирования в utf-8 сохраняем результат
 		  if ($encoded_text) $page_content=$encoded_text;
		}
	//-----------------Определение кодировки страницы. END-------------

	
	
	//Ищем ключевое слово отдельно в тегах 'head' и 'body'
	$matches=array();
	foreach (array('head','body') as $tag) {
		$html=$page->find($tag)[0];
		//Если требуемый тег на найден, возвращаем -1
		$matches[$tag]=($html) ? keyword_matches($keyword,$html->innertext,$case_sensitive) : -1;
	}
		
	//Возвращем результат
	$result = array('success' => true, 'matches' => $matches);
	if (isset($warnings)) $result['message']=implode('; ', $warnings);

	print_r ($result);
	echo '<pre>'.htmlspecialchars($page_content,NULL,'').'</pre>';

	return $result;
}

//Функция ищет количество появлений ключевого слова в тексте
function keyword_matches($keyword,$html,$case_sensitive)
{
	$regexp='#'.preg_quote($keyword).'#';
	if (!$case_sensitive) $regexp.='i';

	preg_match_all($regexp, $html,$res);
	return count($res[0]);
}

?>		 </body>	
		</html>