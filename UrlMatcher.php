<?php
include 'simple_html_dom.php';
class UrlMatcher {
	
	public function count_keyword_at_url($url,$keyword,$case_sensitive=false){
		//Проверка корректности URL
		if (UrlMatcher::validate_url($url) == false) return array('success'=>false, 'message'=>'URL is invalid');

		//--------------Получение страницы. BEGIN-----------------
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER,		 1);	//http-заголовки в ответ
			curl_setopt($ch, CURLOPT_VERBOSE, 		 1);	//человекочитаемое сообщение об ошибках
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//отключить проверку сертификатов для https
		    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	//получать результат в переменную
			curl_setopt($ch, CURLOPT_TIMEOUT,        20);   //ждать загрузки 20 секунд

			$page_content=curl_exec_follow($ch);//Оригинальная curl_exec на сервере не работает из-за safe mode.
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
				{$page_encoding=$res[1];}
			else if ($meta_ch)
				{$page_encoding=$meta_ch->charset;}
			//2. Иначе смотрим, указала ли страница кодировку в заголовках
			else if (preg_match('#charset=(.+)#i', $content_type,$res))
				{$page_encoding=$res[1];}
			//3. Иначе пытаемся определить кодировку текста сами.
			else{
				$page_encoding=mb_detect_encoding($page_content);
				if (!$page_encoding) $encode_warning='Page encoding wasn\'t identified, search results can be wrong';
			}

			
			//Если полученная кодировка страницы - не utf-8, то меняем её на utf-8.
			if ( $page_encoding && !in_array(strtolower($page_encoding), array('utf8','utf-8', 'utf 8')) ){
			  $encoded_text=iconv($page_encoding,'utf-8//TRANSLIT',$page_content);
	 		  //В случае успешного конвертирования в utf-8 сохраняем результат
	 		  if ($encoded_text) $page_content=$encoded_text;
			}
		//-----------------Определение кодировки страницы. END-------------

		//Раскомментировав следующую строку, можно увидеть содержимое страницы
		//echo '<pre>'.htmlspecialchars($page_content,NULL,'').'</pre>';			

		//Ищем ключевое слово отдельно в тегах 'head' и 'body'
		$matches=array();
		foreach (array('head','body') as $tag) {
			$html=$page->find($tag,0);
			//Если требуемый тег на найден, возвращаем -1
			$matches[$tag]=($html) ? UrlMatcher::keyword_matches($keyword,$html->innertext,$case_sensitive) : -1;
		}
			
		//Возвращаем результат
		$result = array('success' => true, 'matches' => $matches);
		if (isset($encode_warning)) $result['message']=$encode_warning;

		return $result;
	}

	//Функция ищет количество появлений ключевого слова в тексте
	private function keyword_matches($keyword,$html,$case_sensitive)
	{
		$regexp='#'.preg_quote($keyword,'#').'#';
		if (!$case_sensitive) $regexp.='i';

		preg_match_all($regexp, $html,$res);
		return count($res[0]);
	}

	//Функция проверки корректности URL
	private function validate_url(&$url) {
	   if (empty($url)) return false;//проверка на пустоту

	   $url=strtolower(trim($url));// режем крайние пробелы
	   if (!strstr($url,"://")) $url="http://".$url; //добавляем http:// при его отсутствии
	   
	   //Проверяем наличие http и минимум 2 доменов. Конец адреса не проверяется
	   return (preg_match("#^(https?:\/\/)?([a-z0-9-]+\.)+([a-z0-9]{2,6})#",$url));
	}
}


function curl_exec_follow($ch, &$maxredirect = null) {
  
  // we emulate a browser here since some websites detect
  // us as a bot and don't let us do our job
  $user_agent = "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0";
              
  curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );

  $mr = $maxredirect === null ? 5 : intval($maxredirect);

  if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off') {

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
    curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  } else {
    
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    if ($mr > 0)
    {
      $original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
      $newurl = $original_url;
      
      $rch = curl_copy_handle($ch);
      
      curl_setopt($rch, CURLOPT_HEADER, true);
      curl_setopt($rch, CURLOPT_NOBODY, true);
      curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
      do
      {
        curl_setopt($rch, CURLOPT_URL, $newurl);
        $header = curl_exec($rch);
        if (curl_errno($rch)) {
          $code = 0;
        } else {
          $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
          if ($code == 301 || $code == 302) {
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $newurl = trim(array_pop($matches));
            
            // if no scheme is present then the new url is a
            // relative path and thus needs some extra care
            if(!preg_match("/^https?:/i", $newurl)){
              $newurl = $original_url . $newurl;
            }   
          } else {
            $code = 0;
          }
        }
      } while ($code && --$mr);
      
      curl_close($rch);
      
      if (!$mr)
      {
        if ($maxredirect === null)
        trigger_error('Too many redirects.', E_USER_WARNING);
        else
        $maxredirect = 0;
        
        return false;
      }
      curl_setopt($ch, CURLOPT_URL, $newurl);
    }
  }
  return curl_exec($ch);
}
?>