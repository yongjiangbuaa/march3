<?php
$prelogs = array(
		'/usr/local/cok/SFS2X/bugdata/bugfix_buyitemfb.log',
		'/usr/local/cok/SFS2X/bugdata/bugfix_buyitemfb_v2.log',
);


$outf = '/usr/local/cok/SFS2X/bugdata/bugfix_buyitemfb_v1+v2.log';



//OK,s152,126181627000152,200016,63,3,0,{"ret":{"result":true,"effect":1}}
//NG,s152,327294256000152,200300,594,NORECORDS

//OK,$server,$gameuid,$itemid,$subcount,$owncnt_ori,$newcnt
$player_been_subed = array();
foreach ($prelogs as $fname) {
	echo "$fname\n";
	$f = file($fname);
	foreach ($f as $line) {
		$data = trim($line);
		$tok = explode(',', $data);
		
		if ($tok[0] == 'NG' || $tok[6] == 0) {
			file_put_contents($outf, $data."\n", FILE_APPEND);
			continue;
		}
		
		$owncnt_ori = $tok[5];
		$subcount = $tok[4];
		$newcnt = $owncnt_ori - $subcount;
		if ($newcnt < 0) {
			$tok[6] = $owncnt_ori;
		}else{
			$tok[6] = $subcount;
		}
		
		$newdata = implode(',', $tok);
		file_put_contents($outf, $newdata."\n", FILE_APPEND);
	}
}
