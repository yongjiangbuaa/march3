<?php
/**
 * CountryItem
 * 
 * 国家属性
 * 
 * @Entity
 * @package item
 */
class CountryItem {
//	protected $id;
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $user;
	protected $userUid;//用户uid
	private static $countryItem;
	
	static function singleton($uid = null){
		if(!self::$countryItem){
			self::$countryItem = new self($uid);
		}
		return self::$countryItem;
	}
	
	public function __construct($uid = null){
		if(!empty($uid)){
			$this->uid = $uid;
		}
	} 
	
	/**
	 * 获得国家配置
	 * Enter description here ...
	 * @param $uid 用户uid
	 */
	public function getItems($uid)
	{
		import('service.action.CountryClass');
		$user = UserProfile::getWithUID ($uid);
		$countryOrderArray = CountryClass::getCountryStrongWeakOrder($user);
		return CountryClass::formatTransfer($countryOrderArray);
	}
	
	/**
	 * 获得作用效果配置项
	 * @param $effectid 作用效果配置号
	 */
	public function getEffect($effectid){
		$this->user = UserProfile::getWithUID($this->uid);
		$country_id = $this->user->country;
		$return_data = array();
		if($country_id != '0'){
			$item = ItemSpecManager::singleton('default','country.xml')->getItem($country_id);
			if($item->effect == $effectid){
				$return_data[$effectid]['value'] = $item->value;
			}
		}
		return $return_data;
	}
}
?>