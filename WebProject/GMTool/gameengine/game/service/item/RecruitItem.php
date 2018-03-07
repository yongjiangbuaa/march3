<?php
/**
 * RecruitItem
 * 
 * 征募属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class RecruitItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;               // 用户uid
	protected $point1 = 0;  	  // 绿 军魂
	protected $point2 = 0;  		  // 蓝 军魂
	protected $point3 = 0;       // 紫 军魂
	protected $point4 = 0;  	  // 橙 军魂
	protected $point5 = 0;  	  // 金 军魂
	protected $planId = 0;		//选择武将列表的ID
	protected $colortab = 0;		  //选择武将区域颜色 1,2,3，4
	protected $reobj1 = '';           // 演习对抗对象1
	protected $reobj2 = '';			  // 演习对抗对象2
	protected $reobj3 = '';			  // 演习对抗对象3
	protected $currentObj = 0;		//当前即将对抗第几个
	protected $special = 0;			//特殊将失败次数
	
	/**
	 * 初始化
	 * @param unknown_type $userUid
	 */
	static function init($userUid){
		$recruitItem = new self;
		$recruitItem->uid = $userUid;
		$recruitItem->save();
		return $recruitItem;
	}
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		return self::getOne(__CLASS__, $uid);
	}
	/**
	 * getItems
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	public function getItems($uid)
	{
		$recruitItem = self::getWithUID($uid);
		
		if(!isset($recruitItem)){
			$recruitItem = $this->init($uid);
		}
		$data = array();
		$data[] = self::getTabInfo($recruitItem);
		
		return $data;
			
	}
	
	/**
     +----------------------------------------------------------
     * 获得招募页面配置信息
     +----------------------------------------------------------
     * @method updateGeneralRank
     * @access public
     * @param $generalRankId 用户将要升级的军衔id
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	static function getTabInfo($recruitItem){
		$data = $recruitItem->asArray();
		$user = UserProfile::getWithUID($recruitItem->uid);
// 		$level = $user->level;
		$recruitXml = ItemSpecManager::singleton('default', 'recruit.xml')->getGroup('recruit');
		$generalids = array();
		$level_conf = array();
		foreach($recruitXml as $key => $item){
// 			if($level >= $item->player_lv){
				$data['recruit_info'][] = $item;
				for($i=1;$i<=6;$i++){
					$gen_key = 'gen_plan' . $i;
					if(!empty($item->$gen_key)){
						$generalids = array_merge($generalids,explode(",", $item->$gen_key));
					}
				}
// 			}
			$level_conf[] = $item->player_lv;
			if($item->item_plan){
				$goodsArr = explode(",", $item->item_plan);
			}
		}
	//	$goodsArr = Array('6584','6585','6602','6603');
		$generalids = array_merge($generalids,$goodsArr);//将军经验卡兑换ID
		$data['goods_conf'] = $goodsArr;
		$data['level_conf'] = $level_conf;
		import('service.action.GeneralClass');
		if(!empty($generalids)){
			$general_array = array();
			foreach ($generalids as $idItem){
				$itemXml = ItemSpecManager::singleton('default', 'generalPlan.xml')->getItem($idItem);
				$it = array();
				$it['id'] = $itemXml->id;
				$it['type1'] = $itemXml->type1;
				$it['id2'] = $itemXml->id2;
				$it['order'] = $itemXml->order;
				$it['cost'] = $itemXml->cost;
				$it['reward1'] = $itemXml->reward1;
				$it['reward2'] = $itemXml->reward2;
				$it['reward3'] = $itemXml->reward3;
				$xmlGoods = ItemSpecManager::singleton('default', 'goods.xml')->getItem($itemXml->id2);
				$it['require_level'] = $xmlGoods->require_level;
				$it['effect1'] = $xmlGoods->effect1;
				$it['effect2'] = $xmlGoods->effect2;
				$it['effect3'] = $xmlGoods->effect3;
				$it['value1'] = $xmlGoods->value1;
				$it['value2'] = $xmlGoods->value2;
				$it['value3'] = $xmlGoods->value3;
				if($itemXml->type1 == '1'){
					$general_info = General::singleton($user)->createOneGeneral($itemXml->id2);
					$general_info = self::getGeneralInfo($general_info);
					$it['generalinfo']['attrGrow'] = $general_info['attrGrow'];
					$it['generalinfo']['battle'] = $general_info['battle'];
					$it['generalinfo']['defence'] = $general_info['defence'];
					$it['generalinfo']['tech'] = $general_info['tech'];
					$it['generalinfo']['luck'] = $general_info['luck'];
					$it['generalinfo']['leader'] = $general_info['leader'];
					$it['generalinfo']['skill'] = $general_info['skill'];
					$it['generalinfo']['face'] = $general_info['face'];
					$it['generalinfo']['pro1'] = $general_info['pro1'];
					$it['generalinfo']['level'] = $general_info['level'];
					$it['generalinfo']['gen_army1'] = $general_info['gen_army1'];
					$it['generalinfo']['gen_army2'] = $general_info['gen_army2'];
					$it['generalinfo']['gen_army3'] = $general_info['gen_army3'];
					$it['generalinfo']['defaultSkill'] = $general_info['defaultSkill'];
				}
				$general_array[$itemXml->id] = $it;	
			}
			$data['generalplan'] = $general_array;
		}
		return $data;
	}
	/**
     +----------------------------------------------------------
     * 获得将军4维属性
     +----------------------------------------------------------
     * @method getGeneralInfo
     * @access public
     * @param $general_info 读取的将军属性
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	static public function getGeneralInfo($general_info){
		$m = (intval($general_info['pro']) % 10) + 1;
		$itemXml = ItemSpecManager::singleton('default', 'role.xml')->getItem(2000 + $m);
		$dataConfigXml = ItemSpecManager::singleton('default', 'item.xml')->getItem(120012);
		$k3 = $dataConfigXml->k3;
		$k4 = $dataConfigXml->k4;
		$total = $general_info['baseBattle'] + $general_info['baseDefence'] + $general_info['baseTech'] + $general_info['baseLuck'];
		$general_info['battle'] = intval($general_info['baseBattle'] / $total * $general_info['attrGrow'] * $itemXml->att1 * ($general_info['level'] + $k3));
		$general_info['defence'] = intval($general_info['baseDefence'] / $total * $general_info['attrGrow'] * $itemXml->att2 * ($general_info['level'] + $k3));
		$general_info['tech'] = intval($general_info['baseTech'] / $total * $general_info['attrGrow'] * $itemXml->att3 * ($general_info['level'] + $k3));
		$general_info['luck'] = intval($general_info['baseLuck'] / $total * $general_info['attrGrow'] * $itemXml->att4 * ($general_info['level'] + $k3));
		$general_info['leader'] = $itemXml->att5 * ($general_info['level'] + $k4);
		return $general_info;
	}
	
	
	
	/**
     +----------------------------------------------------------
     * 记录刚刚选择的招募pk对象id
     +----------------------------------------------------------
     * @method setGeneralByChoice
     * @access public
     * @param $generalRankId 用户将要升级的军衔id
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	public function setGeneralByChoice($generalplan_ids, $choice_array){
		$this->reobj1 = $generalplan_ids[0];
		$this->reobj2 = $generalplan_ids[1];
		$this->reobj3 = $generalplan_ids[2];
		$this->planId = $choice_array[0];
		$this->colortab = $choice_array[1];
		$this->special = 0;
		$this->currentObj = 1;
		$this->save();
	}
}
?>