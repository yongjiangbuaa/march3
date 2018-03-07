<?php
/*
* file:myvcode.class.php
* 验证码类,类名Vcode
*/
class Vcode
{
	public $width;              /*验证码宽度*/
	public $height;             /*验证码高度*/
	public $codeNum;            /*验证码字符个数*/
	public $checkCode;            /*验证码字符*/
	public $image;                /*验证码资源*/
	public $pixNum;            /*绘制干扰点的个数*/
	public $lineNum;            /*绘制干扰线的条数*/

	/*
	*构造方法实例化验证码对象，并初始化数据
	*@param int $width         设置默认宽度
	*@param int $height     设置默认高度
	*@param int $codeNum    设置验证码中的字符个数
	*@param int $pixNum        设置干扰点的个数
	*@param int $lineNum    设置干扰线的数量
	*/
	function __construct($width=110,$height=40,$codeNum=4,$pixNum=40,$lineNum=5)
	{
		$this->width = $width;
		$this->height = $height;
		$this->codeNum = $codeNum;
		$this->pixNum = $pixNum;
		$this->lineNum = $lineNum;
	}
	/*内部私有方法,创建图像资源*/
	public function getCreateImage()
	{
		$this->image = imagecreatetruecolor($this->width, $this->height);
		$white = imagecolorallocate($this->image,0xff,0xff,0xff);
		imagefill($this->image, 0, 0, $white);
		$black = imagecolorallocate($this->image,0,0,0);
		imagerectangle($this->image, 0, 0, $this->width-1, $this->height-1, $black);
	}
	/*内部私有方法,绘制字符，去掉o0Llz和012*/
	public function createCheckCode()
	{
		$code = '123456789abcdefghijkmnpqrstuvwxyABCDEFGHIJKMNPQRSTUVWXYZ';
		$this->checkCode = "";

		for($i=0; $i<$this->codeNum;$i++)
		{
			$char = $code{rand(0,strlen($code) - 1)};
			$this->checkCode .= $char;
			$fontColor = imagecolorallocate($this->image, rand(100,225), rand(0,128),rand(100,255));
			$fontSize = rand(10,20);
			$w = $i * $this->width/4 + imagefontwidth($fontSize);
			$w2 = $w + $this->width/4 - imagefontwidth($fontSize) - 20;
			$x = rand($w,$w2);
			$y = rand(imagefontheight($fontSize)+18,$this->height-2*imagefontheight($fontSize));
			#imagechar($this->image, $fontSize, $x, $y, $char, $fontColor);
			$range = rand(-10,10);
			ImageTTFText($this->image,$fontSize,$range, $x, $y, $fontColor,'./font.ttf',$char);
		}
	}

	/*内部私有方法设置干扰元素*/
	public function setDisturbColor()
	{
		/*绘制干扰点*/
		for($i=0; $i<$this->pixNum; $i++)
		{
			$color = imagecolorallocate($this->image, rand(0,255), rand(0,255), rand(0,255));
			imagesetpixel($this->image, rand(1,$this->width-2), rand(1,$this->height-2), $color);
		}
		/*绘制干扰线*/
		for($i=0; $i<intval($this->lineNum/2); $i++)
		{
			$color = imagecolorallocate($this->image, rand(0,255), rand(0,255), rand(0,255));
			imageline($this->image, rand(1,$this->width / 2), rand(1,$this->height / 2),rand($this->width / 2,$this->width - 2), rand($this->height / 2,$this->height - 2), $color);
		}
	}
	/*开启session保存 利用echo 输出图像*/
	public function show()
	{
		
		//$_SESSION['code'] = strtoupper($this->checkCode);
		$this->getCreateImage();
		$this->createCheckCode();
		$this->setDisturbColor();
		return $this->checkCode;
		//$this->outputImg();
		//file_put_contents('1.txt',$this->checkCode."\r\n",FILE_APPEND);
		//return $this->checkCode;
	}

	public function outputImg(){
		Header("content-type:image/png");
		imagepng($this->image);
	}
	
	/*析构方法，释放对象*/
	function __destruct()
	{
		imagedestroy($this->image);
	}
}
?>
