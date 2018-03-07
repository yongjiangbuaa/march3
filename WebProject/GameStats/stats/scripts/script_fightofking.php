<?php
set_time_limit ( 0 );
defined('STATS_ROOT') || define('STATS_ROOT', dirname(__DIR__));
include STATS_ROOT.'/stats.inc.php';
$server_list = get_server_list();

$client = new Redis();
$client->connect('RedisIP');

// $serverids = array_keys($servers);
	foreach ( $server_list as $server) {
		if ($server['svr_id']<1){
			continue;
		}
		$sid = $server['svr_id'];
		
		// key=fight_of_king_info1&index=1
		$rediskey = 'fight_of_king_inf' . $sid;
		
		$clientTemp = new Redis();
		$clientTemp->connect($server['ip_inner']);
		$fields = $clientTemp->hGetAll($rediskey);
		//$fields = $clientTemp->hKeys($rediskey);
		/* $fields = $client->hKeys($rediskey);
		foreach ($fields as $v){
			$tmpval = $client->hGet($rediskey,$v);
		} */
		if (is_array ( $fields )) {
			foreach ( $fields as $k => $v ) {
				//$tmpval = ( array ) json_decode ( $clientTemp->hGet($rediskey,$v));
				//$tmpval = ( array ) json_decode ($v);
				$client->hSet($rediskey,$k,$v);
				
				/* $insertArray = array ();
				$insertArray [] = $sid;
				$insertArray [] = $v;
				$insertArray [] = date ( 'Y-m-d H:i:s', intval ( $tmpval ["startTime"] / 1000 ) );
				$insertArray [] = date ( 'Y-m-d H:i:s', intval ( $tmpval ["endTime"] / 1000 ) );
				$insertArray [] = $tmpval ["kingAlliance"];
				$insertArray [] = $tmpval ["kAbbr"];
				$insertArray [] = $tmpval ["kingName"];
				$insertArray [] = $tmpval ["beKingTime"];
				$insertArray [] = intval ( $tmpval ["thDC"] / 1000 ) . "K";
				$insertArray [] = intval ( $tmpval ["trDC"] / 1000 ) . "K";
				$insertArray [] = $tmpval ["bUC"];
				$insertArray [] = $tmpval ["bAC"];
				$insertArray [] = $tmpval ["sc"];
				$values = implode ( '\',\'', $insertArray );
                $sql = "insert * () values ('$values') on d  update " */
             
			}
		} else {
			echo "error:$server ".$fields."\n";
		}
	}
?>