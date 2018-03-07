<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];

//固定框架，单主键表，需修改插入部分
$db = 'user_item';
$dbArray = array(
	'itemId' => array('name'=>'ID',),
	'name' => array('name'=>'名称',),
	'enname' => array('name'=>'英文名称',),
	'count' => array('name'=>'数量','editable'=>1,),
// 	'pos' => array('name'=>'位置',),
);

$reg_pattern = '/^(\d+\|)*\d+$/';


if($type){
    $k = 'item_delete';
    
    if($username){
	    	$account_list = cobar_getValidAccountList('name', $username);
	    	$useruid=$account_list[0]['gameUid'];
		$server = $account_list[0]['server'];
    }
    
    if($type > 1) $ret = $page->webRequest('kickuser',array('uid'=>$useruid),$currentServer);
	//修改
	if($type == 5)
	{
		if($_REQUEST['itemId'] && $_REQUEST['count'] && $useruid) {

			$itemIds = trim($_REQUEST['itemId']);
			$nums = trim($_REQUEST['count']);
			if (preg_match($reg_pattern, $itemIds) && preg_match($reg_pattern, $nums)) {
				$items = explode('|', $itemIds);
				$counts = explode('|', $nums);
				if (count($items) > 0 && count($counts) > 0 && count($items) === count($counts)) {
					foreach ($items as $key => $itemId) {
						if(!$itemId || !$counts[$key]) continue;
						$sql = "select * from $db where ownerId = '$useruid' and itemId = {$itemId}";
						$tmpItems = $page->execute($sql);
						if ($tmpItems['ret']['data'][0]['uuid']) {
							$target = $tmpItems['ret']['data'][0]['count'] - $counts[$key];
							if ($target <= 0) {
								if ($target < 0) $owe_items[$itemId] = -$target;
								$target = 0;
								$sql = "delete from $db where uuid = '{$tmpItems['ret']['data'][0]['uuid']}'";
							} else {
								$sql = "update $db set count =  {$target} where ownerId = '$useruid' and itemId = {$itemId}";
							}
							$actual_delta = $tmpItems['ret']['data'][0]['count'] - $target;

							$page->execute($sql);
							$loguser = !empty($useruid) ? $useruid : $username;
							adminLogUser($adminid, $loguser, $currentServer, array($k => array('itemId' => $itemId, 'count' => $actual_delta)));
						} else {
							$owe_items[$itemId] = $counts[$key];
						}
					}
				} else {
					$error_msg .= "道具ID与数量不对应！！一长一短！";
				}
			} else {
				$error_msg .= "道具id和数量只可输入“|”和数字！！";
			}
		}else{
			$error_msg .= "必须同时输入道具ID和对应的数量！！";

		}
	}

	$sql = "select * from $db where count>0 and ownerId = '{$useruid}'";

	$sql .= " order by itemId asc";
	$result = $page->execute($sql,3, true);
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$enlang = loadLanguage('en');
		$clientXml = loadXml('goods','goods');
		$items = $result['ret']['data'];
		foreach ($items as $key => $item) {//$key 是0 ,1,2,3
			$items[$key]['enname'] = $enlang[(int)$clientXml[$item['itemId']]['name']];
			$items[$key]['name'] = $lang[(int)$clientXml[$item['itemId']]['name']];

			$num =  (int)($clientXml[$item['itemId']]['para']);
			$items[$key]['enname'] = str_replace('{0}',$num,$items[$key]['enname']);
			$items[$key]['name'] = str_replace('{0}',$num,$items[$key]['name']);
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
