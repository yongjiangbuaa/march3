<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);
error_reporting(E_ERROR);

if($_REQUEST['subact'] == 'upload' && !$_FILES['XmlFile']['error']){
// 	$local_allinone_filepath =  '/usr/local/cok/SFS2X/resource/GMstatistics/'. basename($_FILES['XmlFile']['name']);
// 	if (file_exists ( $local_allinone_filepath )) {
// 		unlink($local_allinone_filepath);
// 	}
// 	move_uploaded_file($_FILES['XmlFile']['tmp_name'], $local_allinone_filepath);
// 	echo $_FILES['XmlFile']['tmp_name'].','.basename($_FILES['XmlFile']['name']);

	if(basename($_FILES['XmlFile']['name'])!='exchange.xml'){
		$headAlert='请上传用于退款和补发漏单的exchange.xml文件!';
	}else {
		$writeResult=writePackageInfo($_FILES['XmlFile']['tmp_name']);
		if($writeResult){
			$headAlert='文件上传成功';
		}else {
			$headAlert='文件上传失败';
		}
	}
}

function getPackageInfo() {
	$a = require ADMIN_ROOT . '/language/refound/package.php';
	return $a;
}

function writePackageInfo($file) {
	$index=array('id','type','gold_doller','item','dollar');
	if (file_exists ( $file )) {
		$xml = ( array ) simplexml_load_file ( $file );
		$array1 = ( array ) $xml ['Group'];
		foreach ( $array1 ['ItemSpec'] as $x ) {
			$array2 = ( array ) $x;
			$one=array();
			foreach ($index as $kv){
				if (isset($array2 ['@attributes'][$kv])){
					$one[$kv]=$array2 ['@attributes'][$kv];
				}
			}
			$array3 [] = $one;
		}
		$beforeArray=array();
		if(file_exists(ADMIN_ROOT . '/language/refound/package.php')){
			$beforeArray = getPackageInfo();
		}
		if(isset($beforeArray)){
			$newPackageArray=array_merge($beforeArray,$array3);
		}else {
			$newPackageArray=$array3;
		}
		$strarr = var_export ( $newPackageArray, true );
		file_put_contents ( ADMIN_ROOT . '/language/refound/package.php', "<?php\n \$productArray= " . $strarr . ";\nreturn \$productArray;\n?>" );
		return true;
	} else {
		return false;
	}
}


include( renderTemplate("{$module}/{$module}_{$action}") );

