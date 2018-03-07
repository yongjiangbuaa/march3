<?php
require_once PATH_ROOT . '/upload/ftp.class.php';

class FtpTransfer {
	/**
	 *
	 * ftp client
	 * @var ftp
	 */
	private $ftp = null;

	private $host = 'localhost';
	private $username = 'user';
	private $password = 'pass';
	private $remote_base_dir = '/attachment';
	private $url_prefix = '';
	
	/**
	 * set the options for ftp transfer
	 * supported fields: host, username, password
	 * @param array $options
	 */
	public function setFtpOptions($options){
		if (!is_array($options)){
			return;
		}
		if (isset($options['host'])){
			$this->host = $options['host'];
		}
		if (isset($options['username'])){
			$this->username = $options['username'];
		}
		if (isset($options['password'])){
			$this->password = $options['password'];
		}
	}
	
	protected function connect(){
		if ($this->ftp === null){
			$this->ftp = new ftp($this->host, $this->username, $this->password);
			$this->ftp->connect();
		}
	}

	/**
	 * upload a local file to remote ftp server
	 * @param string $local_file_path the file path of local file
	 * @param string $custom_filename the file name will be put to ftp
	 * @param bool $timestamp_filename rename the file name with timestamp
	 * @throws Exception
	 * @return string
	 */
	public function put($local_file_path, $custom_filename='', $timestamp_filename=false){
		if(!file_exists($local_file_path)){
			throw new Exception("file $local_file_path not exist");
		}
		$this->connect();
		$this->ftp->chdir($this->remote_base_dir);
		$pathinfo = pathinfo($local_file_path);
        $ext = $pathinfo['extension'];
        if($timestamp_filename)
        {
		    $new_file_name =  date('Ym/d/').md5(microtime(TRUE)). '.' . $ext;
        }
        else
        {
            $new_file_name = $pathinfo['basename'];
        }
        $remote_file_path = $custom_filename == '' ? $new_file_name : $custom_filename;
        $remote_dir = dirname($remote_file_path);
        if ($remote_dir != '' && $remote_dir != '/' && $remote_dir != '.') {
        	$this->ftp->mkdir_recursive($remote_dir);
        }
		$re = $this->ftp->put($local_file_path, $remote_file_path);
		$this->ftp->close();
		if($re === false){
			throw new Exception("put file $local_file_path to ftp fail");
		}else{
			return $this->getFileUrl($remote_file_path);
		}
	}


	protected function getFileUrl($file_name){
		return $this->url_prefix . $this->remote_base_dir . '/' . $file_name;
	}
	/**
	 * @return the $remore_base_dir
	 */
	public function getRemoreBaseDir() {
		return $this->remote_base_dir;
	}

	/**
	 * @param field_type $remote_base_dir
	 */
	public function setRemoteBaseDir($remote_base_dir) {
		$this->remote_base_dir = $remote_base_dir;
	}

	/**
	 * @param field_type $url_prefix
	 */
	public function setUrlPrefix($url_prefix) {
		$this->url_prefix = $url_prefix;
	}

}