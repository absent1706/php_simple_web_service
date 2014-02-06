<?php
//Если ничего не передано, выводим справку

if (empty($_REQUEST)){
	header("Content-Type: text/html; charset=windows-1251");
	include('readme.htm');
	exit();
}

header("Content-Type: application/json; charset=utf-8");
include("UrlMatcher.php");

//Проверяем, на месте ли параметры.
$required_params=array('url'=>0,'keyword'=>0);
$missing_params=join(',',array_keys(array_diff_key($required_params, $_REQUEST)));
 
//Если все параметры на месте, вызываем основную функцию
if(empty($missing_params))
{
	$case_sens=isset($_REQUEST['case_sensitive']) && ($_REQUEST['case_sensitive'] == 'true');
	$result=UrlMatcher::count_keyword_at_url($_REQUEST['url'], $_REQUEST['keyword'], $case_sens);
}
//Иначе возвращаем ошибку
else
	$result = array("success" => false, 'message'=>'Missing parameters: '.$missing_params);

echo json_encode($result);

?>