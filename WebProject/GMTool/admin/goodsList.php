<link href="bootstrap/css/bootstrap.css" rel="stylesheet">
<link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
<?php
header('Content-Type:text/html;charset=utf-8');
define('APP_ROOT', realpath(dirname(__FILE__) . '/../'));
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/'));
define('ITEM_ROOT', APP_ROOT. '/../resource/locale/language');
define('LANG_ROOT', APP_ROOT. '/../resource/locale/language/cn');
include ADMIN_ROOT.'/config.inc.php';

$goodsItems = simplexml_load_file(ITEM_ROOT.'/goods.xml');
$nodes = $goodsItems->xpath('/tns:database//Group[@id=\'goods\']');
$goodsItems = $nodes[0];
$langItems = simplexml_load_file(LANG_ROOT.'/item.xml');
$nodes = $langItems->xpath('/tns:database//Group[@id=\'goods\']');
$langItems = $nodes[0];
$langXml = array();
foreach ($langItems as $langItem)
{
	$langXml[(int)$langItem['id']] = $langItem;
}
$goodsXml = array();
$colorArr = getColor();
$goodsType = array('1'=>'一般杂物'
			,'10'=>'武器装备'
			,'11'=>'宝石'
			,'20'=>'消耗品'
			,'21'=>'礼包'
			,'22'=>'将军道具'
			,'24'=>'将军卡'
			,'25'=>'团战道具'
			,'31'=>'喇叭'
			,'32'=>'强征令'
			,'33'=>'动员令'
			,'37'=>'碎片'
			,'38'=>'重置经典战役');
$goodsArr = array();
foreach ($goodsItems as $goodsItem)
{
	$color = (int)$langXml[(int)$goodsItem['id']]['color'];
	$type = (int)$goodsItem['type'];
	$goodsArr[$type][(int)$goodsItem['id']] = $color;
}
foreach ($goodsArr as $type=>$goods){
	foreach ($goods as $id=>$color)
		$html .= "{$goodsType[$type]}:{$id}:<label style= 'color:{$colorArr[$color-1]}'>{$langXml[$id]['name']}</label><br />";
}
$html = "<table class='listTable' style='text-align:center'>";
// foreach ($goodsArr as $type=>$goods){
// 	foreach ($goods as $id=>$color)
// 	{
// 		$html .= "<tr><td>$goodsType[$type]</td><td>$id</td><td><label style= 'color:{$colorArr[$color-1]}'>{$langXml[$id]['name']}</label></td></tr>";
// 	}
// }
$step = ceil(count($goodsItems)/4);
for($i = 0;$i<$step;$i++){
	$html .= "<tr>";
	for($j = 1;$j < 5;$j++){
		$goods = getRowData($goodsArr,$i,$j,$step);
		if(!$goods)
			continue;
		foreach ($goods as $type=>$value)
			foreach ($value as $id=>$color)
				$html .= "<td>$id</td><td><label style= 'color:{$colorArr[$color-1]}'>{$langXml[$id]['name']}{$langXml[$id]['require_level']}</label></td><td>$goodsType[$type]</td>";
	}
	$html .= "</tr>";
}


$html .= "</table>";
echo $html;
function getRowData($data,$row,$line,$step){
	$i = 1;
	foreach ($data as $type=>$value){
		foreach ($value as $id=>$color)
			if($i++ >= $row + ($line-1)*$step)
				return array($type=>array($id=>$color));
	}
}
?>