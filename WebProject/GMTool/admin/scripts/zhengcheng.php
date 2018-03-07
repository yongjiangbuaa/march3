<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/etc/db.inc_online.php';
include ADMIN_ROOT.'/servers.php';
//include ADMIN_ROOT.'/servers_online.php';
ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
global $servers;
$page = new BasePage();
$errorSql = "SELECT uid, ability from user_general where ability is not null";
$file = 'repairqueue.log';
$count = 0;
$s = 1;
$b = 700;
if(isset($argv[1])){ 
 				  $s=$argv[1];
 				}
 				if(isset($argv[2])){ 
 				  $b=$argv[2];
 				}
foreach ($servers as $curServer=>$serverInfo){

 $count++;
 if($count < $s ){ 
continue;
 }
 if($count > $b){ 
break;
 }


 			try{
 				
                                $logStr = "reparing ".$curServer."\n";
                                echo $logStr;
                 //               file_put_contents($file, $logStr, FILE_APPEND);
                                $sqlData = $page->executeServer($curServer,$errorSql,3,true);
                                foreach ($sqlData['ret']['data'] as $curRow) {
                                        $uid = $curRow['uid'];
                                        $ability = $curRow['ability'];
                                        $de_json = json_decode($ability, true);
                                        $count_json = count($de_json);

                                        for ($i = 0; $i < $count_json; $i++){ 
                                        	foreach ($de_json[$i] as $key => $value) {
                                        		if($key == "id"){ 
                                        			continue;
                                        		}
                                        		
                                        		if($value < 0 || $value > 20){ 
                                        			$es= $uid.":".$ability."\n";
                                        			echo $es;
                                        			 file_put_contents($file, $es, FILE_APPEND);
                                        			break;
                                        		}
                                        	}
                                        }

                                        //$userLog = $uuid."|".$curRow['ownerId']."|".$curRow['qid']."|".$curRow['type']."|".$curRow['itemId']."|".$curRow['startTime']."|".$curRow['endTime']."|".$curRow['updateTime']."|".$curRow['isHelped']."\n";
                                        //$repairSql = "UPDATE queue SET updateTime = 9223372036854775807 WHERE uuid ='".$uuid."'";
                                        //$page->executeServer($curServer, $repairSql,1,true);
                                        //echo $repairSql;
                                        //file_put_contents($file, $userLog, FILE_APPEND);
                                }
                        }catch(Excepton $e){
                                echo $e;
                                break;
                        }
                       
                }

?>

