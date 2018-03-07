<?php
class ftp {
	private $conn_id;
	private $host;
	private $username;
	private $password;
	private $port;
	public $timeout = 300;
	public $passive = true;
	public $ssl = false;
	public $system_type = '';

	public function __construct($host, $username, $password, $port = 21) {
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->port = $port;
	}

	public function connect() {
		if(is_resource($this->conn_id)){
			return true;
		}
		if ($this->ssl == false) {
			$this->conn_id = ftp_connect ( $this->host, $this->port );
		} else {
			if (function_exists ( 'ftp_ssl_connect' )) {
				$this->conn_id = ftp_ssl_connect ( $this->host, $this->port );
			} else {
				return false;
			}
		}
		if (! is_resource ( $this->conn_id )) {
			throw new FtpException ( "can't connect to $this->username@$this->host:$this->port" );
		}

		$result = ftp_login ( $this->conn_id, $this->username, $this->password );

		if ($result === false) {
			throw new FtpException ( "login fail by $this->username@$this->host with password:yes" );
		}
		ftp_set_option ( $this->conn_id, FTP_TIMEOUT_SEC, $this->timeout );

		if ($this->passive == true) {
			ftp_pasv ( $this->conn_id, true );
		} else {
			ftp_pasv ( $this->conn_id, false );
		}

		$this->system_type = ftp_systype ( $this->conn_id );
		return true;
	}

	public function put($local_file_path, $remote_file_path, $mode = FTP_BINARY) {
		if (ftp_put ( $this->conn_id, $remote_file_path, $local_file_path, $mode )) {
			return true;
		} else {
			return false;
		}
	}

	public function get($local_file_path, $remote_file_path, $mode = FTP_ASCII) {
		if (ftp_get ( $this->conn_id, $local_file_path, $remote_file_path, $mode )) {
			return true;
		} else {
			return false;
		}
	}

	public function chmod($permissions, $remote_filename) {
		if ($this->is_octal ( $permissions )) {
			$result = ftp_chmod ( $this->conn_id, $permissions, $remote_filename );
			if ($result) {
				return true;
			} else {
				return false;
			}
		} else {
			throw new FtpException ( '$permissions must be an octal number' );
		}
	}

	public function chdir($directory) {
		return ftp_chdir ( $this->conn_id, $directory );
	}

	public function delete($remote_file_path) {
		if (ftp_delete ( $this->conn_id, $remote_file_path )) {
			return true;
		} else {
			return false;
		}
	}

	public function mkdir($directory) {
		if (@ftp_mkdir ( $this->conn_id, $directory )) {
			return true;
		} else {
			return false;
		}
	}

	public function mkdir_recursive($directory) {
		$parts = explode ( '/', $directory );
		$path = '';
		while ( ! empty ( $parts ) ) {
			$path .= array_shift ( $parts );
			if ($path !== '') {
				$re = $this->mkdir ( $path );
			}
			$path .= '/';
		}
		return $re;
	}
	public function rename($old_name, $new_name) {
		if (ftp_rename ( $this->conn_id, $old_name, $new_name )) {
			return true;
		} else {
			return false;
		}
	}

	public function remove_dir($directory) {
		if (ftp_rmdir ( $this->conn_id, $directory )) {
			return true;
		} else {
			return false;
		}
	}

	public function dir_list($directory) {
		$contents = ftp_nlist ( $this->conn_id, $directory );
		return $contents;
	}

	public function cdup() {
		return ftp_cdup ( $this->conn_id );
	}

	public function current_dir() {
		return ftp_pwd ( $this->conn_id );
	}

	private function is_octal($i) {
		return decoct ( octdec ( $i ) ) == $i;
	}

	public function close(){
		if (is_resource($this->conn_id)) {
			ftp_close ( $this->conn_id );
		}
	}
	public function __destruct() {
		$this->close();
	}
}

class FtpException extends Exception {
}