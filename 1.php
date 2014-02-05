<?php

// Задачи:
// 1)Дописать проверку на валидность УРЛа
// 2)Обрезать в заголовках ХТТП все ответ серверов, кроме последнего (их несколько, когда идёт перенаправление)
// 3)Сделать всё это веб-сервисом и написать к нему документацию.

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
				<input type="checkbox" name="case_sensitive" value="1"  '.$case_sensitive.'>Case sensitive (works correctly for english keywords only)<br />
				<input type="submit" name="run">
			</form>
			<br/>
';

if (isset($_POST['run'])){
	print_r(count_keyword_at_url( $url,$keyword,(bool)$case_sensitive ));
}


function count_keyword_at_url($url,$keyword,$case_sensitive=false){
	//Проверка корректности URL
	if (!checkUrl($url)) return array('success'=>false, 'message'=>'URL is invalid');
	else echo 'url is valid <br />'    ;

	//--------------Получение страницы. BEGIN-----------------
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER,		 1);	//http-заголовки в ответ
		curl_setopt($ch, CURLOPT_VERBOSE, 		 1);	//человекочитаемое сообщение об ошибках
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	//следовать перенаправлениям
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//отключить проверку сертификатов для https
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	//получать результат в переменную
		curl_setopt($ch, CURLOPT_TIMEOUT,        20);   //ждать загрузки 20 секунд

		$page_content=curl_exec($ch);
		$curl_error=curl_error($ch);

		$content_type=curl_getinfo($ch,CURLINFO_CONTENT_TYPE);//информация о типе и кодировке содержимого
		curl_close($ch);
	//--------------Получение страницы. END-----------------
	
	//Если	произошла ошибка при получении страницы, возвращаем её
	if($curl_error!='')
		return array('success'=>false, 'message'=>$curl_error);

	//Загружаем содержимое страницы в объект simple_html_dom
	$page=str_get_html($page_content);
	
	//-----------------Определение кодировки страницы. BEGIN-------------
		//1. Проверяем, есть ли теги meta http-equiv и charset, где указана кодировка
		$meta_eq=$page->find('meta[http-equiv]',0);
		$meta_ch=$page->find('meta[charset]',0);
		if ($meta_eq && preg_match('#charset=(.+)#i', $meta_eq->content, $res))
			{echo '1'; $page_encoding=$res[1];}
		else if ($meta_ch)
			{echo '2';$page_encoding=$meta_ch->charset;}
		//2. Иначе смотрим, указала ли страница кодировку в заголовках
		else if (preg_match('#charset=(.+)#i', $content_type,$res))
			{echo '3';$page_encoding=$res[1];}
		//3. Иначе пытаемся определить кодировку текста сами.
		else{echo '4';
			$page_encoding=mb_detect_encoding($page_content);
			if (!$page_encoding) $warnings[]='Page encoding wasn\'t identified, search results can be wrong';
		}

		
		//Если полученная кодировка страницы - не utf-8, то меняем её на utf-8.
		if ( $page_encoding && !in_array(strtolower($page_encoding), array('utf8','utf-8', 'utf 8')) ){
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
		
	//Возвращаем результат
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




function checkurl(&$url) {
   if (empty($url)) return false;//проверка на пустоту

   $url=strtolower(trim($url));// режем крайние пробелы
   if (!strstr($url,"://")) $url="http://".$url; //добавляем http:// при его отсутствии
   
   //Проверяем наличие http и минимум 2 доменов. Конец адреса не проверяется
   return (preg_match("#^(https?:\/\/)?([a-z0-9-]+\.)+([a-z0-9]{2,6})#",$url));
}

?>		 </body>	
		</html>