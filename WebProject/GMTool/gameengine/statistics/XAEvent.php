<?php
class XAEvent{
	static public $TUTORIAL="tutorial";
	static public $TUTORIAL_INDEX="index";
	static public $TUTORIAL_ID="tid";
	static public $TUTORIAL_STEP_NAME="step_name";

	static public $BUY_ITEM="buy.item";
	static public $BUY_ITEM_RESOURCE="resource";
	static public $BUY_ITEM_PAY_TYPE="pay_type";// 收入或支出（只能取值为income/payout）
	static public $BUY_ITEM_AMOUNT="amount";//消耗的货币总量
	static public $BUY_ITEM_NUMBER="number";//物品数量

	static public $MILESTONE="milestone";
	static public $MILESTONE_NAME="name";

	static public $COUNT="count";
	
	//follow is use for count function
	static public $TYPE="type";
	static public $LEVEL_1="level_1";
	static public $LEVEL_2="level_2";
	static public $LEVEL_3="level_3";
	static public $LEVEL_4="level_4";
	static public $LEVEL_5="level_5";
	static public $AMOUNT='amount';
}