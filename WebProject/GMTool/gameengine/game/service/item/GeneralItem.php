<?php
/**
 * GeneralItem
 * 用户拥有武将
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class GeneralItem extends RActiveRecord {
	protected $itemId;			
	protected $ownerId;      //拥有者uid
	protected $face;         //头像编号
	protected $name;         //名字
	protected $type2;		//将军类型 1：主将 3：名将
	protected $nameFlag;     //是否改名 0:未改名
	protected $level;        //等级
	protected $sex;          //性别
	protected $personality;   //性格
	protected $exp;          //经验
	protected $rank;         //军阶
	protected $feats;        //功勋
	protected $hp;           //健康度
	protected $zhuangjia;    //重装甲部队专精
	protected $tuji;	 	//突击部队专精
	protected $yuancheng;	//远程部队专精
	protected $fuzhu;		 //辅助部队专精
	protected $pro;          //保留
	protected $pro1;          //职业
	
	protected $baseBattle;   //战斗基数
	protected $baseDefence;  //防御基数
	protected $baseTech;     //战术基数
	protected $baseLuck;     //幸运基数
	
	protected $battle;		 //战斗属性
	protected $defence;		 //防御属性
	protected $tech; 		 //战术属性
	protected $luck; 		 //幸运属性
	protected $leader;		 //统帅属性
	
	protected $addBattle;    //战斗加成
	protected $addDefence;   //防御加成
	protected $addTech;      //战术加成
	protected $addLuck;      //幸运加成
	protected $addLeader;	 //统帅加成
	
	protected $honorBattle;    //勋章战斗加成
	protected $honorDefence;   //勋章防御加成
	protected $honorTech;      //勋章战术加成
	protected $honorLuck;      //勋章幸运加成
	
	protected $attrGrow;	 //属性成长
	protected $forces;		 //实际带兵数 (准备废除该属性)
	protected $category;     //带兵类型
	protected $skillLimit;   //技能格数
	protected $status;        //1:空闲状态,2:出征,3:驻守
	protected $dismissible;	  //可否举荐
	
	protected $expLimit;	  //当前等级经验上限
	protected $forcesLimit;   //当前兵力上限
	protected $levelLimit;    //当前等级上限
	
	protected $gen_army1;	  //陆兵种
	protected $gen_army2;     //海兵种
	protected $gen_army3;     //空兵种
	protected $defaultSkill;     //默认技能
	protected $skid_arms;		//普通技能
	
	protected $effectStatus; //将军状态
	
	const TABLE = 'general';
	
	/**
	 * 数组转化为对象实例
	 *
	 * @param Array $results
	 * @param Boolean $retArr 如果只有一条记录，false返回对象，true返回数组
	 * @return Object Or Array
	 */
	static function to($results, $retArr = false){
		$res = self::toObject(__CLASS__, $results, $retArr);
		return $res;
	}
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		if($res){
			$res->unserializeProperty('effectStatus');
		}
		return $res;
	}
	
	public function save(){
		$this->serializeProperty('effectStatus');
		parent::save();
		$this->unserializeProperty('effectStatus');
		if(isset(self::$generalCache[$this->uid])){
			self::$generalCache[$this->uid] = null;
		}
		if($this->type2 == 1 && isset(self::$mainGeneralCache[$this->ownerId])){
			self::$mainGeneralCache[$this->ownerId] = null;
		}
	}
	/**
	 * 取得玩家所有武将信息
	 *
	 * @param String $uid
	 */
	static function getItems($uid){
		import('util.mysql.XMysql');
		import('service.item.GeneralSkillItem');
		import('service.action.GeneralClass');
		import('service.action.ScienceClass');
		$res = $data = array();
		$mysql = XMysql::singleton()->connect();
		$sql = "select * from " . self::TABLE . " where ownerId='{$uid}'";
		$res = $mysql->execResult($sql, 20);
		if(count($res) <= 0){
			return array();
		}
		$science = Science::singleton()->setUserUID($uid);
		foreach($res as $key => $general){
			$temp = array();
			if($general['effectStatus'] != NULL) 
			{
				$general['effectStatus'] = json_decode($general['effectStatus']);
				foreach ($general['effectStatus'] as $item)
				{
					$temp[] = $item;
				}
			}
			$data[$key] = General::getGeneralFiveProperty($general, $science, $uid);
			$data[$key]['skill'] = GeneralSkillItem::getSkills($general['itemId'],$general['ownerId']);
			//$data[$key]['effectStatus'] = $temp;
		}
		return $data;
	}
	
	static function getNewItems($uid, $itemUids){
		import('service.item.GeneralSkillItem');
		import('service.action.GeneralClass');
		import('service.action.ScienceClass');
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$data = array();
		$science = Science::singleton()->setUserUID($uid);
		foreach ($itemUids as $key => $itemUid){
			$res = $mysql->get(self::TABLE, array('uid' => $itemUid));
			$generalItem = $res[0];
			$data[$key] = General::getGeneralFiveProperty($generalItem, $science,$uid);
			$data[$key]['skill'] = GeneralSkillItem::getSkills($generalItem['itemId'],$generalItem['ownerId']);
		}
		return $data;
	}
	
	/**
	 * 查询玩家的所有武将
	 * 
	 */
	static function getAllGeneral($uid){
		import('util.mysql.XMysql');;
		$res = $data = array();
		$mysql = XMysql::singleton()->connect();
		$sql = "select * from " . self::TABLE . " where ownerId='{$uid}'";
		$res = $mysql->execResult($sql, 20);
		if(count($res) <= 0){
			return array();
		}	
		return $res;	
	}
	/*
	 * 查询玩家拥有武将数目
	 */
	static function getGeneralNums($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		return $mysql->exist(self::TABLE, array('ownerId' => $uid));
	}
	
	/*
	 * 根据玩家将军itemId，查询玩家将军
	 */
	static function getGeneralByItemId($uid,$itemId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		return $mysql->exist(self::TABLE, array('ownerId' => $uid,'itemId' => $itemId));
	}
	
	/**
	 * 取得玩家武将头像/名字
	 *
	 * @param unknown_type $uid
	 */
	static function getGeneralFace($uid){
		import('util.mysql.XMysql');
		$faceArr = array();
		$mysql = XMysql::singleton();
		$res = $mysql->get(self::TABLE, array('ownerId' => $uid), 'face,name', 20);
		if(!$res) return array('name' => array(), 'face' => array());
		foreach($res as $row){
			$faceArr['face'][] = $row['face'];
			$faceArr['name'][] = $row['name'];
		}
		return $faceArr;
	}
	
	static $generalCache;
	static $mainGeneralCache;
	/** 查询玩家将领  **/
	static function getCacheGeneral($uid){
		if(!self::$generalCache[$uid]){
			import('util.mysql.XMysql');
			$mysql = XMysql::singleton();
			$sqlData = $mysql->execResultWithoutLimit("select * from general where ownerId = (select ownerId from general where uid = '{$uid}')");
			if($sqlData){
				$generalItems = self::to($sqlData, true);
				foreach ($generalItems as $generalItem){
					self::$generalCache[$generalItem->uid] = $generalItem;
					if($generalItem->type2 == 1){
						self::$mainGeneralCache[$generalItem->ownerId] = $generalItem;
					}
				}
			}
		}
		if(self::$generalCache[$uid]){
			$generalItem = clone self::$generalCache[$uid];
			$generalItem->unserializeProperty('effectStatus');
			return $generalItem;
		}else{
			return array();
		}
	}
	/** 查询玩家主将  **/
	static function getGeneral($uid){
		if(!self::$mainGeneralCache[$uid]){
			import('util.mysql.XMysql');
			$mysql = XMysql::singleton();
// 			$sqlData = $mysql->get(self::TABLE, array('ownerId' => $uid,'type2' => 1));
// 			if($sqlData){
// 				$generalItem = self::to($sqlData);
// 				self::$mainGeneralCache[$uid] = $generalItem;
// 			}
			$sqlData = $mysql->execResultWithoutLimit("select * from general where ownerId = '{$uid}'");
			if($sqlData){
				$generalItems = self::to($sqlData, true);
				foreach ($generalItems as $generalItem){
					self::$generalCache[$generalItem->uid] = $generalItem;
					if($generalItem->type2 == 1){
						self::$mainGeneralCache[$generalItem->ownerId] = $generalItem;
					}
				}
			}
		}
		if(self::$mainGeneralCache[$uid]){
			$generalItem = clone self::$mainGeneralCache[$uid];
			$generalItem->unserializeProperty('effectStatus');
			return $generalItem;
		}else{
			return array();
		}
	}
}
?>