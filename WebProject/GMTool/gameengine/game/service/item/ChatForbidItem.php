<?php
/**
 * 
 * 聊天禁言类
 *
 */
class ChatForbidItem  {
	public static function getItems()
	{
		import('service.item.ItemSpecManager');
		$forbidItems = ItemSpecManager::singleton('default', 'chatforbid.xml')->getGroup('forbid');
		$data = array();
		foreach ($forbidItems as $item)
		{
			$data[$item->id] = $item->keyword;
		}
		return $data;
	}
}
?>