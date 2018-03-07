<?php
/**
 * PrisonerItem
 * 
 * 勋章效果
 * @author yufuyuan
 * @date 2012-08-21
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class GeneraleffectItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;   		// 用户uid
	protected $frontUnit;   //前军
	protected $middleUnit;	//中军
	protected $lastUnit;	//后军
	protected $general;		//主将
	protected $armylist;    //增加兵种list
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){

		$res = self::getOne(__CLASS__, $uid);
		if($res){
			$res->unserializeProperty('frontUnit');
			$res->unserializeProperty('middleUnit');
			$res->unserializeProperty('lastUnit');
			$res->unserializeProperty('general');
			$res->unserializeProperty('armylist');
		}
			
		return $res;
	}
	
		/**
     +----------------------------------------------------------
     * 请求作用Item
     +----------------------------------------------------------
     * @method getItems
     * @access static public
     * @param $uid 用户uid
     * 	      $type 类型  frontUnit前军  middleUnit中军 lastUnit后军  general主将
     * 		  $attriKey 作用号
     * 		  $attriValue 作用值
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */	
	
	static public  function getItems($uid){
/*		import('util.mysql.XMysql');

		$mysql = XMysql::singleton()->connect();
		$sql = "select * from generaleffect where uid='{$uid}'";
		$res = $mysql->execResult($sql, 1);
		foreach ($res as $key=>$value){
			$value["frontUnit"] = json_decode($value["frontUnit"]);
			$value["middleUnit"] = json_decode($value["middleUnit"]);
			$value["lastUnit"] = json_decode($value["lastUnit"]);
			$value["general"] = json_decode($value["general"]);
			$res[$key] = $value;
		}
*/		
		//从CalculateUtil取出
		import('service.item.ItemSpecManager');
		import('service.item.MedalItem');
		$medalItem = MedalItem::getWithUID($uid);
		$medallist = $medalItem->medallist;
		$honorXmlGroup = ItemSpecManager::singleton('default','honor.xml')->getGroup('honor');
		foreach ($honorXmlGroup as $honorXml){
			if($medallist[$honorXml->id]){
				$linkArr = array('1'=>'frontUnit','2'=>'middleUnit','3'=>'lastUnit','4'=>'general');
				$tempData = null;
				if ($effectItem)
				{
					$tempData = $effectItem;
				}
				for($i=1;$i<8;$i++){
					$seat = $honorXml->{'seat' . $i};
					if($seat){
						$eff_key = 'eff' . $i;
						$val_key = 'val' . $i;
						$eff = $honorXml->{$eff_key};
						$val = $honorXml->{$val_key};
						switch ($seat){
							case 1:
							case 2:
							case 3:
							case 4:
								$tempData->{$linkArr[$seat]}[$eff] += $val;
								break;
							case 5:
								$tempData->frontUnit[$eff] += $val;
								$tempData->middleUnit[$eff] += $val;
								$tempData->lastUnit[$eff] += $val;
								break;
						}
					}
				}
				$effectItem = $tempData;
			}
		}
		$data = Array();
		$generals = Array();
		$frontItems = Array();
		$middleItems = Array();
		$lastItems = Array();
		$armylistItems = Array();
// 		$effectItem = self::getWithUID($uid);
		if($effectItem->frontUnit){
			foreach ($effectItem->frontUnit as $key=>$value){
				$frontItem['id']= $key;
				$frontItem['val']= $value;		
				$frontItems[] = $frontItem;
			}
		}
		if($effectItem->middleUnit){
			foreach ($effectItem->middleUnit as $key=>$value){
				$middleItem['id']= $key;
				$middleItem['val']= $value;		
				$middleItems[] = $middleItem;
			}
		}
		if($effectItem->lastUnit){
			foreach ($effectItem->lastUnit as $key=>$value){
				$lastItem['id']= $key;
				$lastItem['val']= $value;		
				$lastItems[] = $lastItem;
				
			}
		}
		if($effectItem->general){
			foreach ($effectItem->general as $key=>$value){
				$general['id']= $key;
				$general['val']= $value;		
				$generals[] = $general;
			}
		}
		if($effectItem->armylist){
			foreach (array_keys($effectItem->armylist) as $value){
				$alitem['id']= $value;
				$armylistItems[] = $alitem;
			}
		}
		
		$data[] =Array(
			'uid'=>$effectItem->uid,
			'frontUnit'=>$frontItems,
			'middleUnit'=>$middleItems,
			'lastUnit'=>$lastItems,
			'general'=>$generals,
			'armylist'=>$armylistItems,
		);
		return $data;
	}
	/**
     +----------------------------------------------------------
     * 添加作用效果
     +----------------------------------------------------------
     * @method addGeneralEffect
     * @access static public
     * @param $uid 用户uid
     * 	      $type 需要添加的类型  frontUnit前军  middleUnit中军 lastUnit后军  general主将
     * 		  $attriKey 作用号
     * 		  $attriValue 作用值
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */		
	static public function addGeneralEffect($uid, $type, $attriKey, $attriValue){
		
		$effectItem = self::getWithUID($uid);
		
		if($effectItem){
			$contents = $effectItem->{$type};
			if(key_exists($attriKey, $contents)){
				$contents[$attriKey] += $attriValue;
			}else{
				$contents[$attriKey] = $attriValue;
			}
			$effectItem->{$type} = $contents;
			$effectItem->save();
		}else{
			$effectItem = self::initGeneralEffect($uid, $type, $attriKey, $attriValue);
		}
		$result = array();
		foreach ($effectItem->{$type} as $key=>$value){
			$item = array();
			$item["id"] = $key;
			$item["val"] = $value;
			$result[] = $item;
		}
		return $result;
	}
	
/**
     +----------------------------------------------------------
     * 添加兵种使用权限
     +----------------------------------------------------------
     * @method addArms
     * @access static public
     * @param $uid 用户uid
     * 	      $arm_ids 军队ids 数组
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */		
	static public function addArms($uid, $arm_ids){
		$effectItem = self::getWithUID($uid);
		if(empty($effectItem)){
			$effectItem = self::initGeneralEffect($uid);
		}
		$armylist = $effectItem->armylist;
		if(empty($armylist)){
			$armylist = array();
		}
		foreach ($arm_ids as $arm_id){
			if(!key_exists($arm_id, $armylist)){
				$armylist[$arm_id] = array("id"=>$arm_id);
				$save_flag = true;
			}

		}
		if($save_flag){
			$effectItem->armylist = $armylist;
			$effectItem->save();
		}
		return $armylist;
	}
	
	
	/**
     +----------------------------------------------------------
     * 初始化作用效果信息
     +----------------------------------------------------------
     * @method initGeneralEffect
     * @access static public
     * @param $uid 用户uid
     * 	      $type 需要添加的类型  前军 中军 后军 主将
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */		
	static public function initGeneralEffect($uid, $type=null, $attriKey=null, $attriValue=null ){
		$generalItem = new self();
		$generalItem->uid = $uid;
		$generalItem->frontUnit = array();
		$generalItem->middleUnit = array();
		$generalItem->lastUnit = array();
		$generalItem->general = array();
		$generalItem->armylist = array();
		if(!empty($type)){
			$content = array($attriKey => $attriValue);
			$generalItem->{$type} = $content;
		}
		$generalItem->save();
		return $generalItem;
	}
	
	
	
	public function save(){
		$this->serializeProperty('frontUnit');
		$this->serializeProperty('middleUnit');
		$this->serializeProperty('lastUnit');
		$this->serializeProperty('general');
		$this->serializeProperty('armylist');
		parent::save();
		$this->unserializeProperty('frontUnit');
		$this->unserializeProperty('middleUnit');
		$this->unserializeProperty('lastUnit');
		$this->unserializeProperty('general');
		$this->unserializeProperty('armylist');
	}
 		
 	

	
}
?>