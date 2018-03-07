<?php
!defined('IN_ADMIN') && exit('Access Denied');
$sensitiveArray=array('INTO','OUTFILE','INFILE','GRANT','ALTER','UPDATE','INSERT','DELETE','DROP','SHOW','ADMIN','shell','\!','ls');
if (isset($_REQUEST['table'])) {
	try {
		if($_REQUEST['type'] == 'read')
		{
			$page = new BasePage();
			$param = $_REQUEST;
			
			foreach ($param as $pa){
				$tempArray=explode(' ', $pa);
				foreach ($tempArray as $word){
					if (in_array($word, $sensitiveArray)){
						$html="请重新输入sql语句,sql中不能出现$word!";
						echo $html;
						exit();
					}
				}
				if (stripos($pa,';')!==false){
					$html=";只能出现在sql语句的末尾!";
					echo $html;
					exit();
				}
			}
			
			$param["type"] = 6;
			$param = array(
					"changes"=>null,
					"params"=>$param,
				);
			$sendParam = array('info'=>'','data'=>$param,'mainDB'=>1);
			$result = $page->call( 'gm/gm/Mysql', $sendParam );
			if ( isset( $result['error'] ) ){
				echo $result['error'];
				exit();
			}
			else {
				$struct = $result['ret']['struct']; 
 				$html = "<div style='float:left;width:100%;height:500px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
				$html .= "<tr><th>index</th>";
				$tableAttr = Array("name","scale","type","max_length","not_null","primary_key","auto_increment","binary","unsigned","zerofill","has_default","default_value");
				for($i=0;$i<count($tableAttr);$i++){
					$html .= "<th>" . $tableAttr[$i] . "</th>";
				}
				$html .= "</tr>";
				foreach ($struct as $entryIndex=>$entry){
					$html .= "<div class='div" . $entryIndex . "'>";
					$html .= "<tr class='color" . flag . "' >";
					$html .= "<td><input class='input-small' type='text' value='" . $entryIndex . "' size = 5 disabled></td>";
					//tableIndex.push($entryIndex);
					$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_name' value='" . $entry['name'] . "' size = 5></td>";
					$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_scale' value='" . $entry['scale'] . "' size = 5 disabled></td>";
					$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_type' value='" . $entry['type'] . "' size = 10 disabled></td>";
					$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_max_length' value='" . $entry['max_length'] . "' size = 5></td>";
					if($entry['has_default'])
						$html .= "<td><select class='input-small' id='". $entryIndex . "_not_null'><Option value=0>true</Option><Option value=1>false</Option></select></td>";
					else
						$html .= "<td><select class='input-small' id='". $entryIndex . "_not_null'><Option value=0>true</Option><Option value=1 selected>false</Option></select></td>";
					//$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_not_null' value='" . $entry['not_null'] . "' size = 5></td>";
					$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_primary_key' value='" . $entry['primary_key'] . "' size = 5></td>";
					$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_auto_increment' value='" . $entry['auto_increment'] . "' size = 5 disabled></td>";
					$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_binary' value='" . $entry['binary'] . "' size = 5 disabled></td>";
					$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_unsigned' value='" . $entry['unsigned'] . "' size = 5 disabled></td>";
					$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_zerofill' value='" . $entry['zerofill'] . "' size = 5 disabled></td>";
					//$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_has_default' value='" . $entry['has_default'] . "' size = 5></td>";
					if($entry['has_default'])
						$html .= "<td><select class='input-small' id='". $entryIndex . "_has_default'><Option value=0>true</Option><Option value=1>false</Option></select></td>";
					else
						$html .= "<td><select class='input-small' id='". $entryIndex . "_has_default'><Option value=0>true</Option><Option value=1 selected>false</Option></select></td>";
					$html .= "<td><input class='input-small' type='text' id='". $entryIndex . "_default_value' value='" . $entry['default_value'] . "' size = 5></td>";
					$html .= "</tr></div>";
					//$("#". $entryIndex . "_has_default").val(1).attr("selected", 1);
				}
				$html .= "</table></div><br/>";
				echo $html;
				exit();
			}
		}
		else if($_REQUEST['type'] == 'modify')
		{
			
		}
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
		exit();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>