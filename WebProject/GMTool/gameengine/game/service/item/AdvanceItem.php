<?php
/**
 * AdvanceItem
 * 
 * 将军进阶列表
 * 
 * @Entity
 * @package item
 */
class AdvanceItem {

	public function getItems($uid){
		import('service.action.GeneralClass');
		return General::singleton()->getGeneralRankXml();
	}
}
?>