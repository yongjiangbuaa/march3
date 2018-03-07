<?php
import('service.user.Passport');
import('persistence.dao.XActiveRecord');
/**
 * AbstractAccount
 * 
 * abstract class of account
 * 
 * Account抽象类
 * 
 * @Entity
 * @author Wangqi
 * @package user
 */
abstract class AbstractAccount extends XActiveRecord implements Passport{
	/**
	 * @Id
	 * @var string
	 */
	protected $username = '';
	/**
	 * @Visible(visible=false,scope=own)
	 * @var string
	 */
	protected $password = NULL;
	/**
	 * @Visible(visible=false,scope=own)
	 * @var string
	 */
	protected $salt = NULL;
	protected $nickname = NULL;
	protected $email = NULL;
	protected $userUid = NULL;
	
	/**
	 * (non-PHPdoc)
	 * @see Passport::getUsername()
	 */
	public function getUsername(){
		return $this->username;
	}
	/**
	 * (non-PHPdoc)
	 * @see Passport::setUsername()
	 */
	public function setUsername($username){
		$this->username = $username;
	}
	/**
	 * (non-PHPdoc)
	 * @see Passport::getPassword()
	 */
	public function getPassword(){
		return $this->password;
	}
	/**
	 * (non-PHPdoc)
	 * @see Passport::setPassword()
	 */
	public function setPassword($password){
		$this->password = $password;
	}
	/**
	 * 
	 * Enter description here ...
	 */
	public function getSalt(){
		return $this->salt;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $salt
	 */
	public function setSalt($salt){
		$this->salt = $salt;
	}
	/**
	 * (non-PHPdoc)
	 * @see Passport::getNickname()
	 */
	public function getNickname(){
		return $this->nickname;
	}
	/**
	 * (non-PHPdoc)
	 * @see Passport::setNickname()
	 */
	public function setNickname($nickname){
		$this->nickname = $nickname;
	}
	/**
	 * (non-PHPdoc)
	 * @see Passport::getEmail()
	 */
	public function getEmail(){
		return $this->email;
	}
	/**
	 * (non-PHPdoc)
	 * @see Passport::setEmail()
	 */
	public function setEmail($email){
		$this->email = $email;
	}
	/**
	 * (non-PHPdoc)
	 * @see Passport::getUserUid()
	 */
	public function getUserUid(){
		return $this->userUid;
	}
	/**
	 * (non-PHPdoc)
	 * @see Passport::setUserUid()
	 */
	public function setUserUid($userUid){
		$this->userUid = $userUid;
	}
}