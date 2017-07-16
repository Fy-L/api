<?php

require_once __DIR__.'/ErrorCode.php';
class User
{
    /**
     * 数据库链接句柄
     */
    private $_db;
    public function __construct($_db){
        $this->_db = $_db;
    }
    /**
     * 用户登陆
     */
    public function login($username, $password){	
        if(empty($username)){
	    throw new Exception('用户名不能为空',ErrorCode::USERNAME_CANNOT_EMPTY);
	}
	if( empty($password) ){
	    throw new Exception('密码不能为空',ErrorCode::PASSWORD_CANNOT_EMPTY);
	}
	$sql = 'SELECT * FROM `user` WHERE `username`=:username AND `password`=:password';
	$stmt = $this->_db->prepare($sql);
	$stmt->bindParam(':username',$username);
	$password = $this->_md5($password);
	$stmt->bindParam(':password',$password);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
	if(empty($user)){
	    throw new Exception('用户名或密码错误',ErrorCode::USERNAME_OR_PASSWORD_INVALID);
	}
	unset($user['password']);
	return $user;
    }
    private function _md5($string,$key = 'api'){
        return md5($string . $key);
    }
    /**
     * 用户注册
     */
    public function register($username, $password){
        if(empty($username)){
	    throw new Exception('用户名不能为空',ErrorCode::USERNAME_CANNOT_EMPTY);
	}
	if( empty($password) ){
	    throw new Exception('密码不能为空',ErrorCode::PASSWORD_CANNOT_EMPTY);
	}
	if($this->_isUsernameExists($username)){
	    throw new Exception('用户已存在',ErrorCode::USERNAME_EXISTS);
	}
	$sql = 'INSERT INTO `user` (`username`,`password`,`created_at`) VALUES(:username,:password,:created_at)';
	$createat = date('Y-m-d H:i:s',time());
	$password = $this->_md5($password);
	$stmt = $this->_db->prepare($sql);
	$stmt->bindParam(':username',$username);
	$stmt->bindParam(':password',$password);
	$stmt->bindParam(':created_at',$createat);
	if(!$stmt->execute()){
	    throw new Exception('注册失败',ErrorCode::REGISTER_FAIL);
	}
	return [
		'user_id' =>$this->_db->lastInsertId(),
		'username'=> $username,
		'createat'=>$createat
	       ];   
    }
    
    public function _isUsernameExists($username){
        $exists = false;
        $sql = 'SELECT * FROM `user` WHERE `username`=:username';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':username',$username);
        $stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);	
        return !empty($result);
    }
}
