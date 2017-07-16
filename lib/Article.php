<?php

require_once __DIR__ .'/ErrorCode.php';

class Article
{    
    /**
     * 数据库链接句柄
     */
    private $_db;
    public function __construct($_db){
        $this->_db = $_db;
    }
    /**
     * 创建文章
     * @param $title
     * @param $content
     * @param $userId
     */     
    public function create($title,$content,$userId){
        if(empty($title)){
	    throw new Exception('文章标题不能为空',ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
        }
	if(empty($content)){
	    throw new Exception('文章内容不能为空',ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY);
	}
	//$this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
	$sql = 'INSERT INTO `article` (`title`,`content`,`user_id`,`create_at`) VALUES(:title,:content,:userId,:createat)';
	$stmt = $this->_db->prepare($sql);
	$createat = date('Y-m-d H:i:s',time());
	$stmt->bindParam(':title',$title);
	$stmt->bindParam(':content',$content);
	$stmt->bindParam(':userId',$userId);
	$stmt->bindParam(':createat',$createat);
	if(!$stmt->execute()){
	    throw new Exception('发表文章失败',ErrorCode::ARTICLE_CREATE_FAIL);
	}
	return [
	    'articleId'=>$this->_db->lastInsertId(),
	    'title'=>$title,
	    'content'=>$content,
	    'userId'=>$userId,
	    'createdAt'=>$createat
	];
    }

    /**
     * 查看一篇文章
     * @param $articleId
     */     
    public function view($articleId){
        if(empty($articleId)){
	    throw new Exception('文章ID不能为空',ErrorCode::ARTICLE_ID_CANNOT_EMPTY);
	}
	$sql = 'SELECT * FROM `article` WHERE `article_id`=:id';
	$stmt = $this->_db->prepare($sql);
	$stmt->bindParam(':id',$articleId);
	$stmt->execute();
	$article = $stmt->fetch(PDO::FETCH_ASSOC);
	if(empty($article)){
	    throw new Exception('文章不存在',ErrorCode::ARTICLE_NOT_FOUND);
	}
	return $article;
    }

    /**
     * 编辑文章
     * @param $articleId
     * @param $title
     * @param $content
     * @param $userId
     */     
    public function edit($articleId,$title,$content,$userId){
        $article = $this->view($articleId);
	if($article['user_id']!=$userId){
	    throw new Exception('你无权编辑改文章',ErrorCode::PERMISSION_DENIED);
	}
	$title = empty($title) ? $article['title']:$title;
	$content = empty($content) ? $article['content']:$content;
	if($article['title'] === $title && $article['content'] === $content){
	    return $article;
	}
	$sql = 'UPDATE `article` SET `title`=:title,`content`=:content WHERE `article_id`=:id';
	$stmt = $this->_db->prepare($sql);
	$stmt->bindParam(':title',$title);
	$stmt->bindParam(':content',$content);
	$stmt->bindParam(':id',$articleId);
	if(!$stmt->execute()){
	    throw new Exception('文章编辑失败',ErrorCode::ARTICLE_EDIT_FAIL);
	}	
	return [
	    'article_id'=>$articleId,
	    'title'=>$title,
	    'content'=>$content,
	    'create_at'=>$article['create_at']
	];
    }

    /**
     * 删除文章
     * @param $title
     * @param $userId
     */     
    public function delete($articleId,$userId){
	$article = $this->view($articleId);
	if($article['user_id']!==$userId){
	    throw new Exception('您无权操作',ErrorCode::PERMISSION_DENIED);
	}
        $sql = 'DELETE FROM `article` WHERE `article_id`=:articleId AND `user_id`=:userId';
	$stmt = $this->_db->prepare($sql);
	$stmt->bindParam(':articleId',$articleId);
	$stmt->bindParam(':userId',$userId);
	if(!$stmt->execute()){
	    throw new Exception('删除失败',ErrorCode::ARTICLE_DELETE_FAIL);
	}
	return true;
    }

    /**
     * 读取文章列表
     * @param $userId
     * @param $page
     * @param $size
     */     
    public function getList($userId,$page=1,$size=10){
	if($size>100){
	    throw new Exception('分页大小最大为100',ErrorCode::PAGE_SIZE_TO_BIG);
	}
        $sql = 'SELECT * FROM `article` WHERE `user_id`=:id limit :limit,:offset';
	$limit = ($page-1)*$size;
	$limit = $limit<0?0:$limit;
	$stmt = $this->_db->prepare($sql);
	$stmt->bindParam(':id',$userId);
	$stmt->bindParam(':limit',$limit);
	$stmt->bindParam(':offset',$size);
	$stmt->execute();
	$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $data;
    }
}
