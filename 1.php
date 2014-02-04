<html>
         <head></head>
         <body>
<?php
include 'simple_html_dom.php';
$url="http://vk.com";//http://i-vd.org.ru/books/html-begin/example02.html";
$keyword="word";

print  '
			
			<form action="'.$_SERVER['PHP_SELF'].'">
				<input type="text" name="keyword" value="'.$keyword.'"><br/>
				<input type="text" size="100" name="url" value="'.$url.'"><br/>
				<input type="submit" name="send">
			</form>
			'.count_keyword_at_url($url,$keyword).'
';

function count_keyword_at_url($url,$keyword){
	 $html = new simple_html_dom();
	 $html->load_file($url);
	 $head = $html->find('body');
	echo htmlspecialchars($head[0]->innertext,NULL,'');


	//preg_match_all('#<head>(.*)</head>#',$page,$head);
	//var_dump($head);

	//echo $head->innertext;

//$page=str_replace('<', '>', $page);
	//$head=htmlspecialchars($head,NULL,'');
	

	//$page=htmlspecialchars($page,NULL,'');
	//var_dump($page);
	//return 'e';
//	$html->clear();
// Удаление DOM обьекта
//unset($html);
	//$rss =  simplexml_load_file($url);
//	echo $rss;
}

?>		 </body>	
		</html>