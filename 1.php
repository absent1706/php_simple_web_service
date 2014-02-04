<?php
header('Content-Type: text/html; charset=utf-8');
include 'simple_html_dom.php';


if (isset($_POST['run'])){
	$url=$_POST['url'];
	$keyword=$_POST['keyword'];
} else{
	$url="http://i-vd.org.ru/books/html-begin/example02.html";
	$keyword="по";
}
print  '<html>
         <head></head>
         <body>
			
			<form action="'.$_SERVER['PHP_SELF'].'" method="post">
				<input type="text" name="keyword" value="'.$keyword.'"><br/>
				<input type="text" size="100" name="url" value="'.$url.'"><br/>
				<input type="submit" name="run">
			</form>
			<br/>
';


print count_keyword_at_url($url,$keyword);

function count_keyword_at_url($url,$keyword){

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$html=curl_exec($ch);
	curl_close($ch);

	echo '<pre>'.htmlspecialchars($html,NULL,'').'</pre>';
	$page=str_get_html($html);
	$head = $page->find('body')[0]->innertext;

	//Определение кодировки страницы
	
	if (mb_detect_encoding($head) != ) ? mb_detect_encoding($head) : '';
	//$head=iconv()

	preg_match_all('#'.preg_quote($keyword).'#', $html,$res);
	//print_r($res);
	return count($res[0]);

}

?>		 </body>	
		</html>