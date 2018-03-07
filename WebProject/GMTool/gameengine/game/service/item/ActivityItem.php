<?php
/**
 * ActivityItem
 * 
 * 活动列表
 * 
 * @Entity
 * @package item
 */
class ActivityItem {

	public function getItems($uid){
		
		$time = time();
		$data = array();
		import('service.item.ItemSpecManager');
		$xmlActivities = ItemSpecManager::singleton('default', 'activity.xml')->getGroup('activity');
		import('service.item.TeamItem');
		$res = TeamItem::getActivityPlayerJoin($uid);
		$gmFlag = UserProfile::getWithUID($uid)->gmFlag;
		foreach ($xmlActivities as $xmlActivity){
			$starTimes = array();
			$endTimes = array();
			$registration_start = array();
			$registration_end = array();
			if($gmFlag == 1) {
				$starTimes[] = strtotime("00:00:00");
				$endTimes[] = strtotime("24:00:00");
				$registration_start[] = strtotime("00:00:00");
				$registration_end[] = strtotime("24:00:00");
			} else {
				if($time < strtotime($xmlActivity->start_date) || $time > strtotime($xmlActivity->end_date) + 3600*24){
					continue;
				}
				$startEndTime = $this->resolveMultiTime($xmlActivity, 'startEndTime');
				foreach($startEndTime[0] as $startTime) {
					$starTimes[] = $startTime;
				}
				foreach($startEndTime[1] as $endTime) {
					$endTimes[] = $endTime;
				}
			}
			$planStr = '';
			$exitStr = '';
			$useGoodsStr = '';
			$mapRange = '';
			if($xmlActivity->type == 1 || $xmlActivity->type == 4) {
				$plans = explode(',', $xmlActivity->enemy_plan);
				for($i=0,$count=count($plans);$i<$count;$i++) {
					$versusXML = ItemSpecManager::singleton('default', 'versus.xml')->getItem($plans[$i]);
					if($i != $count - 1) {
						$planStr = $planStr . $versusXML->player_lv . ',';
						$exitStr = $exitStr . $versusXML->exit . ',';
						$useGoodsStr = $useGoodsStr . $versusXML->usegoods . ';';
						$mapRange = $mapRange . $versusXML->map_range . ',';
					} else {
						$planStr = $planStr . $versusXML->player_lv;
						$exitStr = $exitStr . $versusXML->exit;
						$useGoodsStr = $useGoodsStr . $versusXML->usegoods;
						$mapRange = $mapRange . $versusXML->map_range;
					}
				}
			}
			
			$idMapRange = array();
			$idExitMap = array();
			if($xmlActivity->type == 3) {
				$plans = explode(',', $xmlActivity->enemy_plan);
				for($i=0,$count=count($plans);$i<$count;$i++) {
					$versusXML = ItemSpecManager::singleton('default', 'versus.xml')->getItem($plans[$i]);
					while($versusXML->next_id) {
						$idMapRange[$versusXML->id] = $versusXML->map_range;
						$idExitMap[$versusXML->id] = $versusXML->exit;
						$versusXML = ItemSpecManager::singleton('default', 'versus.xml')->getItem($versusXML->next_id);
					}
					$idMapRange[$versusXML->id] = $versusXML->map_range;
					$idExitMap[$versusXML->id] = $versusXML->exit;
				}
			}
			$order = null;
			$resources_scope = null;
			$notMoveRange = null;
			$Prepare_scope = null;
			$Prepare_scope2 = null;
			if($xmlActivity->type == 4 || $xmlActivity->type == 7 || $xmlActivity->type == 8) {
				$registration = $this->resolveMultiTime($xmlActivity, 'registration');
				$registration_start = $registration[0];
				$registration_end = $registration[1];
				$xmlVersus = ItemSpecManager::singleton('default', 'versus.xml')->getItem($xmlActivity->enemy_plan);
				$order = $xmlVersus->order;
				$resources_scope = $xmlVersus->resources_scope;
				$notMoveRange = $xmlVersus->not_move_range;
				$Prepare_scope = $xmlVersus->Prepare_scope;
				$Prepare_scope2 = $xmlVersus->Prepare_scope2;
			}
			$data[] = array(
				'itemId' => $xmlActivity->id,
				'startTime' => $starTimes,
				'endTime' => $endTimes,
				'registration_start' => $registration_start,
				'registration_end' => $registration_end,
				'active_week' => $xmlActivity->active_week,
				'registration_week' => $xmlActivity->registration_week,
				'Player_lv' => $xmlActivity->Player_lv,
				'enemy_plan' => $xmlActivity->enemy_plan,
				'enemy_plan_level' => $planStr,
				'min_player' => is_null($xmlActivity->min_player) ? 0 : $xmlActivity->min_player,
				'max_player' => $xmlActivity->max_player,
				'do_times' =>  $xmlActivity->do_times,
				'Exdo_times' =>  $xmlActivity->Exdo_times,
				'item_cost' => $xmlActivity->item_cost,
				'type' => $xmlActivity->type,
				'exit' => $exitStr,
				'current' => $res && $res[0]['activityId'] == $xmlActivity->id ? 1 : 0,
				'idMapRange' => $idMapRange,
				'idExitMap' => $idExitMap,
				'mapRange' => $mapRange,
				'soldier_return' => $xmlActivity->soldier_return,
				'join_cost' => $xmlActivity->join_cost,
				'union_lvl' => $xmlActivity->union_lvl,
				'order' => $order,
				'resources_scope' => $resources_scope,
				'not_move_range' => $notMoveRange,
				'useGoods' => $useGoodsStr,
				'Prepare_scope' => $Prepare_scope,
				'Prepare_scope2' => $Prepare_scope2,
			);
		}
		return $data;
	}
	
	private function resolveMultiTime($xmlActivity, $type) {
		switch($type) {
			case 'startEndTime':
				$Starts  = explode(',',$xmlActivity->start_time);
				$Ends = explode(',',$xmlActivity->end_time);
				break;
			case 'registration':
				$Starts  = explode(',',$xmlActivity->registration_start);
				$Ends = explode(',',$xmlActivity->registration_end);
				break;
		}
		$count=count($Starts);
		if($count > 1) {
			for($i=0;$i<$count;$i++) {
				$starTimes[] = strtotime($Starts[$i]);
				$endTimes[] = strtotime($Ends[$i]);
			}
		} else {
			$starTimes[] = strtotime($Starts[0]);
			$endTimes[] = strtotime($Ends[0]);
		}
		return array($starTimes, $endTimes);
	}
}
?>