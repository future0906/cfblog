<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:评论的模型类
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*	2007-12-3	Start writing this file
*/
//check is directly access
if(!defined('rootpath')) {
	die("Access denied");
}
//include section
include_once rootpath.'/config/config.php';
include_once rootpath.'/includes/core/model/BaseModel.php';

/*
	评论操作模型，通过该模型，可以对评论进行操作
	该类是基于BaseModel提供的基本操作
	@package /includes/core/model
	@copyright future0906
	@version 0.1.0
*/
class CommentModel extends BaseModel
{
	/*
		构造函数，要扩展BaseModel，必须在构造函数中把BaseModel的各属性填写好
		@param rDbo 数据库操作对象
		@return nul
	*/
	public function __construct(&$rDbo)
	{
		$this->mrDbo = &$rDbo;
		$this->mTableName = 'comments';
		$this->mFullTableName = db_prefix.$this->mTableName;
		$this->mColumn = array(
						'eid'=>'int(32) unsigned not null AUTO_INCREMENT',
						'blogid'=>'int(32) unsigned not null',
						'content'=>'text not null',
						'reply'=>'text null',
						'nick_name'=>'text not null',
						'homepage'=>'text null',
						'email'=>'text null',
						'pub_date'=>'datetime not null',
						'IP'=>'varchar(15) not null',
						'status'=>'int(32) unsigned not null default 0'
						);
		$this->mPrimaryKey = 'eid';
		$this->mOneToOne = null;
		$this->mOneToMOre = array(
							'comments.blogid'=>'blogs.eid'
							);
		$this->InitCache('comments.php',"select substring(content,1,".blog_comment_byte.") content,eid,blogid,reply,nick_name,homepage,email,ip,pub_date,status from {$this->mFullTableName} order by pub_date desc limit ".blog_newcomment);
	}
	/*
		根据blogid取出指定的评论
		@param blogid int blog编号
		@return array
	*/
	public function GetCommentByBlog($blogid)
	{
		$blogid = intval($blogid);
		$fields = "nick_name,homepage,pub_date date,content,nick_name,reply";
		$orderby = "order by  pub_date";
		$where_clause = "where blogid = $blogid";
		return $this->FindAll($fields,$where_clause,$orderby);
	}
	/*
		提交一个评论
		@param newcomment array 
	*/
	public function CommitComment($newcomment)
	{
		//Blog表中的c_count字段需要加1
		$this->mrDbo->StartTrans();
		include_once rootpath.'/includes/core/model/BlogModel.php';
		$blog = new BlogModel($this->mrDbo);
		$match = array("eid"=>"{$newcomment['blogid']}");
		$field = 'c_count';
		$value = 'c_count + 1';
		$blog->UpdateOne($match,$field,$value);
		//提交
		$this->Save($newcomment);
		$this->mrDbo->EndTrans();
	}
	/*
		获取所有的评论。分页。
		@param page int 页号
		@return array
	*/
	public function GetComment($page)
	{
		if($page < 1) {//检查页号是否少于1
			die("内部错误：页号不能为0");
		}

		$fields = 'substring(content,1,10) content,eid,substring(nick_name,1,3) nick_name,email,pub_date,ip';
		$limit = blog_comment_adminlist_count;
		$offset = ($page - 1) * $limit;
		$limit_clause = "limit $limit offset $offset";
		$order_clause = "order by pub_date desc";
		$where_clause = null;
		
		return $this->FindAll($fields,$where_clause,$order_clause,$limit_clause);
	}
	/*
		获取评论数目
		@param page int 页号
		@return array
	*/
	public function GetCommentCount()
	{
		$fields = 'count(eid)';
		$this->Find($fields);
		return $this->GetOne();
	}
	/*
		获取评论内容
		@param eid int 评论编号
		@return string
	*/
	public function GetContent($eid)
	{
		$fields = 'eid,content';
		$where_clause = "where eid = $eid";
		return $this->FindAll($fields,$where_clause);
	}
	/*
		回复某一条评论
		@param eid int 评论编号
		@param reply string 回复内容
	*/
	public function Reply($eid,$reply)
	{
		$update_array = array('reply'=>"'".$reply."'",'eid'=>$eid);
		$this->Save($update_array);
		return true;
	}
}

?>