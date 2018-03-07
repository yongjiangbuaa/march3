<?php
/**
 * XAssert
 * 
 * assertion class
 * 
 * 校验类
 * 
 * @author Wangqi
 * @package test 
 */
class XAssert{
	/**
	 * assert two variable's equality
	 * 
	 * 校验两个变量是否相同
	 * 
	 * @param mixed $expected
	 * @param mixed $actual
	 * @param string $message
	 * @throws XAssertFailedException
	 */
	public static function assertEquals($expected, $actual, $message = null){
		if(is_array($expected) && is_array($actual)){
			self::assertArrayEquals($expected, $actual);
			return;
		}
		if(is_object($expected) && is_object($actual)){
			self::assertObjectEquals($expected, $actual);
			return;
		}				
		if($expected !== $actual){
			if(is_null($message)){				
				self::fail("expected {$expected}, but was {$actual}");
				return;
			}
			self::fail($message);
		}
	}
	/**
	 * assert two array's equality
	 * 
	 * 校验两个数组是否相同
	 * 
	 * @param array $expected
	 * @param array $actual
	 * @throws XAssertFailedException
	 */
	public static function assertArrayEquals(array $expected, $actual){
		if(!is_array($actual)){
			self::fail("expected an array, but was not");
		}
		$message = "two arrays not equal";
		foreach($actual as $key => $value){
			$expectedValue = array_shift($expected);
			self::assertEquals($expectedValue, $value, $message);
		}
	}
	/**
	 * assert two object's equality
	 * 
	 * 校验两个对象是否相同
	 * 
	 * @param array $expected
	 * @param array $actual
	 * @throws XAssertFailedException
	 */
	public static function assertObjectEquals($expected, $actual){
		if(!is_object($actual)){
			self::fail("expected an object, but was not");
		}
		$expectedClass = get_class($expected);
		$class = get_class($actual);
		if($expectedClass != $class){
			self::fail("expected class {$expectedClass}, but was {$class}");
		}	
		$message = "two objects of class {$expectedClass} not equal";
		if(method_exists($expectedClass, 'equals')){
			self::assertEquals(true, $expected->equals($actual), $message);
		}	
		if(method_exists($expectedClass, '__toString')){
			self::assertEquals($expected->__toString(), $actual->__toString(), $message);
		}
		if(method_exists($expectedClass, 'asArray')){
			self::assertEquals($expected->asArray(), $actual->asArray(), $message);
		}
		if($expected !== $actual){
			self::fail($message);
		}
	}
	/**
	 * assert whether two variable is the same
	 * 
	 * 校验两个变量是否相同
	 * 
	 * @param mixed $expected
	 * @param mixed $actual
	 * @throws XAssertFailedException
	 */
	public static function assertSame($expected, $actual){
		$expectedRef = &$expected;
		$actualRef = &$actual;
		if($expectedRef !== $actualRef){
			self::fail("two objects don't have the same reference.");
		}
	}
	/**
	 * assert whether the specified variable is TRUE
	 * 
	 * 校验指定变量是否为TRUE
	 * 
	 * @param mixed $expected
	 * @throws XAssertFailedException
	 */
	public static function assertTrue($expected){
		if($expected!==TRUE){
			self::fail("expected TRUE, but got FALSE.");
		}
	}
	/**
	 * assert whether the specified variable is empty
	 * 
	 * 校验指定变量是否为空
	 * 
	 * @param mixed $expected
	 * @throws XAssertFailedException
	 */
	public static function assertEmpty($expected){
		if(!empty($expected)){
			self::fail("expected an empty value, but got a not empty value.");
		}
	}
	/**
	 * assert whether the specified variable is not empty
	 * 
	 * 校验指定变量是否为非空
	 * 
	 * @param mixed $expected
	 * @throws XAssertFailedException
	 */
	public static function assertNotEmpty($expected){
		if(empty($expected)){
			self::fail("expected a not empty value, but got an empty value.");
		}
	}
	/**
	 * assert whether the specified variable is NULL
	 * 
	 * 校验指定变量是否为NULL
	 * 
	 * @param mixed $expected
	 * @throws XAssertFailedException
	 */
	public static function assertNULL($expected){
		if(!is_null($expected)){
			self::fail("expected null, but the value is not null");
		}
	}
	/**
	 * assert whether the specified variable is not NULL
	 * 
	 * 校验指定变量是否为非NULL
	 * 
	 * @param mixed $expected
	 * @throws XAssertFailedException
	 */
	public static function assertNotNULL($expected){
		if(is_null($expected)){
			self::fail("expected a not null value, but the value is null");
		}
	}
	/**
	 * assert whether the specified variable is string
	 * 
	 * 校验指定变量是否为字符串
	 * 
	 * @param mixed $expected
	 * @throws XAssertFailedException
	 */
	public static function assertString($expected){
		if (!is_string($expected)){
			self::fail("expected a string, but got a non-string value");
		}
	}
	/**
	 * assert whether the specified variable is Object
	 * 
	 * 校验指定变量是否为对象
	 * 
	 * @param mixed $expected
	 * @throws XAssertFailedException
	 */
	public static function assertObject($expected){
		if(!is_object($expected)){
			self::fail("expected an object, but got a non-object value");
		}
	}
	/**
	 * assert whether the specified variable is array
	 * 
	 * 校验指定变量是否为数组
	 * 
	 * @param mixed $expected
	 * @throws XAssertFailedException
	 */
	public static function assertArray($expected){
		if(!is_array($expected)){
			self::fail("expected an array, but got a non-array value");
		}
	}
	/**
	 * assert whether the specified property exists in the specifed class
	 * 
	 * 校验指定类中是否定义了指定的属性
	 * 
	 * @param mixed $class
	 * @param mixed $property
	 * @throws XAssertFailedException
	 */
	public static function assertPropertyExists($class, $property){
		if(!property_exists($class, $property)){
			self::fail("property $property not exists in class ".(is_object($class)?get_class($class):$class));
		}
	}
	/**
	 * assert whether the specified key exists in the specifed array
	 * 
	 * 校验指定数组中是否定义了指定的键值
	 * 
	 * @param string $key
	 * @param array $search
	 * @throws XAssertFailedException
	 */
	public static function assertKeyExists($key, $search){
		if(!is_array($search)){
			self::fail("target is not an array: ".var_export($search, TRUE));
		}
		if(!key_exists($key, $search)){
			self::fail("key $key not exists in array: ".var_export($search, TRUE));
		}
	}
	/**
	 * assertion failed, will throw XAssertFailedException
	 * 
	 * 校验失败，抛出XAssertFailedException
	 * 
	 * @param string $message 错误消息
	 * @throws XAssertFailedException
	 */
	public static function fail($message){
		throw new XAssertFailedException($message);
	}
}
?>