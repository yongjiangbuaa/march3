<?php
class GMMysqlAction extends XAbstractAction {

	protected $params;
	public function execute(XAbstractRequest $request){

		
		$this->params = $request->getParameters();
		$this->params = $this->params['data']['params'];
		$data = array();
		switch ($this->params['type']){
			case 1://建表
				$data = '';
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				foreach ($this->tableSql as $table=>$sql)
					$data .= $this->createTable($mysql, $table, $sql);
				break;
			case 2://初始化world
				set_time_limit(0);
				import('service.action.WorldClass');
				$data = World::singletion($this->user)->initWorld('shijiezhengba');
				$data = true;
				break;
			case 3://查询mysql数据
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$pageLimit = $this->params['pagelimit'];
				$tablename = $this->params['tablename'];
 				$sql = "";
 				if(is_string($this->params['where1']) && $this->params['where1'] != null)
 				{
 					$sql .= "where {$this->params['where1']} {$this->params['condition1']} '{$this->params['num1']}' ";
 					if(is_string($this->params['where2']) && $this->params['where2'] != null)
 						$sql .= " and {$this->params['where2']} {$this->params['condition2']} '{$this->params['num2']}' ";
 				}
 				elseif(is_string($this->params['where2']) && $this->params['where2'] != null)
 				{
					$sql .= "where {$this->params['where2']} {$this->params['condition2']} '{$this->params['num2']}' ";
 				}
 				//获得数据总数
 				$count = $mysql->execResult("select count(1) DataCount from $tablename ".$sql);
 				$count = $count[0]['DataCount'];
 				$pager = self::page($count, $this->params['page'], $pageLimit);
 				$index = $pager['offset'];
 				if($count - $index < $pageLimit)
 				{
 					$limit = $count - $index;
 					$sql .= "limit $index,$limit";
 				}
 				else 
 				{
 					$sql .= "limit $index,$pageLimit";
 				}
 				$sql = "select * from $tablename ".$sql;
 				$data['sql'] = $sql;
 				$result = $mysql->execute($sql);
 				if ($result) {
 					while ($curRow = mysql_fetch_assoc($result) )
 							$data['data'][] = $curRow;
 				}
 				$data['page'] = $pager['pager'];
 				$data['index'] = $index;
 				$data['count'] = $count;
				break;
			case 4://获得mysql配置
				$data['host'] 	= xingcloud_get("mysql_host");
				$data['port'] 	= xingcloud_get("mysql_port");
				$data['user'] 	= xingcloud_get("mysql_user");
				$data['password'] = xingcloud_get("mysql_passwd");
				$data['database'] = xingcloud_get("mysql_db");
				break;
			case 5://执行数据库语句
				//如果是查询返回查询结果
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sql = trim($this->params['sql']);
				if (!strcasecmp(substr($sql,0,6),'select'))
				{
					if(strrpos($sql,'limit'))
					{
						$result = $mysql->execute($sql);
						if ($result){
							while ($curRow = mysql_fetch_assoc($result) )
								$data['data'][] = $curRow;
						}
					}
					else {
						$data['data'] = $mysql->execResult($sql);
						//file_put_contents('/data/htdocs/ifadmin/admin/sqlResultYao.log', $sql.','.print_r($data['data'],true)."\n",FILE_APPEND);
					}
				}elseif (!strcasecmp(substr($sql,0,8),'describe') || !strcasecmp(substr($sql,0,4),'desc')){
					$tableName = trim(strrchr($sql, ' '));
					$data['data'] = $mysql->describeTable($tableName);
				}else 
				{
					$data['result'] = $mysql->execute($sql);
				}
				$data['effect'] = $mysql->affected_rows();
				break;
			case 6://查询数据库结构
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$tableName = $this->params['table'];
				$data['struct'] = $mysql->describeTable($tableName);
				break;
			case 7://修改数据库结构//TODO未完成
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$tableName = $this->params['table'];
				$oldStruct = $mysql->describeTable($tableName);
				$newStruct = $this->params['newStruct'];
				$modify = array();//MODIFY COLUMN `test4`  tinyint(22) NOT NULL DEFAULT 0 FIRST ;
				$change = array();//CHANGE COLUMN `test4` `test1`  tinyint(21) NOT NULL DEFAULT 0 
				//ALTER TABLE `building` ADD COLUMN `addSource`  int(10) NOT NULL AFTER `lastUpdateTime`;
				$changeArr = array();
				foreach ($newStruct as $index=>$struct)
				{
					if($oldStruct[$index]['name'] != $struct['name'])
					{
						$change[] = $index;
						$changeArr[$index] = $struct['name'];
					}
					elseif($oldStruct[$index]['max_length'] != $struct['max_length'] ||
							$oldStruct[$index]['default_value'] != $struct['default_value'])
					{
						$modify[] = $index;
					}
					elseif((($struct['not_null'] == 'true' && !$oldStruct[$index]['not_null']) || ($struct['not_null'] == 'false' && $oldStruct[$index]['not_null']))
						||(($struct['has_default'] == 'true' && !$oldStruct[$index]['has_default']) || ($struct['has_default'] == 'false' && $oldStruct[$index]['has_default'])))
					{
							$modify[] = $index;
					}
				}
				if($modify == array() && $change == array())
					break;
				$sql = "ALTER TABLE `$tableName`";
				foreach ($newStruct as $index=>$struct)
				{
					if(in_array($index, $change))
					{
						$sql .= " CHANGE COLUMN `$index` `{$changeArr[$index]}`";
					}
					elseif(in_array($index, $modify))
						$sql .= " MODIFY COLUMN `$index`";
					else
						continue;
					$sql .= " {$struct['type']}({$struct['max_length']})";
					if($struct['not_null'] == 'true'|| $struct['primary_key'] == 'true')
						$sql .= " NOT NULL";
					else
						$sql .= " NULL";
					if($struct['has_default'] == 'true')
						$sql .= " DEFAULT {$struct['default_value']}";
					$sql .= ",";
				}
				$sql = substr($sql,0,strlen($sql) -1);
				$sql .= ";";
				$mysql->execute($sql);
				$data['sql'] = $sql;
				$data['old'] = $oldStruct;
				$data['new'] = $newStruct;
				$data['change'] = $change;
				$data['modify'] = $modify;
				break;
			case 8://删除所有数据库数据
				if($this->params['code'] != 'ik2Gm')
					return XServiceResult::clientError('code error');
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				//删除所有数据操作
				foreach ($this->tableSql as $table=>$sql)
				{
					if($mysql->execute("Truncate `{$table}`"))
						$data[$table] = 1;
					else
						$data[$table] = 0;
				}
				break;
			case 9://获得所有表
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$tables = $mysql->getTables();
				$data = array();
				$data['tableList'] = $tables;
				$data['result'] = true;
				break;
			case 10://获得某列的所有值
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$data['result'] = true;
				$tablename = $this->params['table'];
				$column = $this->params['column'];
				$where = $this->params['whereSql'];
				$data['column'] = $mysql->execResult("select $column from $tablename $where group by $column order by $column",100);
				break;
			case 11://执行查询语句，无limit判断，只支持select
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sql = $this->params['sql'];
				if (strcasecmp(substr($sql,0,6),'select') == 0)
					$data['data'] = $mysql->execResultWithoutLimit($sql);
				break;
			case 12://INNODB
				$data = '';
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				foreach ($this->tableSql as $table=>$sql)
					$data .= $this->INNODB($mysql, $table);
				break;
			case 13://执行多条查询，无limit判断，只支持select
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sqls = explode(';', $this->params['sql']);
				foreach ($sqls as $sql){
					if (strcasecmp(substr($sql,0,6),'select') == 0)
						$data['data'][] = $mysql->execResultWithoutLimit($sql);
				}
				break;
			case 14://初始化world的遗迹
				set_time_limit(0);
				import('service.action.WorldClass');
				$data = World::singletion($this->user)->initRelic();
				$data = true;
				break;
			case 15://OP用数据库查询，只能查询
				//返回查询结果
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sql = $this->params['sql'];
				$pregArr = array('update','insert','delete','truncate','alter');
				$enabled = true;
				foreach ($pregArr as $preg){
					if(stripos($sql,$preg) !== false){
						$enable = false;
					}
				}
				if (!strcasecmp(substr($sql,0,6),'select') && $enabled)
				{
					if(strrpos($sql,'limit'))
					{
						$result = $mysql->execute($sql);
						while ($curRow = mysql_fetch_assoc($result) )
							$data['data'][] = $curRow;
					}
					else
						$data['data'] = $mysql->execResult($sql);
					$data['effect'] = $mysql->affected_rows();
				}
				break;
			case 16://根据传入的表名列获得对应的表结构
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$tables = $mysql->getTables();
				foreach ($tables as $table){
					$tableName = reset($table);
					$sql = "SHOW COLUMNS FROM `{$tableName}`";
					$struct = array();
					$sqlDatas = $mysql->execResultWithoutLimit("$sql");
					foreach ($sqlDatas as $sqlData){
						$struct[$sqlData['Field']] = $sqlData;
					}
					$data[$tableName]['struct'] = $struct;
					$sql = "SHOW INDEX FROM `{$tableName}`";
					$index = array();
					$sqlDatas = $mysql->execResultWithoutLimit("$sql");
					foreach ($sqlDatas as $sqlData){
						$index[$sqlData['Key_name'].$sqlData['Column_name']] = $sqlData;
					}
					$data[$tableName]['index'] = $index;
				}
				break;
		}
		return XServiceResult::success($data);
	}
	static function page($total, $curr_page, $page_limit){
		$page = empty($curr_page) ? 1 : $curr_page;
		$last_page = ceil($total/$page_limit); //最后页，也是总页数
		$page = min($last_page, $page);
		$prepg = $page - 1; //上一页
		$nextpg = ($page == $last_page ? 0 : $page + 1); //下一页
		$offset = intval(max($page - 1, 0) * $page_limit);
	
		//开始分页导航条代码：
		$pagenav="显示第 <B>".($total ? ($offset + 1):0)."</B>-<B>".min($offset + $page_limit ,$total)."</B> 条记录，共 $total 条记录";
		//如果只有一页则跳出函数：
		if($last_page<=1) return array('offset' => $offset, 'pager' => NULL);
		$pagenav.=" <a href='#' onclick='getData(1)'>首页</a> ";
		if($prepg) $pagenav.=" <a href='#' onclick='getData({$prepg})'>前页</a> "; else $pagenav.=" 前页 ";
		if($nextpg) $pagenav.=" <a href='#' onclick='getData({$nextpg})'>后页</a> "; else $pagenav.=" 后页 ";
		$pagenav.=" <a href='#' onclick='getData({$last_page})'>尾页</a> ";
		$pagenav.=" 第{$page}页  共 {$last_page} 页";
		$pagenav .= " 跳转 <input class='input-small' size=3 id='turn' onKeyUp='check(this)' value=''> <input type='button' value='go' onclick='turnPage()'>";
		return array('offset' => $offset, 'pager' => $pagenav);
	}
	private function createTable(XMysql $mysql,$table,$sql = null)
	{
		if(!$sql)
			$sql = $this->tableSql[$table]; 
		if($sql)
		{
			$sql = str_ireplace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $sql);
			if($mysql->execute("SHOW COLUMNS FROM `{$table}`"))
				return "<label style='color:purple;font-size:16px'>{$table}已存在</label>";
			if($mysql->execute($sql))
				return "<label style='color:blue;font-size:16px'>{$table}成功</label>";
		}
		return "<label style='color:red;font-size:16px'>{$table}失败</label>";
	}
	private function INNODB(XMysql $mysql,$table,$sql = null){
		$sql = "ALTER TABLE `$table` ENGINE=InnoDB;<br />";
		return $sql;
		if($mysql->execute($sql))
			return "<label style='color:blue;font-size:16px'>{$table}成功</label>";
		return "<label style='color:red;font-size:16px'>{$table}失败</label>";
	}
	private $tableSql = array(
			"actionlog"=>"CREATE TABLE `actionlog` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  `action` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `type` int(40) NOT NULL,
  `code` int(10) NOT NULL DEFAULT '0',
  `count` int(10) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_user` (`action`,`type`,`code`,`time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"admin"=>"CREATE TABLE `admin` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `passmd5` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `auth` text COLLATE utf8_unicode_ci NOT NULL,
  `groupid` int(11) NOT NULL DEFAULT '0',
  `addtime` bigint(20) NOT NULL,
  `lastactive` bigint(20) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1234567903 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"alliance"=>"CREATE TABLE `alliance` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `exp` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `leader` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createTime` int(11) NOT NULL,
  `post` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `declaration` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` int(20) NOT NULL,
  `memberNum` int(20) NOT NULL,
  `vpNum` int(20) DEFAULT NULL,
  `vpLimitNum` int(20) DEFAULT NULL,
  `memLimitNum` int(20) DEFAULT NULL,
  `time1` int(11) DEFAULT NULL,
  `time2` int(11) DEFAULT NULL,
  `points` int(11) DEFAULT '0',
  `resource` int(11) NOT NULL DEFAULT '-5',
  `agreeCount` int(11) DEFAULT '0',
  `lastInviteTime` int(11) DEFAULT NULL,
  `allianceWarfareRemainNum` int(11) DEFAULT NULL,
  `flushTime` int(11) DEFAULT NULL,
  `heroLevel` int(11) DEFAULT '1',
  `heroExp` bigint(20) DEFAULT '0',
  `currMaxDarkLevel` int(11) DEFAULT '1',
  `firstDarkFlag` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"allianceactivity"=>"CREATE TABLE `allianceactivity` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `allianceId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `activityId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `darkLevel` int(11) DEFAULT NULL,
  `playerList` blob,
  `npcInfo` blob,
  `startTime` int(11) DEFAULT NULL,
  `battleCD` int(11) DEFAULT NULL,
  `battleResult` int(11) DEFAULT NULL,
  `battleRecord` blob,
  `topFight` blob,
  `lastRound` int(11) DEFAULT NULL,
  `repaireFlag` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"alliancebattleapply"=>"CREATE TABLE `alliancebattleapply` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `alliance` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `time` int(11) NOT NULL,
  PRIMARY KEY (`uid`,`time`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"alliancebattlepic"=>"CREATE TABLE `alliancebattlepic` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `battlepic` blob,
  `time` int(11) DEFAULT NULL,
  `generatetime` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"alliancebattleuserapply"=>"CREATE TABLE `alliancebattleuserapply` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`uid`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"allianceboss"=>"CREATE TABLE `allianceboss` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `bossId` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `attack` blob NOT NULL,
  `time` int(11) DEFAULT NULL,
  `leftTime` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"allianceenemy"=>"CREATE TABLE `allianceenemy` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `allianceId1` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `allianceId2` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `endTime` int(20) DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"allianceenemylog"=>"CREATE TABLE `allianceenemylog` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `timeStamp` int(10) NOT NULL DEFAULT '0',
  `fromAlliance` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fromName` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fromLevel` int(10) NOT NULL DEFAULT '0',
  `fromMember` int(10) NOT NULL DEFAULT '0',
  `toAlliance` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `toName` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `toLevel` int(10) NOT NULL DEFAULT '0',
  `toMember` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"alliancehis"=>"CREATE TABLE `alliancehis` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `AllianceId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `para1` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `para2` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `para3` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `para4` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `para5` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` int(4) DEFAULT NULL,
  `createTime` int(11) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"alliancemem"=>"CREATE TABLE `alliancemem` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `AllianceId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `MemberId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(3) NOT NULL,
  `status` int(2) DEFAULT NULL,
  `createTime` int(11) NOT NULL,
  `contribution` int(20) DEFAULT NULL,
  `power` int(20) DEFAULT NULL,
  `totalExp` int(20) DEFAULT '0',
  `dailyExp` int(20) DEFAULT '0',
  `time` int(20) NOT NULL DEFAULT '10',
  `attProclaimFlag` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `attProclaimFlag_index` (`attProclaimFlag`) USING BTREE,
  KEY `allianceid_memberid_index` (`AllianceId`,`MemberId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"alliancepointsrank"=>"CREATE TABLE `alliancepointsrank` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `lastRank` int(11) DEFAULT NULL,
  `currRank` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"arena"=>"CREATE TABLE `arena` (
  `uid` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `wins` int(8) DEFAULT '0',
  `rank` int(8) DEFAULT '0',
  `endTime` int(11) NOT NULL,
  `trend` int(2) DEFAULT '0',
  `cd` int(11) DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_rank` (`rank`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			"arenarank"=>"CREATE TABLE `arenarank` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `rankList` mediumblob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"arenarecord"=>"CREATE TABLE `arenarecord` (
  `uid` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `attacker` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `defender` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `attName` varchar(100) NOT NULL,
  `createAt` int(11) NOT NULL,
  `reportUid` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `attRank` int(8) NOT NULL,
  `win` int(2) DEFAULT '0',
  `attTrend` int(2) DEFAULT '0',
  `defName` varchar(100) NOT NULL,
  `defRank` int(8) NOT NULL,
  `defTrend` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_attacker` (`attacker`) USING BTREE,
  KEY `index_defender` (`defender`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			"arenareward"=>"CREATE TABLE `arenareward` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `rank` int(10) NOT NULL,
  `time` int(20) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"blackshop"=>"CREATE TABLE `blackshop` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId1` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `bought1` int(11) NOT NULL DEFAULT '0',
  `itemId2` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `bought2` int(11) NOT NULL DEFAULT '0',
  `itemId3` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `bought3` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"building"=>"CREATE TABLE `building` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `cityType` int(11) NOT NULL,
  `pos` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `trend` int(11) NOT NULL,
  `finishTime` bigint(20) NOT NULL,
  `lastUpdateTime` bigint(20) NOT NULL DEFAULT '0',
  `totalResource` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"cdalignment"=>"CREATE TABLE `cdalignment` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(10) NOT NULL DEFAULT '1',
  `pos` int(10) NOT NULL DEFAULT '0',
  `expireTime` int(10) NOT NULL DEFAULT '0',
  `redFlag` int(10) NOT NULL DEFAULT '0',
  `cdTime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`,`type`,`expireTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"city"=>"CREATE TABLE `city` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `level` tinyint(10) NOT NULL DEFAULT '1',
  `boom` int(10) NOT NULL DEFAULT '0',
  `boomLoss` int(10) NOT NULL DEFAULT '0',
  `money` int(10) NOT NULL DEFAULT '0',
  `mineral` int(10) NOT NULL DEFAULT '0',
  `oil` int(10) NOT NULL DEFAULT '0',
  `food` int(10) NOT NULL DEFAULT '0',
  `soldiers` int(10) NOT NULL DEFAULT '0',
  `forces` int(10) NOT NULL DEFAULT '0',
  `lastUpdateCity` int(5) NOT NULL DEFAULT '0',
  `groundIndex` blob,
  `forceMarketList` blob,
  `forceMarketFlushTime` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"code"=>"CREATE TABLE `code` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `delivery` int(11) DEFAULT NULL,
  `startTime` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `endTime` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `goods` blob,
  `playerName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `playerUid` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `receiveTime` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"codehistory"=>"CREATE TABLE `codehistory` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `history` blob NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"contrrank"=>"CREATE TABLE `contrrank` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AllianceId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `contribution1` int(20) DEFAULT NULL,
  `contribution2` int(20) DEFAULT NULL,
  `contribution3` int(20) DEFAULT NULL,
  `contribution4` int(20) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"cynthia"=>"CREATE TABLE `cynthia` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `startTime` int(10) NOT NULL,
  `status` int(4) DEFAULT '1',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"dailyreset"=>"CREATE TABLE `dailyreset` (
  `collectFreeTime` int(8) NOT NULL DEFAULT '0',
  `collectBuyTime` int(8) NOT NULL DEFAULT '0',
  `uid` varchar(42) COLLATE utf8_unicode_ci NOT NULL,
  `everydayBuyTimes` int(8) NOT NULL DEFAULT '0',
  `cleanTrainTimes` int(8) NOT NULL,
  `bodyStatus` int(8) NOT NULL DEFAULT '10',
  `lowexerciseTimes` int(8) NOT NULL DEFAULT '0',
  `highexerciseTimes` int(8) NOT NULL DEFAULT '0',
  `dailyBuyDiscount` blob,
  `nuclearStatus` int(8) NOT NULL DEFAULT '0',
  `rankStatus` int(8) DEFAULT NULL,
  `yellowVipYearReward` int(11) DEFAULT NULL,
  `yellowVipGift` int(11) DEFAULT NULL,
  `bmRefresh` int(11) DEFAULT NULL,
  `bmBuy` blob,
  `orders` int(11) DEFAULT '0',
  `golds` int(11) DEFAULT '0',
  `worshipFlag` int(11) DEFAULT '0',
  `zongTimes` int(11) DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
"drillfightreport"=>"CREATE TABLE `drillfightreport` (
  `generalId` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `report` blob NOT NULL,
  PRIMARY KEY (`generalId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"effect"=>"CREATE TABLE `effect` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `effectList` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"exchangelog"=>"CREATE TABLE `exchangelog` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  `cost` int(10) NOT NULL DEFAULT '0',
  `costCount` int(10) NOT NULL DEFAULT '0',
  `reward` int(10) NOT NULL DEFAULT '0',
  `rewardCount` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_user` (`user`,`time`,`cost`,`reward`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"exploit"=>"CREATE TABLE `exploit` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(2) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"famousgeneral"=>"CREATE TABLE `famousgeneral` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `currentGeneral1` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `currentGeneral2` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `currentGeneral3` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `hourTimes` int(11) NOT NULL DEFAULT '0',
  `dayTimes` int(11) NOT NULL DEFAULT '0',
  `lastUpdate` int(11) NOT NULL DEFAULT '0',
  `notPurpleG` int(11) NOT NULL DEFAULT '0',
  `notOrageG` int(11) NOT NULL DEFAULT '0',
  `notGoldG` int(11) NOT NULL DEFAULT '0',
  `notPurpleS` int(11) NOT NULL DEFAULT '0',
  `notOrageS` int(11) NOT NULL DEFAULT '0',
  `notGoldS` int(11) NOT NULL DEFAULT '0',
  `ensureColor` int(11) NOT NULL DEFAULT '0',
  `firstGold` int(2) NOT NULL DEFAULT '1',
  `vipFreeTimes` int(2) NOT NULL DEFAULT '0',
  `recruit1` int(2) NOT NULL DEFAULT '0',
  `recruit2` int(2) NOT NULL DEFAULT '0',
  `recruit3` int(2) NOT NULL DEFAULT '0',
  `haveGold` int(2) NOT NULL DEFAULT '-1',
  `progressBar` int(11) NOT NULL DEFAULT '-1',
  `progressBar1` int(11) NOT NULL DEFAULT '-1',
  `progressBar2` int(11) NOT NULL DEFAULT '-1',
  `ordersTimes` int(11) NOT NULL DEFAULT '0',
  `sysGoldTimes` int(11) NOT NULL DEFAULT '0',
  `usrGoldTimes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"farm"=>"CREATE TABLE `farm` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `exp` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '1',
  `spy` int(11) NOT NULL DEFAULT '0',
  `spyUpdateTime` int(11) NOT NULL DEFAULT '0',
  `plantId` int(11) NOT NULL DEFAULT '0',
  `finishTime` int(11) NOT NULL DEFAULT '0',
  `reward` int(11) NOT NULL DEFAULT '0',
  `stealRecord` blob NOT NULL,
  `processNum` int(11) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"farmhistory"=>"CREATE TABLE `farmhistory` (
  `uid` varchar(42) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(42) NOT NULL,
  `stealUid` varchar(42) NOT NULL,
  `type` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `param1` varchar(42) NOT NULL,
  `param2` varchar(42) NOT NULL,
  `param3` varchar(42) NOT NULL,
  `param4` varchar(42) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `param5` varchar(42) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `time` (`time`) USING BTREE,
  KEY `index_ownerId` (`ownerId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			"festival"=>"CREATE TABLE `festival` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `dumplingId` int(11) NOT NULL DEFAULT '0',
  `dumplingFixed` int(11) NOT NULL DEFAULT '0',
  `dumplingTimes` int(11) NOT NULL DEFAULT '0',
  `dumplingFreeTimes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"fightbehavior"=>"CREATE TABLE `fightbehavior` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fromUser` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `toUser` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` int(2) NOT NULL,
  `waitTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL,
  `forces` int(10) DEFAULT '0',
  `matrix` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `generalList` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `x` int(4) DEFAULT '0',
  `y` int(4) DEFAULT '0',
  `mineral` int(6) DEFAULT '0',
  `oil` int(6) DEFAULT '0',
  `food` int(6) DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_fromUser` (`fromUser`) USING BTREE,
  KEY `index_toUser` (`toUser`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"fightreport"=>"CREATE TABLE `fightreport` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(2) DEFAULT '0',
  `create_at` int(10) NOT NULL,
  `report` mediumblob,
  PRIMARY KEY (`uid`),
  KEY `index_time` (`create_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"fightreportdrill"=>"CREATE TABLE `fightreportdrill` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(2) DEFAULT '0',
  `create_at` int(10) NOT NULL,
  `report` mediumblob,
  PRIMARY KEY (`uid`),
  KEY `index_time` (`create_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"firstlogin"=>"CREATE TABLE `firstlogin` (
  `platformAddress` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(10) NOT NULL,
  `timeStamp` int(10) NOT NULL,
  PRIMARY KEY (`platformAddress`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"fiveonlinedata"=>"CREATE TABLE `fiveonlinedata` (
  `timeStamp` int(10) NOT NULL DEFAULT '0',
  `count` int(10) DEFAULT NULL,
  PRIMARY KEY (`timeStamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"formation"=>"CREATE TABLE `formation` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(2) NOT NULL,
  `isDefault` int(2) NOT NULL DEFAULT '0',
  `generalList` varchar(600) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"friend"=>"CREATE TABLE `friend` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` int(1) DEFAULT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `playerUid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `addTime` bigint(20) DEFAULT NULL,
  `bqqFriend` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`) USING BTREE,
  KEY `index_playerUid` (`playerUid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"general"=>"CREATE TABLE `general` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `nameFlag` int(2) DEFAULT '0',
  `face` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `sex` int(2) NOT NULL,
  `personality` int(2) NOT NULL,
  `level` int(6) NOT NULL,
  `exp` int(11) DEFAULT NULL,
  `rank` int(2) DEFAULT NULL,
  `feats` int(11) DEFAULT NULL,
  `hp` int(4) DEFAULT NULL,
  `zhuangjia` int(11) NOT NULL,
  `tuji` int(11) NOT NULL,
  `yuancheng` int(11) NOT NULL,
  `fuzhu` int(11) NOT NULL,
  `pro` int(2) NOT NULL,
  `baseBattle` float NOT NULL,
  `baseDefence` float NOT NULL,
  `baseTech` float NOT NULL,
  `baseLuck` float NOT NULL,
  `attrGrow` float(11,2) NOT NULL,
  `skillLimit` int(2) NOT NULL,
  `addBattle` int(11) DEFAULT '0',
  `addDefence` int(11) DEFAULT '0',
  `addTech` int(11) DEFAULT '0',
  `addLuck` int(11) DEFAULT '0',
  `addLeader` int(11) DEFAULT '0',
  `category` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(2) DEFAULT '0',
  `dismissible` int(1) NOT NULL DEFAULT '0',
  `type2` int(2) NOT NULL,
  `gen_army1` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `gen_army2` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `gen_army3` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `pro1` int(2) NOT NULL,
  `honorBattle` int(11) DEFAULT NULL,
  `honorDefence` int(11) DEFAULT NULL,
  `honorTech` int(11) DEFAULT NULL,
  `honorLuck` int(11) DEFAULT NULL,
  `defaultSkill` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `effectStatus` blob,
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`) USING BTREE,
  KEY `index_level_exp` (`level`,`exp`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"generaleffect"=>"CREATE TABLE `generaleffect` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `frontUnit` blob,
  `middleUnit` blob,
  `lastUnit` blob,
  `general` blob,
  `armylist` blob NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"generalinit"=>"CREATE TABLE `generalinit` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `refreshTime1` bigint(20) NOT NULL DEFAULT '0',
  `refreshTime2` bigint(20) NOT NULL DEFAULT '0',
  `refreshTime3` bigint(20) NOT NULL DEFAULT '0',
  `generalList` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"generalrank"=>"CREATE TABLE `generalrank` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `rankList` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"generalskill"=>"CREATE TABLE `generalskill` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `skillList` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"goldlog"=>"CREATE TABLE `goldlog` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  `goldType` int(10) NOT NULL DEFAULT '0',
  `type` varchar(40) COLLATE utf8_unicode_ci DEFAULT '',
  `typeParam` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `count` int(10) NOT NULL DEFAULT '0',
  `remain` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_user` (`user`,`goldType`,`type`,`time`,`typeParam`) USING BTREE,
  KEY `index_time` (`time`,`type`,`goldType`,`user`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"goldvolumepack"=>"CREATE TABLE `goldvolumepack` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `packBuyTime` blob NOT NULL,
  `mysteryGiftTime` blob NOT NULL,
  `washAttrGiftTime` blob NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"goodslog"=>"CREATE TABLE `goodslog` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  `type` varchar(40) COLLATE utf8_unicode_ci DEFAULT '',
  `typeParam` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `count` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_user` (`user`,`type`,`time`,`typeParam`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"invattr"=>"CREATE TABLE `invattr` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `inventoryId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `attr1` int(10) DEFAULT NULL,
  `attrValue1` float(10,2) DEFAULT NULL,
  `attrLevel1` int(10) DEFAULT NULL,
  `attr2` int(10) DEFAULT NULL,
  `attrValue2` float(10,2) DEFAULT NULL,
  `attrLevel2` int(10) DEFAULT NULL,
  `attr3` int(10) DEFAULT NULL,
  `attrValue3` float(10,2) DEFAULT NULL,
  `attrLevel3` int(10) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index` (`ownerId`,`inventoryId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"inventory"=>"CREATE TABLE `inventory` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `level` int(6) NOT NULL DEFAULT '0',
  `count` int(6) NOT NULL,
  `useGeneralId` varchar(40) COLLATE utf8_unicode_ci DEFAULT '',
  `attr1` int(10) DEFAULT NULL,
  `attrValue1` float(10,2) DEFAULT NULL,
  `attrLevel1` int(10) DEFAULT NULL,
  `attr2` int(10) DEFAULT NULL,
  `attrValue2` float(10,2) DEFAULT NULL,
  `attrLevel2` int(10) DEFAULT NULL,
  `attr3` int(10) DEFAULT NULL,
  `attrValue3` float(10,2) DEFAULT NULL,
  `attrLevel3` int(10) DEFAULT NULL,
  `statAttr` int(10) DEFAULT NULL,
  `statAttrValue` float(10,2) DEFAULT NULL,
  `statAttrLevel` int(10) DEFAULT NULL,
  `pos` int(2) DEFAULT '0',
  `embed` int(10) NOT NULL DEFAULT '0',
  `gem` blob NOT NULL,
  `temp` int(10) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `Index_ownerId` (`ownerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"killgeneral"=>"CREATE TABLE `killgeneral` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `currentPoint` int(10) NOT NULL DEFAULT '0',
  `totalPoint` int(10) NOT NULL DEFAULT '0',
  `buyGold` int(10) NOT NULL,
  `buyGoldPoint` int(10) NOT NULL,
  `shootTime` tinyint(4) NOT NULL DEFAULT '0',
  `general1` tinyint(4) NOT NULL DEFAULT '0',
  `general2` tinyint(4) NOT NULL DEFAULT '0',
  `general3` tinyint(4) NOT NULL DEFAULT '0',
  `refreshTime` int(10) NOT NULL,
  `rewardFlag` tinyint(4) NOT NULL DEFAULT '0',
  `generalFlag` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"log"=>"CREATE TABLE `log` (
  `uid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `timeStamp` int(10) NOT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param4` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param5` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data4` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data5` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data6` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data7` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data8` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mix` blob,
  PRIMARY KEY (`uid`),
  KEY `index` (`timeStamp`,`user`,`type`,`param1`,`param2`,`param3`) USING BTREE,
  KEY `index2` (`type`,`timeStamp`,`user`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"logdaily"=>"CREATE TABLE `logdaily` (
  `uid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `timeStamp` int(10) NOT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param4` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param5` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data4` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data5` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data6` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data7` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data8` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index` (`timeStamp`,`user`,`type`,`param1`,`param2`,`param3`) USING BTREE,
  KEY `index2` (`type`,`timeStamp`,`user`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"logindata"=>"CREATE TABLE `logindata` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `timeStamp` int(10) NOT NULL,
  `user` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_user` (`user`),
  KEY `index_timeStamp` (`timeStamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"logstat"=>"CREATE TABLE `logstat` (
  `uid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `timeStamp` int(10) NOT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param4` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param5` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data4` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data5` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data6` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data7` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data8` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mix` blob,
  PRIMARY KEY (`uid`),
  KEY `index` (`timeStamp`,`user`,`type`,`param1`,`param2`,`param3`) USING BTREE,
  KEY `index2` (`type`,`timeStamp`,`user`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"logtemp"=>"CREATE TABLE `logtemp` (
  `uid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `timeStamp` int(10) NOT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param4` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param5` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data4` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data5` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data6` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data7` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data8` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mix` blob,
  PRIMARY KEY (`uid`),
  KEY `index` (`timeStamp`,`user`,`type`,`param1`,`param2`,`param3`) USING BTREE,
  KEY `index2` (`type`,`timeStamp`,`user`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"lord"=>"CREATE TABLE `lord` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `commandExp` int(10) NOT NULL DEFAULT '0',
  `attrPoint` int(10) NOT NULL DEFAULT '0',
  `economy` int(10) NOT NULL DEFAULT '0',
  `construct` int(10) NOT NULL DEFAULT '0',
  `military` int(10) NOT NULL DEFAULT '0',
  `defense` int(10) NOT NULL DEFAULT '0',
  `pvphonor` int(10) NOT NULL DEFAULT '0',
  `message` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `generalrankId` int(10) NOT NULL,
  `rankgifttime` int(11) NOT NULL DEFAULT '0',
  `activityReward` int(11) DEFAULT NULL,
  `allianceContr` int(11) NOT NULL DEFAULT '0',
  `isFirstAlliance` enum('Y','N') COLLATE utf8_unicode_ci DEFAULT 'N',
  `firstAlliance` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `saleBuyTimes` int(8) NOT NULL DEFAULT '0',
  `saleBuyRewardId` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `saleBuyTurns` int(8) NOT NULL DEFAULT '0',
  `version` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `donatedMoneyDaily` int(11) DEFAULT NULL,
  `donatedTotalMoney` bigint(20) DEFAULT NULL,
  `leaguecd` int(10) NOT NULL DEFAULT '0',
  `lastLeague` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `leaveAllianceTime` int(11) DEFAULT NULL,
  `buyGenPlaces` int(8) NOT NULL DEFAULT '0',
  `darkHealthDegree` int(11) DEFAULT NULL,
  `generalRewardRankId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"mail"=>"CREATE TABLE `mail` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId` char(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `toUser` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `fromUser` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fromName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `contents` blob NOT NULL,
  `type` int(2) NOT NULL,
  `flag` int(2) DEFAULT '0',
  `createTime` int(11) NOT NULL,
  `status` int(2) DEFAULT '1',
  `rewardId` varchar(4000) COLLATE utf8_unicode_ci DEFAULT '0',
  `rewardStatus` int(2) NOT NULL DEFAULT '0',
  `fightReport` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_fromUser` (`fromUser`) USING BTREE,
  KEY `index_toUser` (`toUser`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"medal"=>"CREATE TABLE `medal` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `medal` int(10) NOT NULL DEFAULT '0',
  `medallist` blob,
  `frontLeader` int(10) NOT NULL DEFAULT '0',
  `middleLeader` int(10) NOT NULL DEFAULT '0',
  `backLeader` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"messageball"=>"CREATE TABLE `messageball` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `itemId` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `fromUser` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `toUser` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `contents` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_fromUser` (`fromUser`),
  KEY `index_toUser` (`toUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"militarydrill"=>"CREATE TABLE `militarydrill` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `scene` int(10) NOT NULL,
  `unlockGeneralId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `generalList` blob NOT NULL,
  PRIMARY KEY (`uid`,`scene`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"militarydrillinfo"=>"CREATE TABLE `militarydrillinfo` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `randomNum` int(11) NOT NULL DEFAULT '0',
  `currentNum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"nuclear"=>"CREATE TABLE `nuclear` (
  `uid` varchar(42) NOT NULL,
  `status` int(8) NOT NULL DEFAULT '0',
  `weekTimes` int(8) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			"openactivity"=>"CREATE TABLE `openactivity` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(2) DEFAULT '0',
  `status` int(2) DEFAULT '0',
  `receivetime` int(10) DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"orangeequipexchange"=>"CREATE TABLE `orangeequipexchange` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `currentEquid` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `fullEquidTimes` int(8) DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"originalreport"=>"CREATE TABLE `originalreport` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(2) DEFAULT '0',
  `create_at` int(10) NOT NULL,
  `backClass` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `report` mediumblob,
  `matrix` blob,
  `queue` blob,
  `params` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"plaftormfirstpay"=>"CREATE TABLE `plaftormfirstpay` (
  `uid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `openid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ownerid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `level` int(11) NOT NULL,
  `billno` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `sendtime` bigint(11) NOT NULL,
  `goodsid` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `price` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `amt` int(11) NOT NULL DEFAULT '0',
  `payamt_coins` int(11) NOT NULL DEFAULT '0',
  `pubacct_payamt_coins` int(11) NOT NULL DEFAULT '0',
  `zoneid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index` (`openid`,`sendtime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"platformfirstreg"=>"CREATE TABLE `platformfirstreg` (
  `platformAddress` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `userUID` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `openid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `pf` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `lang` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `zoneid` int(11) NOT NULL DEFAULT '0',
  `registerTime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`platformAddress`,`userUID`),
  KEY `index_openid` (`openid`) USING BTREE,
  KEY `index_registerTime` (`registerTime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"platformlogin"=>"CREATE TABLE `platformlogin` (
  `platformAddress` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `timeStamp` int(10) NOT NULL,
  `count` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`platformAddress`,`timeStamp`,`count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"platformprofile"=>"CREATE TABLE `platformprofile` (
  `platformAddress` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `userUID` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`platformAddress`),
  KEY `index_userUID` (`userUID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"power"=>"CREATE TABLE `power` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `sel_power` int(10) DEFAULT NULL,
  `powerList` blob,
  `powerSetList` blob,
  `powerRecordList` blob,
  `powerRewardFlag` blob,
  `hidePowerList` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"prisoner"=>"CREATE TABLE `prisoner` (
  `uid` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `start_time` bigint(20) NOT NULL DEFAULT '0',
  `captive_time` bigint(20) NOT NULL DEFAULT '0',
  `record_time` bigint(20) NOT NULL DEFAULT '0',
  `ownerId` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `askhelp_count` int(11) NOT NULL DEFAULT '0',
  `enslave_count` int(11) NOT NULL DEFAULT '0',
  `conquer_count` int(11) NOT NULL DEFAULT '0',
  `save_count` int(11) NOT NULL DEFAULT '0',
  `enslave_end_time` bigint(20) NOT NULL DEFAULT '0',
  `taskid` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `free_use` int(2) NOT NULL DEFAULT '0',
  `taskupdatetime` bigint(20) NOT NULL DEFAULT '0',
  `task1` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `task2` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `task3` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `task4` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `task5` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `exp_add_time` bigint(20) NOT NULL DEFAULT '0',
  `brand_uid` varchar(40) DEFAULT NULL,
  `brand_time` bigint(20) DEFAULT '0',
  `brand_content` varchar(60) DEFAULT NULL,
  `buy_conquer_count` int(11) DEFAULT '0',
  `prison_position` char(10) DEFAULT '0000000000',
  `buy_askhelp_count` int(10) DEFAULT NULL,
  `buy_enslave_count` int(10) DEFAULT NULL,
  `buy_save_count` int(10) DEFAULT NULL,
  `isExp` int(10) DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			"prisonerlog"=>"CREATE TABLE `prisonerlog` (
  `uid` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `slaverId` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `conquer_time` bigint(20) NOT NULL DEFAULT '0',
  `end_time` bigint(20) NOT NULL DEFAULT '0',
  `fight_time` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `Index_slaverId` (`slaverId`) USING BTREE,
  KEY `Index_ownerId` (`ownerId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			"proclaimwar"=>"CREATE TABLE `proclaimwar` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `targetId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(4) NOT NULL,
  `waitTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL DEFAULT '0',
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `ownerId_index` (`ownerId`) USING BTREE,
  KEY `targetId_index` (`targetId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"pvelog"=>"CREATE TABLE `pvelog` (
  `time` int(10) NOT NULL DEFAULT '0',
  `battle` int(10) NOT NULL DEFAULT '0',
  `win` int(10) NOT NULL DEFAULT '0',
  `lose` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`time`,`battle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"pverecord"=>"CREATE TABLE `pverecord` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `level` int(4) NOT NULL,
  `create_at` int(10) NOT NULL,
  `reportUid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"qfilink"=>"CREATE TABLE `qfilink` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `invite` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invited` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `izoneid` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timeStamp` int(10) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_invite` (`invite`) USING BTREE,
  KEY `index_invited` (`invited`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"qfinvite"=>"CREATE TABLE `qfinvite` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `lastUpdate` int(11) DEFAULT NULL,
  `dailyInvite` int(11) DEFAULT NULL,
  `totalInvite` int(11) DEFAULT NULL,
  `dailyRewardList` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rewardList` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"qflink"=>"CREATE TABLE `qflink` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `invite` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invited` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zoneid` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `izoneid` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timeStamp` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `pay` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_invite` (`invite`) USING BTREE,
  KEY `index_invited` (`invited`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"qpay"=>"CREATE TABLE `qpay` (
  `uid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `openid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ownerid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `level` int(11) NOT NULL,
  `billno` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `sendtime` bigint(11) NOT NULL,
  `goodsid` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `price` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `amt` int(11) NOT NULL DEFAULT '0',
  `payamt_coins` int(11) NOT NULL DEFAULT '0',
  `pubacct_payamt_coins` int(11) NOT NULL DEFAULT '0',
  `zoneid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index` (`ownerid`,`sendtime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"qprovideaward"=>"CREATE TABLE `qprovideaward` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `opendid` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `questid` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zoneid` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  `completeTime` int(11) DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_openid` (`opendid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"quest"=>"CREATE TABLE `quest` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(2) DEFAULT '0',
  `nums` int(10) DEFAULT '0',
  `status` int(2) DEFAULT '0',
  `del` int(2) DEFAULT '0',
  `target` int(4) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`),
  KEY `Index_ownereId_target_del` (`target`,`ownerId`,`del`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"questrecord"=>"CREATE TABLE `questrecord` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `questList` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"rankinfo"=>"CREATE TABLE `rankinfo` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `defaultForce` int(10) NOT NULL DEFAULT '0',
  `fightPower` int(10) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"recruit"=>"CREATE TABLE `recruit` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `point1` int(11) NOT NULL DEFAULT '0',
  `point2` int(11) NOT NULL DEFAULT '0',
  `point3` int(11) NOT NULL DEFAULT '0',
  `point4` int(11) NOT NULL DEFAULT '0',
  `point5` int(11) NOT NULL,
  `planId` int(11) NOT NULL,
  `colortab` int(11) NOT NULL,
  `special` int(11) NOT NULL,
  `reobj1` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reobj2` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reobj3` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `currentObj` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"regioninfo"=>"CREATE TABLE `regioninfo` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"register"=>"CREATE TABLE `register` (
  `platformAddress` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `timeStamp` int(10) NOT NULL,
  `user` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`platformAddress`,`timeStamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"registerdata"=>"CREATE TABLE `registerdata` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `timeStamp` int(10) NOT NULL,
  `user` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_user` (`user`),
  KEY `index_timeStamp` (`timeStamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
	"reward"=>"CREATE TABLE `reward` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contents` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` int(10) NOT NULL DEFAULT '0',
  `createTime` int(10) NOT NULL,
  `startTime` int(10) NOT NULL,
  `endTime` int(10) NOT NULL,
  `enabled` int(1) NOT NULL DEFAULT '0',
  `typeParam` blob NOT NULL,
  `goods` blob,
  `exp` int(10) DEFAULT NULL,
  `exp1` int(10) DEFAULT NULL,
  `money` int(10) DEFAULT NULL,
  `general` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `honor` int(10) DEFAULT NULL,
  `pvpHonor` int(10) DEFAULT NULL,
  `gold` int(10) DEFAULT NULL,
  `gift` int(10) DEFAULT NULL,
  `soul1` int(10) DEFAULT NULL,
  `soul2` int(10) DEFAULT NULL,
  `soul3` int(10) DEFAULT NULL,
  `soul4` int(10) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"rewardrecord"=>"CREATE TABLE `rewardrecord` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `rewardList` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"science"=>"CREATE TABLE `science` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `itemId` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(2) NOT NULL,
  `level` int(6) NOT NULL DEFAULT '0',
  `status` int(2) NOT NULL DEFAULT '0',
  `upgradeTime` int(11) DEFAULT '0',
  `isDefault` int(2) NOT NULL DEFAULT '0',
  `money` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"serverannounce"=>"CREATE TABLE `serverannounce` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `startTime` int(10) NOT NULL DEFAULT '0',
  `endTime` int(10) NOT NULL DEFAULT '0',
  `spanTime` int(10) NOT NULL DEFAULT '0',
  `showPos` int(10) NOT NULL DEFAULT '0',
  `contents` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"servercallbacklist"=>"CREATE TABLE `servercallbacklist` (
  `serverId` int(11) NOT NULL,
  `type` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`type`,`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"serverlist"=>"CREATE TABLE `serverlist` (
  `server_id` int(11) NOT NULL COMMENT '服务器ID',
  `server_name` char(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '服务器名称',
  `server_unique_key` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '服务器唯一标识',
  `server_ip` char(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '服务器IP',
  `game_url` char(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '游戏URL地址',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '开服时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `is_new` tinyint(1) NOT NULL DEFAULT '0' COMMENT '新服标识',
  `weight` int(11) NOT NULL DEFAULT '100' COMMENT '权重',
  PRIMARY KEY (`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"serverlogined"=>"CREATE TABLE `serverlogined` (
  `platform_uid` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `last_login_server` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '最后登录服务器ID',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `login_server_list` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '最近登录服务器列表，保留10个，如 “1|3|10|30”',
  PRIMARY KEY (`platform_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"servermail"=>"CREATE TABLE `servermail` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `startTime` int(10) NOT NULL,
  `endTime` int(10) NOT NULL,
  `registerTime` int(10) DEFAULT NULL,
  `levelMin` int(10) NOT NULL,
  `levelMax` int(10) NOT NULL,
  `league` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contents` blob,
  `rewardId` blob,
  PRIMARY KEY (`uid`),
  KEY `index_startTime` (`startTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"servermaillog"=>"CREATE TABLE `servermaillog` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `sendTime` int(10) NOT NULL,
  `level` int(10) NOT NULL,
  `mail` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"serverreset"=>"CREATE TABLE `serverreset` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `allianceReward` int(11) DEFAULT NULL,
  `releaseTime` int(11) DEFAULT NULL,
  `wheelWeekAwardTime` int(11) DEFAULT NULL,
  `wheelWeekAward` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"serverusermail"=>"CREATE TABLE `serverusermail` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `toUser` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `sendBy` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `sendTime` int(10) NOT NULL,
  `title` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contents` blob,
  `rewardId` varchar(4000) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_sendTime` (`sendTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"serverusermaillog"=>"CREATE TABLE `serverusermaillog` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `sendBy` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `sendServer` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `sendTime` int(10) NOT NULL,
  `sendUid` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sendName` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contents` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reward` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_sendTime` (`sendTime`) USING BTREE,
  KEY `index_sendBy` (`sendBy`) USING BTREE,
  KEY `index_sendServer` (`sendServer`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"serviceconfig"=>"CREATE TABLE `serviceconfig` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `lordDouble` tinyint(1) NOT NULL DEFAULT '0',
  `moneyDouble` tinyint(1) NOT NULL DEFAULT '0',
  `mineralDouble` tinyint(1) NOT NULL DEFAULT '0',
  `oilDouble` tinyint(1) NOT NULL DEFAULT '0',
  `foodDouble` tinyint(1) NOT NULL DEFAULT '0',
  `soldiersDouble` tinyint(1) NOT NULL DEFAULT '0',
  `generalExpDouble` tinyint(1) NOT NULL DEFAULT '0',
  `generalFeatsDouble` tinyint(1) NOT NULL DEFAULT '0',
  `rewardGoods` tinyint(1) NOT NULL,
  `arena` tinyint(1) NOT NULL,
  `startTime` int(10) NOT NULL DEFAULT '0',
  `endTime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"sharereward"=>"CREATE TABLE `sharereward` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `shareId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `shareParam` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(10) NOT NULL,
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"shopactivitynums"=>"CREATE TABLE `shopactivitynums` (
  `uid` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `itemId` int(11) NOT NULL DEFAULT '0',
  `nums` int(11) NOT NULL DEFAULT '0',
  `limit` int(11) NOT NULL DEFAULT '0',
  `userLimit` int(11) NOT NULL DEFAULT '0',
  `price` int(11) NOT NULL,
  `priceType` int(11) NOT NULL DEFAULT '0',
  `startTime` int(11) NOT NULL DEFAULT '0',
  `endTime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `index_startTime` (`startTime`),
  KEY `index_endTime` (`endTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			"sign"=>"CREATE TABLE `sign` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `times` int(8) DEFAULT NULL,
  `signList` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"silvermine"=>"CREATE TABLE `silvermine` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `level` int(6) NOT NULL DEFAULT '1',
  `curExp` int(11) NOT NULL DEFAULT '0',
  `exploreCount` tinyint(4) NOT NULL DEFAULT '0',
  `helpRewardCount` int(4) NOT NULL DEFAULT '0',
  `buyTimes` tinyint(4) NOT NULL DEFAULT '0',
  `rewardLevel` int(11) DEFAULT '0',
  `upgradeTime` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"silverminerecord"=>"CREATE TABLE `silverminerecord` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `friendUid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`uid`,`friendUid`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"stargeneral"=>"CREATE TABLE `stargeneral` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `exchList` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"team"=>"CREATE TABLE `team` (
  `uid` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `itemId` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` int(2) DEFAULT '0',
  `battleId` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `create_at` int(11) NOT NULL,
  `fight_time` int(11) DEFAULT NULL,
  `fightPower` int(11) DEFAULT '0',
  `alliance` varchar(100) DEFAULT '',
  `forcesLimit` int(11) DEFAULT '0',
  `autoBattle` enum('Y','N') DEFAULT 'N',
  `autoSweep` enum('N','Y') DEFAULT 'N',
  `nums` int(4) NOT NULL DEFAULT '1',
  `status` int(2) NOT NULL DEFAULT '0',
  `leaderUid` varchar(40) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `round` tinyint(4) DEFAULT '0',
  `vs` blob,
  PRIMARY KEY (`uid`),
  KEY `index_itemId` (`itemId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			"teambattle"=>"CREATE TABLE `teambattle` (
  `uid` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `itemId` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `teamUid` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `x` int(4) NOT NULL,
  `y` int(4) NOT NULL,
  `forces` int(10) DEFAULT '0',
  `lossForces` int(11) NOT NULL,
  `generalList` blob NOT NULL,
  `npc` int(2) DEFAULT '0',
  `join_in` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` int(4) NOT NULL,
  `fightPower` int(10) NOT NULL,
  `role` int(2) NOT NULL DEFAULT '1',
  `armsId` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `face` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `dead` int(2) DEFAULT '0',
  `moveDistance` int(11) NOT NULL DEFAULT '0',
  `maxForces` int(11) NOT NULL DEFAULT '0',
  `scene` varchar(255) NOT NULL,
  `attrGrow` varchar(11) DEFAULT NULL,
  `forcesRatio` blob,
  `ai` int(11) DEFAULT '2000',
  `takeForcesNums` int(11) DEFAULT '0',
  `useGoods` tinyblob,
  `cmd` tinyint(4) DEFAULT NULL,
  `fixedPosition` varchar(10) DEFAULT NULL,
  `league` varchar(40) DEFAULT NULL,
  `killCount` int(11) DEFAULT '0',
  `killForces` int(11) DEFAULT '0',
  `getPoints` int(11) DEFAULT '0',
  `effectList` blob,
  `continuousKill` int(11) DEFAULT '0',
  `leagueRole` tinyint(4) DEFAULT '0',
  `lastPath` blob,
  PRIMARY KEY (`uid`),
  KEY `index_ownerId_teamUid` (`ownerId`,`teamUid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			"teambattlelog"=>"CREATE TABLE `teambattlelog` (
  `team` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `npcId` int(10) NOT NULL DEFAULT '0',
  `battleId` int(10) NOT NULL DEFAULT '0',
  `playerNum` int(10) NOT NULL DEFAULT '0',
  `winside` int(10) NOT NULL DEFAULT '0',
  `start` int(10) NOT NULL DEFAULT '0',
  `end` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`team`,`npcId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"teambattleround"=>"CREATE TABLE `teambattleround` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `round` int(10) DEFAULT '0',
  `roundList` blob,
  `clearFlag` enum('N','Y') COLLATE utf8_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"teamfightreport"=>"CREATE TABLE `teamfightreport` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `create_at` int(10) NOT NULL,
  `report` mediumblob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"teammember"=>"CREATE TABLE `teammember` (
  `uid` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `teamUid` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `leader` int(2) DEFAULT '0',
  `join_in` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `level` int(4) NOT NULL,
  `league` varchar(255) DEFAULT NULL,
  `pic` varchar(10) DEFAULT NULL,
  `fightPower` int(11) DEFAULT '0',
  `moveDistance` int(11) NOT NULL DEFAULT '0',
  `takeForcesNums` int(11) NOT NULL DEFAULT '0',
  `maxForces` int(11) NOT NULL DEFAULT '0',
  `roundReward` blob,
  `generalList` blob,
  `scene` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_ownerId_teamUid` (`ownerId`,`teamUid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			"traingrid"=>"CREATE TABLE `traingrid` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `gridList` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"treasure"=>"CREATE TABLE `treasure` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `treasureTimes` int(10) NOT NULL DEFAULT '1',
  `freeTimes` int(10) NOT NULL DEFAULT '0',
  `unlockPos` int(10) NOT NULL DEFAULT '0',
  `lastUpdate` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"tutorial"=>"CREATE TABLE `tutorial` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `items` mediumblob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"tutorialstep"=>"CREATE TABLE `tutorialstep` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `tutorial` bigint(20) NOT NULL,
  `time` int(10) DEFAULT NULL,
  PRIMARY KEY (`uid`,`tutorial`),
  KEY `index_time` (`time`) USING BTREE,
  KEY `index_tutorial` (`tutorial`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"tutorialstepstat"=>"CREATE TABLE `tutorialstepstat` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `tutorial` int(10) NOT NULL,
  `time` int(10) DEFAULT NULL,
  PRIMARY KEY (`uid`,`tutorial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"unit"=>"CREATE TABLE `unit` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `endTime` int(10) DEFAULT '0',
  `studyArms` int(10) DEFAULT '0',
  `armsList` blob,
  `armyFront` int(10) DEFAULT '0',
  `armyMiddle` int(10) DEFAULT '0',
  `armyBack` int(10) DEFAULT '0',
  `navyFront` int(10) DEFAULT '0',
  `navyMiddle` int(10) DEFAULT '0',
  `navyBack` int(10) DEFAULT '0',
  `airFront` int(10) DEFAULT '0',
  `airMiddle` int(10) DEFAULT '0',
  `airBack` int(10) DEFAULT '0',
  `armySwapTime` int(10) DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"useraction"=>"CREATE TABLE `useraction` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  `action` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `type` int(40) NOT NULL,
  `code` int(10) NOT NULL DEFAULT '0',
  `params` blob NOT NULL,
  `data` blob,
  PRIMARY KEY (`uid`),
  KEY `index_user` (`user`,`action`,`type`,`code`,`time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"useractivity"=>"CREATE TABLE `useractivity` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `activityId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `obtainAward` blob,
  `dailyCount` int(11) NOT NULL DEFAULT '0',
  `buyCount` int(11) DEFAULT '0',
  `flushTime` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_ownerId` (`ownerId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"useractivityapply"=>"CREATE TABLE `useractivityapply` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `activityId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `applyTime` int(11) NOT NULL,
  `fightPower` int(11) DEFAULT '0',
  `maxForces` int(11) DEFAULT '0',
  `winCount` int(11) DEFAULT NULL,
  `recoverCount` int(11) DEFAULT '0',
  `reward` blob,
  `acceptRewardFlag` int(11) DEFAULT NULL,
  `addForces` int(11) DEFAULT '0',
  `returnForcesFlag` int(11) DEFAULT NULL,
  `healthDegree` int(11) DEFAULT NULL,
  `allianceActId` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `ownerId_actId_index` (`ownerId`,`activityId`) USING BTREE,
  KEY `applyTime_index` (`applyTime`) USING BTREE,
  KEY `allianceActId_index` (`allianceActId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"useralliancewelfare"=>"CREATE TABLE `useralliancewelfare` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `welfareCount` int(11) DEFAULT NULL,
  `isAcceptTodayReward` tinyint(4) DEFAULT NULL,
  `welfareTime` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"userboss"=>"CREATE TABLE `userboss` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `fightCount` int(11) NOT NULL,
  `fightCountRecoverTime` int(11) NOT NULL,
  `bossInfo` blob,
  `buyTimes` int(11) DEFAULT '0',
  `buyTimesDaily` int(11) DEFAULT '0',
  `ratio1` int(11) DEFAULT NULL,
  `count1` int(11) DEFAULT NULL,
  `ratio2` int(11) DEFAULT NULL,
  `count2` int(11) DEFAULT NULL,
  `ratio3` int(11) DEFAULT NULL,
  `count3` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `userboss_index` (`ratio1`,`ratio2`,`ratio3`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"userfamousgeneral"=>"CREATE TABLE `userfamousgeneral` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `generals` blob NOT NULL,
  `genTimes` blob NOT NULL,
  `fixTime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"useronethousand"=>"CREATE TABLE `useronethousand` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `currRecord` int(11) NOT NULL,
  `hisMaxRecord` int(11) DEFAULT NULL,
  `hisMaxRecordTime` int(11) DEFAULT NULL,
  `challengeTimes` int(11) NOT NULL DEFAULT '0',
  `isAcceptRewardTimes` tinyint(4) DEFAULT NULL,
  `buyTimes` int(11) NOT NULL DEFAULT '0',
  `failTimes` int(11) NOT NULL DEFAULT '0',
  `auto` enum('N','Y') COLLATE utf8_unicode_ci DEFAULT 'N',
  `roundReward` blob,
  `emailReward` blob,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"useropenactivity"=>"CREATE TABLE `useropenactivity` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `generals` blob NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"userprofile"=>"CREATE TABLE `userprofile` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `platformAddress` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT '',
  `level` int(10) NOT NULL DEFAULT '0',
  `vip` int(10) NOT NULL DEFAULT '0',
  `vip_finish_time` int(11) NOT NULL DEFAULT '0',
  `accept_vipgift_status` int(10) NOT NULL DEFAULT '0',
  `yellow_vip_status` tinyint(4) NOT NULL DEFAULT '0',
  `yellow_vip_level` int(10) NOT NULL DEFAULT '0',
  `yellowvip_firstgift_status` tinyint(4) NOT NULL DEFAULT '0',
  `accept_yvipgift_status` tinyint(4) NOT NULL DEFAULT '0',
  `first_pay_status` tinyint(4) NOT NULL DEFAULT '0',
  `pic` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `picTimes` int(10) NOT NULL DEFAULT '0',
  `gender` int(10) NOT NULL DEFAULT '0',
  `user_gold` int(10) NOT NULL DEFAULT '0',
  `system_gold` int(10) NOT NULL DEFAULT '0',
  `active_point` int(10) NOT NULL DEFAULT '0',
  `activeReward` blob NOT NULL,
  `x` int(10) NOT NULL DEFAULT '0',
  `y` int(10) NOT NULL DEFAULT '0',
  `league` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `country` int(10) NOT NULL DEFAULT '0',
  `registerTime` int(10) NOT NULL DEFAULT '0',
  `date` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `forcibly_forces` int(10) NOT NULL DEFAULT '0',
  `forcibly_resource` int(10) NOT NULL DEFAULT '0',
  `occupyCityTimes` int(10) NOT NULL DEFAULT '0',
  `occupyResourceTimes` int(10) NOT NULL DEFAULT '0',
  `plunderTimes` int(10) NOT NULL DEFAULT '0',
  `destroyTimes` int(10) NOT NULL DEFAULT '0',
  `ownResource` int(10) NOT NULL DEFAULT '0',
  `maxResource` int(10) NOT NULL DEFAULT '0',
  `dailyFlushTimes` int(10) NOT NULL DEFAULT '0',
  `dailyCompleteTimes` int(10) NOT NULL DEFAULT '0',
  `dailyFlushTime` int(10) NOT NULL DEFAULT '0',
  `pveTimes` int(10) NOT NULL DEFAULT '0',
  `extraPveTimes` int(10) NOT NULL DEFAULT '0',
  `pveRefreshTime` int(10) NOT NULL DEFAULT '0',
  `buyPveTimes` int(10) NOT NULL DEFAULT '0',
  `onlineGift` int(10) DEFAULT NULL,
  `giftEndTime` int(10) NOT NULL DEFAULT '0',
  `buttonIndex` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `speakingForbid` int(10) NOT NULL DEFAULT '0',
  `seize` int(10) NOT NULL DEFAULT '0',
  `tabIndex` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `onLoadKey` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `lastLoadTime` int(10) NOT NULL DEFAULT '0',
  `playerOnlineTime` int(10) NOT NULL DEFAULT '0',
  `island` int(10) NOT NULL DEFAULT '0',
  `gmFlag` int(10) NOT NULL DEFAULT '0',
  `gmShow` int(10) NOT NULL DEFAULT '0',
  `goldOffered` int(10) NOT NULL DEFAULT '0',
  `onlineVersion` int(10) NOT NULL DEFAULT '0',
  `arenaTimes` int(10) NOT NULL DEFAULT '0',
  `buyArenaTimes` int(10) NOT NULL DEFAULT '0',
  `skillPoint` int(10) NOT NULL DEFAULT '0',
  `fixTime` int(10) NOT NULL DEFAULT '0',
  `teamTimes` blob NOT NULL,
  `buyTeamTimes` blob NOT NULL,
  `allianceFlushTimes` int(10) NOT NULL,
  `serverMailTime` int(10) NOT NULL DEFAULT '0',
  `moveDistanceDelta` int(11) NOT NULL DEFAULT '0',
  `leagueFlushTimes` int(11) NOT NULL DEFAULT '0',
  `test` int(11) NOT NULL,
  `rewardFlag` int(11) DEFAULT NULL,
  `registerBefore` int(11) DEFAULT NULL,
  `inviteFlag` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `contractId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `resetPveTimes` blob NOT NULL,
  `source` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `index_name` (`name`) USING BTREE,
  KEY `index_country` (`country`) USING BTREE,
  KEY `index_registered` (`registerBefore`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"usershoprecord"=>"CREATE TABLE `usershoprecord` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `goodsList` blob,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"userwheel"=>"CREATE TABLE `userwheel` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `rndAwardList` blob,
  `luckyValue` int(11) NOT NULL,
  `status` blob,
  `firstFlag` blob,
  `obtainAward` blob,
  `wheelCount` int(11) DEFAULT '0',
  `moneyCount` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"userworld"=>"CREATE TABLE `userworld` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `fightCount` int(11) NOT NULL DEFAULT '0',
  `buyFightCount` int(11) DEFAULT '0',
  `failCount` int(11) NOT NULL DEFAULT '0',
  `dailyFailCount` int(11) NOT NULL DEFAULT '0',
  `healthDegree` int(11) NOT NULL,
  `defenseForces` int(11) NOT NULL DEFAULT '0',
  `sign` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hdRefreshTime` int(11) NOT NULL DEFAULT '0',
  `ccRecoverTime` blob,
  `ccRecoverFlag` int(11) NOT NULL DEFAULT '0',
  `hisMaxCCLevel` int(11) NOT NULL DEFAULT '0',
  `whiteBanner` blob,
  `firstBanish` tinyint(4) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"warexercise"=>"CREATE TABLE `warexercise` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `frontExp` int(8) NOT NULL DEFAULT '0',
  `middleExp` int(8) NOT NULL DEFAULT '0',
  `backExp` int(8) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"washlog"=>"CREATE TABLE `washlog` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `timeStamp` int(10) NOT NULL,
  `times` int(10) DEFAULT NULL,
  `goldTimes` int(10) DEFAULT NULL,
  `money` int(10) DEFAULT NULL,
  `gold` int(10) DEFAULT NULL,
  PRIMARY KEY (`uid`,`timeStamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"world"=>"CREATE TABLE `world` (
  `x` int(4) NOT NULL,
  `y` int(4) NOT NULL,
  `type` int(2) NOT NULL,
  `country` int(10) NOT NULL DEFAULT '0',
  `occupant` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `relicId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `npcRemainForces` int(11) DEFAULT NULL,
  `occupantStartTime` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`x`,`y`),
  KEY `occupant_index` (`occupant`) USING BTREE,
  KEY `type_index` (`type`,`x`,`y`) USING BTREE,
  KEY `country_index` (`country`) USING BTREE,
  KEY `relicId_index` (`relicId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"worldfight"=>"CREATE TABLE `worldfight` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ownerId` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(4) NOT NULL,
  `takeForces` int(11) NOT NULL,
  `remainForces` int(11) NOT NULL,
  `targetUid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `targetX` int(11) NOT NULL,
  `targetY` int(11) NOT NULL,
  `startTime` int(11) NOT NULL,
  `waitTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `reportId` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reinforceEndTime` int(11) DEFAULT NULL,
  `allianceHostilityFlag` tinyint(4) DEFAULT '0',
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `owner_index` (`ownerId`) USING BTREE,
  KEY `target_index` (`targetUid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			"yellowdiamond"=>"CREATE TABLE `yellowdiamond` (
  `uid` varchar(42) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `yellowVipPay` int(10) NOT NULL DEFAULT '0',
  `yellowVipPayReward` int(10) NOT NULL DEFAULT '0',
  `yellowVipPayRewardNum` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
			"yvlevelawardstatus"=>"CREATE TABLE `yvlevelawardstatus` (
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `level` int(10) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`,`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			);
}
?>