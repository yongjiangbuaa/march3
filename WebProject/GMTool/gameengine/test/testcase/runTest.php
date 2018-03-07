<?php
error_reporting(0);
require dirname(__FILE__).'/init.php';

if(!isset($argv[1])){
	displayErr("Invalid arg.\n");
}
if($argv[1]=='-h' || $argv[1]=='-H' || $argv[1]=='--H' || $argv[1]=='--h' || $argv[1]=='-help' || $argv[1]=='--help'){
	echo getHelpMsg();
	exit();
}
$argCount = count($argv);
$type = 'unittest';
$protocol = "rest";
$caseStr = '';

for($i=0;$i<$argCount;$i++){
	if($argv[$i]=='-p' || $argv[$i]=='-protocol'){
		if(isset($argv[$i+1]) && in_array(strtolower($argv[$i+1]), array('rest', 'jsonrpc'))){
			$protocol=strtolower($argv[$i+1]);
		}else{
			displayErr("Please specify a protocol after '-p' parameter\n-p [rest|jsonrpc]\n", FALSE);
		}
	}
	if($argv[$i]=='-t' || $argv[$i]=='-type'){
		if(isset($argv[$i+1]) && in_array(strtolower($argv[$i+1]), array('unittest', 'apitest', 'deploy'))){
			$type=strtolower($argv[$i+1]);
		}else{
			displayErr("Please specify a test type after '-t' parameter\n-t [unittest|apitest|deploy]\n", FALSE);
		}
	}
	if($argv[$i]=='-host'){
		if(isset($argv[$i+1])){
			$host=$argv[$i+1];
			$_SERVER['HTTP_HOST'] = $host;
		}else{
			displayErr("Please specify the host name or IP address after '-host' parameter\n-host [xingcloud]\n", FALSE);
		}
	}
	if($i==$argCount-1){
		$caseStr = $argv[$i];
	}
}

if(empty($caseStr)){
	displayErr("Invalid arg.\n");
}

if($type=='apitest' && !isset($_SERVER['HTTP_HOST'])){
	displayErr("Can't find the HTTP HOST. Please specify the host name or IP address using -host option\n");
}

$manager = new TestCaseManager();
$basepath = dirname(__FILE__).'/'.$type.'/';
if($type=='apitest' && isset($protocol) && !empty($protocol)){
	$basepath .= $protocol.'/';
}
$manager->setBasepath($basepath);
$manager->setTestType($type);
$manager->setProtocol($protocol);
$msg = $manager->run($caseStr);
if(!empty($msg)){
	displayErr($msg);
}else{
	$report = $manager->report();
	echo $report;
	exit();
}


function displayErr($msg, $echoHelp=TRUE){
	echo $msg;
	if($echoHelp){
		echo getHelpMsg();
	}
	die();
}


function getHelpMsg(){
	$str = "<Usage>\n";
	$str .= "php runTest.php [-options] testcase\n";
	$str .= "where options include:\n";
	$str .= "    -t, -type:[unittest|apitest|deploy]   choose the test type, the default value is unittest. the apitest will test the services/actions through curl http request\n";
	$str .= "    -p, -protocol:[rest|jsonrpc]   choose an api protocol the test use, the default value is 'rest'\n";
	$str .= "    -host (optional):    specify the host name or IP address of your test server\n";
	$str .= "Usage Sample:\n";
	$str .= "Run one testcase:\n";
	$str .= "    php runTest.php -t unittest user.UserServiceTestCase\n";
	$str .= "Run several testcases:\n";
	$str .= "    php runTest.php -t apitest -p jsonrpc user.UserServiceTestCase,tutorial.TutorialServiceTestCase\n";
	$str .= "Run all testcases:\n";
	$str .= "    php runTest.php all\n";
	return $str;
}

?>