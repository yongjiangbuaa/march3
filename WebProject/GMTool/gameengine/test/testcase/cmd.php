<?php
require dirname(__FILE__).'/init.php';
//echo 9999;
p($_SERVER);
$type = '';
$param = '';
if(isset($_POST['type'])){
	$type = $_POST['type'];
}else{
	die('Parameter "type" is required.');
}
if(isset($_POST['param'])){
	$param = $_POST['param'];
}else{
	die('Parameter "param" is required.');
}
if(isset($_POST['protocol'])){
	$protocol = $_POST['protocol'];
}

$manager = new TestCaseManager();

switch ($type){
	case 'listTestCase':{
		//get all testcases
		$basepath = dirname(__FILE__).'/'.strtolower($param).'/';
		if($param=='APITest' && isset($protocol) && !empty($protocol)){
			$basepath .= $protocol.'/';
		}
		$manager->setBasepath($basepath);
		$classes = $manager->findAll();
		$testcase_table='';
		$i=0;
		$listed = array();
		$testcase_table .= '<table width="350px"><tbody>';
		if(!empty($classes) && is_array($classes)){
			foreach ($classes as $class){
				if($i%3==0){
					//$testcase_table .= '<tr>';
				}
				if(!in_array($class['path'], $listed)){
					$testcase_table .= '<tr><td colspan=2><input onclick="checkSubBox(\''.$class['path'].'\');" type="checkbox" name="'.$class['path'].'" value="">'.$class['path'].'</input></td></tr>';
				}
				
				$testcase_table .= '<tr><td width="20px"></td><td><input onclick="" type="checkbox" name="'.$class['path'].'" value="'.$class['path'].'.'.$class['classname'].'">'.$class['classname'].'</input></td></tr>';
				$listed[] = $class['path'];
				if($i%3==2){
					//$testcase_table .= '</tr>';
				}
				$i++;
			}
		}
		$testcase_table .= '</tbody></table>';
		echo $testcase_table;
		break;
	}
	case 'unitTest': case 'APITest': case 'deploy':{
		$basepath = dirname(__FILE__).'/'.strtolower($type).'/';
		if($type=='APITest' && isset($protocol) && !empty($protocol)){
			$basepath .= $protocol.'/';
		}
		$manager->setBasepath($basepath);
		$manager->setTestType(strtolower($type));
		if(isset($protocol) && !empty($protocol)){
			$manager->setProtocol($protocol);
		}
		$param = trim($param, ',');
		if(empty($param)){
			echo 'Please select at least one testcase.';
			break;
		}
		try {
			$msg = $manager->run($param);
		}
		catch (Exception $e){
			$msg = $e->getMessage()."\n".$e->getTraceAsString();
		}
		if(!empty($msg)){
			echo $msg;
		}else{
			$report = $manager->report();
			echo $report;
		}
		break;
	}
	
	default:
		echo 'Parameter "type" is not correct.';
		break;
}



?>