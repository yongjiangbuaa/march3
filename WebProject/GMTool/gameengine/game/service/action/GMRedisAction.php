<?php
class GMRedisAction extends XAbstractAction {
	protected $params;
	public function execute(XAbstractRequest $request){
		$this->params = $request->getParameters();
		$this->params = $this->params['data']['params'];
		$data = array();
		$redis = new Redis();
		import('service.action.DataClass');
		$redisConfig = StatData::$redisInfo;
		$redis->connect($redisConfig[0],$redisConfig[1]);
		switch ($this->params['type']){
			case 0://获得所有键名
				$data = $redis->keys('*');
				break;
			case 1://HGETAll
				$data = $redis->hGetAll($this->params['key']);
				break;
			case 2: //HGETAll 批量
				foreach ($this->params['key'] as $key) {
					$temp = $redis->hGetAll($key);
					if($temp)
						$data[$key] = $temp;
				}
				break;
			case 3://hKeys
				$data = $redis->hKeys($this->params['key']);
				break;
			case 4: //hGet
				$data = $redis->hGet($this->params['key'], $this->params['index']);
				break;
			case 5: //
				$data = $redis->hMset($this->params['key'],$this->params['index']);
				break;
			case 6://
				$data = $redis->hDel($this->params['key'],$this->params['index']);
				break;
			case 7://
				$data = $redis->get($this->params['key']);
				break;
			case 8://
				$data = $redis->set($this->params['key'],$this->params['index']);
				break;
			case 9://
				$data = $redis->del($this->params['key']);
				break;
			case 10://
				$data = $redis->keys($this->params['key'].'*');
			    	break;
  			 case 11://
		        	$data = $redis->sAdd($this->params['key'],$this->params['index']);
		        	break;
			case 12://publish 消息
					$data = $redis->publish($this->params['key'], $this->params['index']);
					break;
		}
		return XServiceResult::success($data);
	}
}
?>
