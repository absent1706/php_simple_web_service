<?php
//Сервис работает и по GET, и по POST (предпочтительнее второе ввиду ограничения на длину URL)

include("UrlMatcher.php");
header("Content-Type: application/json; charset=utf-8");

//Проверяем, на месте ли параметры.
$required_params=array('url'=>0,'keyword'=>0);
$missing_params=join(',',array_keys(array_diff_key($required_params, $_REQUEST)));
 
//Если все параметры на месте, вызываем основную функцию
if(empty($missing_params))
	$result=UrlMatcher::count_keyword_at_url($_REQUEST['url'], $_REQUEST['keyword'], isset($_REQUEST['case_sensitive']));
//Иначе возвращаем ошибку
else
	$result = array("success" => false, 'message'=>'Missing parameters '.$missing_params);

echo json_encode($result);

?>