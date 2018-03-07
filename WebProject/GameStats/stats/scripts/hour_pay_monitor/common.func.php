<?php
define('ELEX_MAGIC_QUOTE_GPC',get_magic_quotes_gpc());

function sendMailForReport($title, $content, $projectName, $Addressees){
	//echo "ok4";
	include_once 'class.phpmailer.php';
	//echo "ok5";
	$mail = new PHPMailer ();
	$mail->CharSet = "UTF-8"; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置为 UTF-8
	$mail->IsSMTP (); // 设定使用SMTP服务
	$mail->SMTPAuth = true; // 启用 SMTP 验证功能
	$mail->SMTPSecure = "ssl"; // SMTP 安全协议
	$mail->Host = "smtp.elex-tech.com"; // SMTP 服务器
	$mail->Port = 465; // SMTP服务器的端口号
	$mail->Username = "slg_gm_report@think-nice.com"; // SMTP服务器用名
	$mail->Password = "ELEXtech%5858"; // SMTP服务器密码
	$mail->SetFrom ( 'slg_gm_report@think-nice.com', '['.$projectName.']' ); // 设置发件人地址和名称
	$mail->AddReplyTo ( "slg_gm_report@think-nice.com", $projectName );
	// 设置邮件回复人地址和名称
	$mail->Subject = '['.$projectName.'] '.$title; // 设置邮件标题
	$mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端";
	// 可选项，向下兼容考虑
	$mail->MsgHTML ( $content ); // 设置邮件内容
	foreach($Addressees as $name){
		$mail->AddAddress($name.'@elex-tech.com', $name);
	}

	//echo "ok6";
	if (! $mail->Send ()) {
		error_log($mail->ErrorInfo);
		echo "发送失败：" . $mail->ErrorInfo;
		return false;
		//echo "发送失败：" . $mail->ErrorInfo;
	} else {
		//echo "ok7";
		return true;
	}
}


function sendSMSForReport($group, $serviceName, $serviceContent){
	// curl -d 'contact=aok&status=test by bw&service=付费预警' --header "Host: op.cok.elexapp.com" 'http://10.155.245.248/sms/send'
	$url = 'http://10.155.245.248/sms/send';
	$ch = curl_init();

	$params = array();
	$params['contact'] = $group;
	$params['service'] = $serviceName;
	$params['status'] = $serviceContent;


	//$query_string = http_build_query($params);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
	curl_setopt($ch, CURLOPT_POST, true);

	$header[] = "Host: op.cok.elexapp.com";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10000);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	if (strpos($url, 'https') === 0) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	}

	$result = curl_exec($ch);
	
	$info = curl_getinfo($ch);

	curl_close($ch);

	return $info;

}
class FileNotFoundException extends Exception{
    protected $file_not_found;
    public function __construct($message,$code,$file){
        parent::__construct($message,$code);
        $this->file_not_found = $file;
    }
    public function getNotFoundFile(){
        return $this->file_not_found;
    }
}



class ElexException extends Exception{}