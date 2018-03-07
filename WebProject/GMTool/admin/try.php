<?php
$mysqli = new mysqli('URLIP', 'root', 'admin123', 'cokdb1');
	$sql = "select distinct(uid) from server_update";
	$result=$mysqli->query($sql);
	$data = array();
	if ($result) {
         if($result->num_rows>0){                                               //判断结果集中行的数目是否大于0
                  while($row =$result->fetch_array() ){                        //循环输出结果集中的记录
	    			$data[] = $row['uid'];
                  }
         }
	}
$result->free();
$mysqli->close();
	echo json_encode($data);
	return;	
