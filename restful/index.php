<?php

require __DIR__.'/../lib/User.php';
require __DIR__.'/../lib/Article.php';
$pdo = require __DIR__.'/../lib/db.php';
class Restful
{
	/*
	 *@var User	
	 */
	private $_user;

	/*
	 *@var Article
	 */
	private $_article;

	/*
	 * 请求方法
	 * @var string
	 */
	private $_requestMethod;

	/*
	 * 请求资源名称
	 * @var string
	 */
	private $_resourceName;

	/*
	 * 请求id
	 * @var string
	 */
	private $_id;

	/*
	 *允许请求的资源列表
	 *@var array
	 */
	private $_allowResources = ['users','articles'];

	/*
	 * 允许请求的方法
	 * @var array
	 */
	private $_allowRequestMethods = ['GET','POST','PUT','DELETE','OPTIONS'];
	/*
	 * 常用的状态码
	 * @var array
	 */
	private $_statusCodes = [
		200=>'ok',
		204=>'No Content',
		400=>'Bad Request',
		401=>'Unauthorized',
		403=>'Forbidden',
		404=>'Not Found',
		405=>'Method Not Allowed',
		500=>'Server Internal Error'
	];
	public function __construct(User $user,Article $article){
		$this->_user = $user;
		$this->_article = $article;
	}
	/*
	 * 唯一入口
	 */
	public function run(){
		try {
			$this->_setupRequestMethod();
			$this->_setupResource();
			if($this->_resourceName == 'users'){
			    return $this->_json($this->_handleUser());
			}else{
			    return $this->_json($this->_handleArticle());
			}
		} catch (Exception $e) {
			$this->_json(['error'=>$e->getMessage()],$e->getCode());
		}
		
	}

	/*
	 *初始化请求方法
	 */
	private function _setupRequestMethod(){
		$this->_requestMethod = $_SERVER['REQUEST_METHOD'];
		if(!in_array($this->_requestMethod, $this->_allowRequestMethods)){
			throw new Exception("请求方法不被允许", 405);
			
		}
	}

	/*
	 *初始化请求资源
	 */
	private function _setupResource(){
	    $path = $_SERVER['PATH_INFO'];
	    $params = explode('/',$path);
	    $this->_resourceName = $params[1];
	    if(!in_array($this->_resourceName,$this->_allowResources)){
	        throw new Exception('请求资源不被允许',400);
	    }
	    if(!empty($params[2])){
	        $this->_id = $params[2];
	    }
	}

	/*
	 *初始化请求资源标识符
	 */
	private function _setupId(){

	}
	/*
	 * 输出JSON
	 * @param $array
	 */
	private function _json($array,$code = 0){
		if($code >0 && $code !=200 && $code != 204){
		    header('HTTP/1.1 '.$code.' '.$this->_statusCodes[$code]);
		}
		header('Content-Type:application/json;charset=utf8');
		echo json_encode($array,JSON_UNESCAPED_UNICODE);
		exit();
	}
	/*
	 * 请求用户资源
	 */
	private function _handleUser(){
	    if($this->_requestMethod != 'POST'){
	        throw new Exception('请求资源不被允许',405);
	    }

	     //接收参数
	    $body = $this->_getbodyParams();
	    if(empty($body['username'])){
	        throw new Exception('用户名不能为空',400);
	    }
	    if(empty($body['password'])){
		throw new Exception('密码不能为空',400);
	    }
	    return $this->_user->register($body['username'],$body['password']);	   
	}

	/*
	 * 请求文章资源
	 */
	private function _handleArticle(){
	    switch($this->_requestMethod){
	        case 'POST':
		    return $this->_handleArticleCreate();
		case 'PUT':
		    return $this->_handleArticleEdit();
		case 'DELETE':
		    return $this->_handleArticleDelete();
		case 'GET':
		    if(empty($this->_id)){
		        return $this->_handleArticleList();
		    }else{
			return $this->_handleArticleView();
		    }
	    }
	}
	/*
	 * 获取请求体	
	 */
	private function _getbodyParams(){
	    $raw = file_get_contents('php://input');
	    if(empty($raw)){
	       throw new Exception('请求参数错误',400);
	    }
	    return json_decode($raw,true);
	}
}

$article = new Article($pdo);
$user = new User($pdo);

$restful = new Restful($user,$article);
error_reporting(3);
$restful->run();
