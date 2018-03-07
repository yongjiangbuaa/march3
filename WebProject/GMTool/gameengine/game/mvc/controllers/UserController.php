<?php

import('game.mvc.controllers.RomeBaseController');
class UserController extends RomeBaseController {
	function doIndex(){
	}
	
	function doPrevent(){
	}
	
	function doList(){
		
	}
	
	function doTrade(){
		
	}
	
	function doApiRequest(){
		
	}
	
	function doRank(){
		$this->powers = array(912129 => 'Pélias', 914119 => 'Perseu', 916119 => 'Teseu', 918129 => 'Adrasto');
	}
	
	function doReport(){
	}
	
	function doExportExcel(){
		$params = $this->request()->gets();
		$offset = isset($params['offset']) ? intval($params['offset']) : 0;
		$limit_once = 1000;//一次能导出的最大记录数
		$limit = 100; //每次查询最大数
		//导入PHPExcel类
		import('mvc.controllers.classes.PHPExcel');
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Set properties
		$objPHPExcel->getProperties()
			 ->setCreator("Maarten Balliauw")
			 ->setLastModifiedBy("Maarten Balliauw")
			 ->setTitle("Office 2007 XLSX Test Document")
			 ->setSubject("Office 2007 XLSX Test Document")
			 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
			 ->setKeywords("office 2007 openxml php")
			 ->setCategory("Test result file");
		switch ($params['type']){
			case 1: //tradeLog
				//取出TradeLog
				$start_time = strtotime($params['from_date']);
				$end_time = strtotime($params['to_date']);
				$e_type = $params['e_type'];
				import('domain.item.TradeLog');
				import('domain.user.UserProfile');
				$offset = $offset > 0 ? $offset : 0;
				$query = new Query('TradeLog');
				if($e_type == 2){
					$query->order('date');
				}
				if($e_type == 1){
					$query->filter('date>=', $start_time);
				}
				$tradeLogs = $query->exec();
				
				$res = array();
				$ret = array();
				//区分用户查询name
				foreach ($tradeLogs as $key => $tradeLog){
					if($e_type == 1 && $tradeLog->date > $end_time){
						continue;
					}
					
					if(!isset($res[$tradeLog->user_uid])){
						$query = new Query('UserProfile');
						$query->uid($tradeLog->user_uid);
						$user = $query->exec();
						$res[$tradeLog->user_uid]['name'] = $user->name;
						$res[$tradeLog->user_uid]['recharge_times'] = $user->recharge_times;
					}
					$ret[] = array(
						'trade_id' => $tradeLog->trade_id,
						'platform_uid' => $tradeLog->platform_uid,
						'name' => $res[$tradeLog->user_uid]['name'],
						'money' => $tradeLog->money,
						'state' => $tradeLog->status,
						'level' => $tradeLog->level,
						'recharge_times' => $res[$tradeLog->user_uid]['recharge_times'],
						'time' => date ( 'Y-m-d H:i:s', $tradeLog->date ),
						'gold' => $tradeLog->gold,
					);
					unset($tradeLogs[$key]);
				}
				//set title
				$Excel = $objPHPExcel->setActiveSheetIndex(0);
				$Excel->setCellValue('A1', 'trade_id');
				$Excel->setCellValue('B1', 'platform_uid');
				$Excel->setCellValue('C1', 'name');
				$Excel->setCellValue('D1', 'money');
				$Excel->setCellValue('E1', 'gold');		
				$Excel->setCellValue('F1', 'level');
				$Excel->setCellValue('G1', 'recharge_times');
				$Excel->setCellValue('H1', 'time');
				$Excel->setCellValue('I1', 'state');
				//set data
				foreach ($ret as $key => $value){
					$key = $key + 2;
					$Excel->setCellValue('A'.$key, $value['trade_id']);
					$Excel->setCellValue('B'.$key, $value['platform_uid']);
					$Excel->setCellValue('C'.$key, $value['name']);
					$Excel->setCellValue('D'.$key, $value['money']);
					$Excel->setCellValue('E'.$key, $value['gold']);
					$Excel->setCellValue('F'.$key, $value['level']);
					$Excel->setCellValue('G'.$key, $value['recharge_times']);
					$Excel->setCellValue('H'.$key, $value['time']);
					$Excel->setCellValue('I'.$key, $value['state']);
				}
				//filename
				$file_name = 'trade_logs';
				break;
			case 2: // userList
				import("service.item.CityItem");
				$user = PersistenceSession::singleton()->get('CityItem', '1270014f4c6ea60f001');
				import('domain.user.UserProfile');
				$loop = ceil($limit_once/$limit);
				$user_list = array();
				//循环取出5000条记录
				for($i=0; $i < $loop; $i++){
					$query = new Query('UserProfile');	
					$query->order('name');
					$query->offset($offset);
					$query->limit($limit);
					$users = $query->exec();
					if(!$users){
						break;
					}
					$user_list = array_merge($user_list, $users);
					$offset += $limit;
				}
				//set title
				$Excel = $objPHPExcel->setActiveSheetIndex(0);
				$Excel->setCellValue('A1', '用户337平台ID');
				$Excel->setCellValue('B1', '用户等级');
				$Excel->setCellValue('C1', '金币');
				$Excel->setCellValue('D1', '银币');
				$Excel->setCellValue('E1', '军功');
				$Excel->setCellValue('F1', '威望');
				$Excel->setCellValue('G1', '粮食');
				$Excel->setCellValue('H1', '兵力');
				$Excel->setCellValue('I1', '武将数');
				$Excel->setCellValue('J1', 'VIP');
				$Excel->setCellValue('K1', '最后登录日期');
				$Excel->setCellValue('L1', '注册日期');
				$index = 2;
				$match_str = '_rome0@elex337';
				if(is_array($user_list)){
					foreach ($user_list as $key => $user){
						//过滤掉未初始化的用户
						if($user->name == '' || $user->name == NULL){
							continue;
						}
						$arr_index = strpos($user->platformAddress, $match_str);
						$platform_uid = $arr_index == 0 ? $user->platformAddress : substr($user->platformAddress, 0, $arr_index);
						$date = $user->five_refresh_time == null ? '' : date('Y-m-d', $user->five_refresh_time);
						$Excel->setCellValue('A'.$index, $platform_uid);
						$Excel->setCellValue('B'.$index, $user->level);
						$Excel->setCellValue('C'.$index, $user->user_gold + $user->system_gold);
						$Excel->setCellValue('D'.$index, intval($user->silver_coin));
						$Excel->setCellValue('E'.$index, intval($user->military_exploit));
						$Excel->setCellValue('F'.$index, intval($user->prestige));
						$Excel->setCellValue('G'.$index, intval($user->grain));
						$Excel->setCellValue('H'.$index, $user->forces);
						$Excel->setCellValue('I'.$index, count($user->generalList));
						$Excel->setCellValue('J'.$index, $user->vip_flag);
						$Excel->setCellValue('K'.$index, $date);
						$Excel->setCellValue('L'.$index, date('Y-m-d', $user->new_tutorial));
						$index++;
						unset($user_list[$key]);
					}
				}
				//fileName
				$file_name = $this->publishID;
				break;
			case 3: //威望排行
				import('domain.action.util.CalculateUtil');
				import('service.action.lib.XActionContext');
				XActionContext::singleton()->publishID = $this->publishID;
				import('domain.item.PrestigeRankItem');
			 	import('service.item.lib.ItemSpecManager');
			 	import('domain.user.UserProfile');
			 	$xml = ItemSpecManager::singleton();
				$prestige_arr = array();
				if($params['country'] != 0)
				{					
					$query = new Query('PrestigeRankItem');
					$query->uid($params['country']);
					$prestigeItem = $query->exec();
					$temp = $prestigeItem->get('rank');
					foreach ($temp as $item)
					{
						if ($item['uid'] != NULL) array_push($prestige_arr , $item);
					}
				}
				else
				{
					$prestige_arr = array();
					for($country = 1;$country<4;$country++)
					{
						$query = new Query('PrestigeRankItem');
						$query->uid($country);
						$prestigeItem = $query->exec();
						$temp = $prestigeItem->get('rank');
						foreach ($temp as $item)
						{
							if ($item['uid'] != NULL) array_push($prestige_arr , $item);
						}
					}
					array_merge($prestige_arr);
					foreach ($prestige_arr as $key=>$value)
					{
						$sort[$key] = $value['prestige'];
					}
					if(count($prestige_arr)>1)
					{
						array_multisort($sort,SORT_DESC,$prestige_arr);
					}
				}
				//set title
				$Excel = $objPHPExcel->setActiveSheetIndex(0);
				$Excel->setCellValue('A1', '排名');
				$Excel->setCellValue('B1', '用户');
				$Excel->setCellValue('C1', '等级');
				$Excel->setCellValue('D1', '国家');
				$Excel->setCellValue('E1', '军团');
				$Excel->setCellValue('F1', '威望');
				$Excel->setCellValue('G1', '官职');
				$index = 2;
				$data = array();
				foreach ($prestige_arr as $key => $prestige){
					if($prestige['uid'] == NULL){
						continue;
					}
					$query = new Query('UserProfile');
					$query->uid($prestige['uid']);
					$player = $query->exec();
					if($player->name == null)continue;
					import('domain.item.LegionItem');
					$query = new Query('LegionItem'); 
					$query->uid($player->legion);
					$legion = $query->exec();					
					$country_xml = $xml->getItem(100000 + $player->country);
					$office = CalculateUtil::getCurrentOffice($player->prestige);
					$Excel->setCellValue('A'.$index, $key+1);
					$Excel->setCellValue('B'.$index, $player->name);
					$Excel->setCellValue('C'.$index, $player->level);
					$Excel->setCellValue('D'.$index, $country_xml->name);
					$Excel->setCellValue('E'.$index, $legion->army_emblem);
					$Excel->setCellValue('F'.$index, intval($player->prestige));
					$Excel->setCellValue('G'.$index, $office['name']);
					$index++;
				}
				$file_name = 'ik威望排行_' . $params['country'];
				break;
		}
			
		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle($file_name);
		
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		
		// Redirect output to a client鈥檚 web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename={$file_name}.xls");
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	
	function doVip(){
		
	}
}

?>