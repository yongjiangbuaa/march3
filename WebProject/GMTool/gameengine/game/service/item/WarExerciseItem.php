<?php
/**
 * WarExerciseItem
 * 演练属性
 */
import('persistence.dao.RActiveRecord');
class WarExerciseItem extends RActiveRecord {
	protected $frontExp;      //前军演练经验
	protected $middleExp;     //中军演练经验
	protected $backExp;  		//后军演练经验
	protected $time;					//自动恢复体力的时间

	static function getItems($uid){
		
		$data = array();
		$warExerciseItem = self::getWithUID($uid);
		if(!$warExerciseItem){
			$warExerciseItem = self::InitWarExercise($uid);
		}
		$data[] = self::resArr($warExerciseItem);
		return $data;
	}
	
	/**
	 * 数组转化为对象实例
	 *
	 * @param Array $results
	 * @param Boolean $retArr 如果只有一条记录，false返回对象，true返回数组
	 * @return Object Or Array
	 */
	static function to($results, $retArr = false){
		return self::toObject(__CLASS__, $results, $retArr);
	}
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		return self::getOne(__CLASS__,$uid);
	}
	static function InitWarExercise($uid){
		$warItem = new self;
		$warItem->uid = $uid;
		
		$warItem->frontExp = 0;
		$warItem->middleExp = 0;
		$warItem->backExp = 0;
		
		$warItem->time = 0;
		$warItem->save();
		return $warItem;
	}

	static function resArr($item){
		import('service.action.CalculateUtil');
		return array(
		'uid' => $item->uid,
		'frontExp' =>$item->frontExp,
		'middleExp' => $item->middleExp,
		'backExp' =>$item->backExp,	
		'time' => $item->time,
		);
	}


}



?>