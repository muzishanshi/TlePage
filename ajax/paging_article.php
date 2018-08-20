<?php
date_default_timezone_set('Asia/Shanghai');
include '../../../../config.inc.php';
$db = Typecho_Db::get();
$prefix = $db->getPrefix();

$cid = isset($_POST['cid']) ? addslashes($_POST['cid']) : 0;
$query= $db->select()->from('table.contents')->where('cid = ?', $cid); 
$row = $db->fetchRow($query);
$log_content=@$row["text"];
$page_now = isset($_POST['page_now']) ? intval($_POST['page_now']) : 1;
if($page_now<1){
	$page_now=1;
}
$Tle_content_list = explode("----------", $log_content);
$Tle_page_count = count($Tle_content_list);

$page_rec=1;
$totalrec=$Tle_page_count;
$page=ceil($totalrec/$page_rec);

$arr['totalItem'] = $totalrec;
$arr['pageSize'] = $page_rec;
$arr['totalPage'] = $page;

$content=stripslashes($Tle_content_list[$page_now -1]);
if($page_now==1&&strpos($content, '<!--markdown-->')===0){
	$content=substr($content,15);
}
$content=Markdown::convert($content);
$content = str_replace("<img ", "<img width=\"100%\"", $content);
$arr['log_content'] = $content;
echo json_encode($arr);
?>