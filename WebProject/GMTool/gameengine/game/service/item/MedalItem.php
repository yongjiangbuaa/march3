<?php
/**
 * MedalItem
 * 
 * 勋章属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class MedalItem extends RActiveRecord {
//	protected $id;
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;//用户uid
	protected $medal;//功勋值
	protected $medallist; //开启勋章list
	
	protected $frontLeader;      //前军演练累计统帅
	protected $middleLeader;     	//中军演练累计统帅
	protected $backLeader;  		//后军演练累计统帅
	/**
     +----------------------------------------------------------
     * 获得items
     +----------------------------------------------------------
     * @method getItems
     * @access public
     * @param 
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	
	public function getItems($uid, $loadMethod = false)
	{
		$medal = self::getWithUID($uid);
		$medalitem = array();
		$medalitem['medal'] = $medal->medal;
		$medalitem['frontLeader'] = $medal->frontLeader;
		$medalitem['middleLeader'] = $medal->middleLeader;
		$medalitem['backLeader'] = $medal->backLeader;
		$medalownlist = $medal->medallist;
		//取得
		$xmlHonor = ItemSpecManager::singleton('default', 'honor.xml')->getGroup('honor');
		foreach($xmlHonor as $xmlItem){
			$item = get_object_vars($xmlItem);
			$item["status"] = $medalownlist[$xmlItem->id] ? 1:0;
			$medalitem["medallist"][] = $item;
		}
		$data[] = $medalitem;
		return $data;
	}
	
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$cachekey = __CLASS__.$uid;
		$cacheVal = parent::getCacheValue($cachekey);
		if ($cacheVal)
			return $cacheVal;
		$res = self::getOne(__CLASS__, $uid);
		if($res){
			$res->unserializeProperty('medallist');
		}else{
			$medalitem = new self();
			$medalitem->uid = $uid;
			$medalitem->medal = 0;
			$medalitem->medallist = array();
			$medalitem->save();
			$res = self::getOne(__CLASS__, $uid);
			if($res){
				$res->unserializeProperty('medallist');
			}
		}
		parent::setCacheValue($cachekey, $res);
		return $res;
	}
	
	public function save(){
		$this->serializeProperty('medallist');
		parent::save();
		$this->unserializeProperty('medallist');
		$cachekey = __CLASS__.$this->uid;
		parent::delCacheValue($cachekey);
	}
}
?>