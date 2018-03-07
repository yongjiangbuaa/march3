<?php
class UploadFile {
	protected $upload_dir = '/tmp';
	public function __construct($upload_dir=null){
		if(!empty($upload_dir) && is_writable($upload_dir)){
			$this->upload_dir = $upload_dir;
		}else{
			throw new Exception("upload dir $upload_dir not writable");
		}
	}
	public function getUploadFile($key){
		if(!isset($_FILES[$key])){
			throw new Exception("not upload file with $key found");
		}
		$file = $_FILES[$key];
		if($file['error'] != UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])){
			throw new Exception("file upload error size:".$file["size"], $file['error']);
		}
		$uploadfile = $this->getUploadFilename($file['name']);
		if(move_uploaded_file($file['tmp_name'], $uploadfile)){
			return $uploadfile;
		}else{
			throw new Exception("move to $uploadfile fail");
		}
	}
	
	public function getMddUploadFile($key){
		if(!isset($_FILES[$key])){
			throw new Exception("not upload file with $key found");
		}
		$file = $_FILES[$key];
		if($file['error'] != UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])){
			throw new Exception("file upload error", $file['error']);
		}
		
		$uploadfile = $this->upload_dir . '/' .$file['name'];
		if(move_uploaded_file($file['tmp_name'], $uploadfile)){
			return $uploadfile;
		}else{
			throw new Exception("move to $uploadfile fail");
		}
	}
	
	
	protected function getUploadFilename($filename){
		$fileext = substr($filename, strrpos ($filename, '.'));
		$uploadfile = "";
		do{
			$uploadfile = $this->upload_dir . '/' .md5(time() . mt_rand()) . $fileext;
			if(!file_exists($uploadfile)){
				break;
			}
		}while (true);
		return $uploadfile;
	}
	
	public function getFileName($key){
		return $_FILES[$key]['name'];
	}
}
