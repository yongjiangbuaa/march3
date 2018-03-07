<?php

/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 15/12/10
 * Time: 10:31
 */
class UploadPhoto
{

    protected $photo_seq = 0;

    protected $folder_name = '/data/coq_avatar';
    protected $file_name = '';
    private $gameuid = null;

    public function execute($params)
    {
        // Log::info("UploadPhoto {$this->gameuid}===== ".json_encode($params)) ;
        $this->gameuid = $params['gameuid'] ;

        $this->photo_seq = intval($params['photo_seq']);
        if ($this->photo_seq < 0 || $this->photo_seq >= 1000000) {
            $this->throwException("photo_seq error: $this->photo_seq", 1);
        }

        $this->processUploadFile();
        $re = array();
        $re['file_name'] = $this->file_name;

        return $re;
    }

    protected function processUploadFile()
    {
        require_once PATH_ROOT . '/upload/UploadFile.class.php';
        $tmp_image_folder = '/tmp/coq/tmp_images';
        $this->makeFolder($tmp_image_folder);
        $upload_file = new UploadFile($tmp_image_folder);
        $tmp_image_file = $upload_file->getUploadFile('file');
        $is_image_storage_server = true;
        if ($is_image_storage_server) {
            $this->processFileLocal($tmp_image_file);
        } else {
            $data = array(
                'file' => $tmp_image_file,
                'gameuid' => $this->gameuid,
                'seq' => $this->photo_seq
            );
            $this->postFtpTask($data);
        }

    }

    protected function makeFolder($dir)
    {
        if (is_dir($dir)) {
            return true;
        }
        $re = mkdir($dir, 0777, true);
        if ($re === false) {
            $this->throwException("mkdir $dir fail");
        }
        return true;
    }

    protected function processFileLocal($local_file)
    {
        $final_image_path = $this->getImageStoragePath($this->gameuid, $this->photo_seq);
        $image_dir = dirname($final_image_path);
        $this->makeFolder($image_dir);
        $re = rename($local_file, $final_image_path);
//        Log::info("UploadPhoto local $this->gameuid $local_file => $final_image_path") ;
        if ($re === false) {
            $this->throwException("rename file $local_file to $final_image_path fail");
        }
    }

    protected function processFileFtp($local_file)
    {
        require_once PATH_ROOT . '/upload/FtpTransfer.class.php';
        $ftp = new FtpTransfer();
        $options = array(
            'host' => 's1.eleximg.com',
            'username' => 'cok',
            'password' => 'oAFnvlNDjSxC_W3'
        );
        $ftp->setFtpOptions($options);
        $ftp->setRemoteBaseDir('/img');
        $remote_file = $this->getImageFileName($this->gameuid, $this->photo_seq);
        $final_image_path = $ftp->put($local_file, $remote_file);
//         Log::info("UploadPhoto ftp $this->gameuid $local_file => $final_image_path") ;
    }

    /**
     * service层异常处理
     * @param string $message
     * @param int $code
     * @param mixed $customData
     * @throws Exception
     */
    protected function throwException($message,$code = 1,$customData=array()){
        throw new Exception($message,$code);
    }
    protected function postFtpTask($post_data)
    {
        // TODO: use task queue to process ftp task
        $this->processFileFtp($post_data['file']) ;
    }

    /**
     * 66967752000177 000177/6f8ab48e19d2ce13aa15fc20f187f136.jpg
     */
    protected function getImageFileName($gameuid, $seq, $ext = '.jpg')
    {
        $gameuid_str = strval($gameuid);
        $file_folder = substr($gameuid_str, -6);
        $this->file_name = md5($gameuid . '_' . $seq) . $ext;
        return $file_folder . '/' . $this->file_name;
    }

    protected function getImageStoragePath($gameuid, $seq, $ext = '.jpg')
    {
        $file_name = $this->getImageFileName($gameuid, $seq, $ext);
        return $this->folder_name . '/' . $file_name;
    }
}