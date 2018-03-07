<?php
!defined('IN_ADMIN') && exit('Access Denied');
if (isset($_REQUEST['action'])) {
	$action_type = trim($_REQUEST['action']);
	try {
		$username = getGPC('username',1);
		$useruid = getGPC('useruid',1);
		//新增开启关卡
		if($action_type == 'modify'){
			$newList = substr($_REQUEST['newList'],0,-1);
			$sql = "select uid from userprofile where name = '{$username}'";
			$tmp = $page->execute($sql,3);
			$uid = $tmp['ret']['data'][0]['uid'];
			//先查询没有开启或者开启了还没通关的NPC
			$sql = "select npcId from pve where uid = '$uid' and npcId in ($newList) and firstFight = 1";
			$newListArr = explode(',', $newList);
			$result = $page->execute($sql,3);
			foreach ($result['ret']['data'] as $value) {
				$oldPower[] = $value['npcId'];
			}
			$newPower = array_diff($newListArr,$oldPower);
			//再replace into数据
			$curTime = microtime(true)*1000;
			foreach ($newPower as $npcId) {
				$newData[] = implode("','", array($uid,$npcId,0,0,0,$curTime,1));
			}
			$sql = "replace into pve values ('".implode("'),('", $newData)."')";
			$result = $page->execute($sql);
		}
		if($username)
			$sql = "select npcId from pve where uid = (select uid from userprofile where name = '{$username}')";
		else
			$sql = "select npcId from pve where uid = '{$useruid}'";
		$result = $page->execute($sql,3);
		foreach ($result['ret']['data'] as $value) {
			$power[$value['npcId']] = 1;
		}
		if($power)
		{
			/** 读取XML **/
			$powerItems = simplexml_load_file(ITEM_ROOT.'/database.local.xml');
			$nodes = $powerItems->xpath('/tns:database//Group[@id=\'power\']');
			$powerItems = $nodes[0];
			foreach ($powerItems as $powerItem)
			{
				$powerLangItems[(int)$powerItem['id']] = $powerItem;
			}

			$checkpointItems = simplexml_load_file(ITEM_ROOT.'/database.local.xml');
			$nodes = $checkpointItems->xpath('/tns:database//Group[@id=\'checkpoint\']');
			$checkpointItems = $nodes[0];
			foreach ($checkpointItems as $checkpointItem)
			{
				$checkpointLangItems[(int)$checkpointItem['id']] = $checkpointItem;
			}

			$npcItems = simplexml_load_file(ITEM_ROOT.'/database.local.xml');
			$nodes = $npcItems->xpath('/tns:database//Group[@id=\'npc\']');
			$npcItems = $nodes[0];
			foreach ($npcItems as $npcItem)
			{
				$npcLangItems[(int)$npcItem['id']] = $npcItem;
			}

			//每关的文字
			$lang = loadLanguage();

			$powerList = array();
			foreach ($checkpointItems as $checkpointItem)
			{
				$powerId = (int)$checkpointItem['power_id'];
				$checkpoint = (int)$checkpointItem['id'];
				$npcIds = explode('|', (string)$checkpointItem['npc_id']);
				foreach ($npcIds as $npcId) {
					if($power[$npcId])
						$powerList[$powerId][$checkpoint][$npcId] = 1;
					else
						$powerList[$powerId][$checkpoint][$npcId] = 0;
				}
			}
			$html = "";
			$allflag = true;
			foreach ($powerList as $powerId=>$powerInfo)
			{
				$checktemp = "";
				$flag = true;
				foreach ($powerInfo as $checkpoint=>$checkInfo)
				{
					$temp = "";
					$checkflag = true;
					foreach ($checkInfo as $npcId=>$status)
					{
						$id = $powerId.'_'.$checkpoint.'_'.$npcId;
						if(!$status)
						{
							$flag = false;
							$allflag = false;
							$checkflag = false;
						}
						$temp .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' id={$id} name={$id} onClick=checkall('{$id}') ".($status?"checked":"")." />　　部队：".$npcId."　　名称：".$lang[(int)$npcLangItems[$npcId]['fb_title']]."<br />";
					}
					$checkId = $powerId.'_'.$checkpoint;
					$checktemp = $checktemp . "&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' id={$checkId} name={$checkId} onClick=chooseall('{$checkId}') ".($checkflag?"checked":"")." />　　势力：".$checkpoint."　　名称：".$lang[(int)$checkpointLangItems[$checkpoint]['name']]."<br />".$temp;
				}
				$html = $html . "<br /><input type='checkbox' id={$powerId} onClick=chooseall('{$powerId}') ".($flag?"checked":"")." />　　地图：".$powerId."　　名称：".$lang[(int)$powerLangItems[$powerId]['name']]."<br />".$checktemp;
			}
			$html = "<form id='list' name='list'><input type='checkbox' id='-1' onClick=openall() ".($allflag?"checked":"")." />全部开启<br />".$html;
			echo $html;
			exit();
		}
		echo "no data";
		exit;
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
		echo $error_msg;
		exit();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>