<?php
import('persistence.dao.RActiveRecord');
class GoldVolumePackItem extends RActiveRecord
{
	protected $uid;
	protected $packBuyTime;		//礼包购买次数     
	protected $mysteryGiftTime; //开服神秘礼包购买次数      
	protected $washAttrGiftTime; //精炼大酬宾次数
	protected $washDaily; //每天精炼获奖次数
	
	public static function getWithUID($uid)
	{
		$res = self::getOne(__CLASS__, $uid);
		if(!$res)
			$res = self::initGoldVolumePackItem($uid);
		
		$res->unserializeProperty('packBuyTime');
		$res->unserializeProperty('mysteryGiftTime');
		return $res;
	}
	
	public static function initGoldVolumePackItem($uid)
	{
		$goldVolumePack = new GoldVolumePackItem();
		$goldVolumePack->uid = $uid;
		$goldVolumePack->packBuyTime = NULL;
		$goldVolumePack->mysteryGiftTime = NULL;
		$goldVolumePack->washAttrGiftTime = NULL;
		$goldVolumePack->washDaily = NULL;
		
		return $goldVolumePack;
	}
	
	public function save()
	{
		$this->serializeProperty('packBuyTime');
		$this->serializeProperty('mysteryGiftTime');
		parent::save();
		$this->unserializeProperty('packBuyTime');
		$this->unserializeProperty('mysteryGiftTime');
	}
	
}

?>