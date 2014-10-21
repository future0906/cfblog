<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:数据层的抽象对象，定义数据层对象的抽象操作
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*	2007-12-2	Start writing this file
*	2008-8-13	添加对文章摘要,不再在GetBlog中返回文章全文
*/
//check is directly access
if(!defined('rootpath')) {
	die("Access denied");
}
//include section
include_once rootpath.'/config/config.php';
include_once rootpath.'/includes/core/model/BaseModel.php';
//define section
define('BLOG_MODE_DETAIL',1);//详细模式
define('BLOG_MODE_LIST',2);//列表模式
define('BLOG_MODE_ADMINLIST',3);//后台列表模式
define('BLOG_MODE_DRAFTLIST',4);//草稿模式
define('BLOG_MODE_ADMIN',5);
define('BLOG_MODE_INDEX',6);
/*
	日志管理模型，通过该模型，可以实现简单查找、删除、修改日志。
	该类是基于BaseModel提供的基本操作
	@package /includes/core/model/
	@copyright future0906
	@version 0.1.0
*/
class BlogModel extends BaseModel
{
	/*
		构造函数，设置数据库操作对象
		@param rDbo 数据库操作对象
		@return nul
	*/
	public function __construct(&$rDbo)
	{
		$this->mrDbo = &$rDbo;
		$this->mTableName = 'blogs';
		$this->mFullTableName = db_prefix.$this->mTableName;
		$this->mColumn = array(
						'eid'=>'int(32) unsigned not null AUTO_INCREMENT Primary Key',
						'title'=>'text not null',
						'content'=>'mediumtext not null',
						'createat'=>'datetime not null',
						'updateat'=>'datetime not null',
						'category'=>'int(32) unsigned not null',
						'password'=>'char(32) not null',
						'r_count'=>'int(20) unsigned not null',
						'c_count'=>'int(20) unsigned not null',
						'c_allow'=>'boolean not null default true',
						'visible'=>'boolean not null default true',
						'on_top'=>'boolean not null default false',
						'draft'=>'boolean not null default false',
						'status'=>'int(32) unsigned not null default 0'
						);
		$this->mPrimaryKey = 'eid';
		$this->mOneToOne = array('blogs.category'=>'categories.eid');
		$this->mOneToMore = array('comments.blogid'=>'blogs.eid');
		$this->InitCache('blogs.php',"select eid,title from {$this->mFullTableName} order by createat desc limit ".blog_newest_count);
	}
	/*
		获取博客日志
		@param $mode int 获取的模式，有列表和详细
		@param $page int 获取的页号
	*/
	public function GetBlog($page=1,$mode=BLOG_MODE_DETAIL,$category=null,$summaryContent=true)
	{
		if($page < 1) {//检查页号是否少于1
			die("内部错误：页号不能为0");
		}
		if(BLOG_MODE_DETAIL == $mode) {//设置每页个数
			$limit = blog_normal_count;
			$fields = 'date_format(createat,"%Y/%m/%d") `date`,date_format(createat,"%h:%i %p") `time`,'.db_prefix.'blogs.eid id,title,substring(content,1,'.blog_summary_byte.') content,category cid,name category,password,r_count,c_count,c_allow,on_top';
		}
		else if(BLOG_MODE_LIST == $mode) {
			$fields = 'date_format(createat,"%Y/%m/%d") `date`,date_format(createat,"%h:%i") `time`,'.db_prefix.'blogs.eid id,title,category cid,name category,r_count,c_count,c_allow,on_top';
			$limit = blog_list_count;
		}
		else if(BLOG_MODE_ADMINLIST == $mode) {
			$fields = 'date_format(createat,"%Y/%m/%d") `date`,date_format(createat,"%h:%i %p") `time`,'.db_prefix.'blogs.eid id,title,category cid,name category,password,r_count,c_count,c_allow,on_top,draft';
			$limit = blog_adminlist_count;
		}
		else if(BLOG_MODE_DRAFTLIST == $mode) {
			$fields = 'date_format(createat,"%Y/%m/%d") `date`,date_format(createat,"%h:%i %p") `time`,'.db_prefix.'blogs.eid id,title,category cid,name category,password,r_count,c_count,c_allow,on_top,draft';
			$limit = blog_draftlist_count;
		}
		$offset = ($page - 1) * $limit;//计算记录的偏移量
		$from_table = db_prefix."blogs left outer join ".db_prefix."categories on ".db_prefix."blogs.category = ".db_prefix."categories.eid";
		$limit_clause = "limit $limit offset $offset";

		if(BLOG_MODE_DETAIL == $mode) {
			$where_clause = "where visible= true and draft = false";
		}
		else if(BLOG_MODE_LIST == $mode) {
			$where_clause = "where visible= true and draft = false and password is null";
		}
		else if(BLOG_MODE_ADMINLIST == $mode) {
			$where_clause = null;
		}
		else if(BLOG_MODE_DRAFTLIST == $mode) {
			$where_clause ="where draft = true";
		}
		if(null != $category) {
			if(!isset($where_caluse)) {
				$where_clause ="where category=$category";
			}
			else {
				$where_clause .= " and category=$category";
			}
		}

		$order_clause = "order by on_top desc,createat desc";
		$result = $this->FindAll($fields,$where_clause,$order_clause,$limit_clause,$from_table);

		if ($summaryContent && BLOG_MODE_DETAIL == $mode) {
			foreach ($result as &$oneRow) {
				$oneRow['content'] = strip_tags($oneRow['content'],blog_summary_allow_tags);
			}
		}
		return $result;
	}
	/*
		获取日志数目
		@param $category int
	*/
	public function GetBlogCount($category=null,$mode=BLOG_MODE_DETAIL)
	{
		$fields = 'count(eid)';
		if(BLOG_MODE_DETAIL == $mode || BLOG_MODE_LIST == $mode) {
			$where_clause = "where visible= true and draft = false";
		}
		else if(BLOG_MODE_ADMINLIST == $mode) {
			$where_clause = null;
		}
		else if(BLOG_MODE_DRAFTLIST == $mode) {
			$where_clause = "where draft=true";
		}

		if(null != $category) {
			if(!isset($where_caluse)) {
				$where_clause ="where category=$category";
			}
			else {
				$where_clause .= " and category=$category";
			}
		}
		$this->Find($fields,$where_clause);
		return $this->GetOne();
	}
	/*
		获取一个日志
		@param $eid int
		@param $add_rcount bool 是否增加r_count
	*/
	public function GetOneBlog($eid,$add_rcount=false,$mode=BLOG_MODE_INDEX)
	{
		$fields = 'date_format(createat,"%h:%i %p") `time`,date_format(createat,"%Y/%m/%d") `date`,'.db_prefix.'blogs.eid id,title,content,category cid,name category,password,r_count,c_count,c_allow,on_top,visible,draft';
		$from_table = db_prefix."blogs left outer join ".db_prefix."categories on ".db_prefix."blogs.category = ".db_prefix."categories.eid";
		if(BLOG_MODE_INDEX == $mode) {
			$where_clause = "where visible = true and draft = false and {$this->mFullTableName}.eid = $eid";
		}
		else if(BLOG_MODE_ADMIN == $mode) {
			$where_clause = "where {$this->mFullTableName}.eid = $eid";
		}
		
		if($add_rcount) {//是否更新r_count字段
			$this->UpdateOne(array('eid'=>$eid),'r_count','r_count + 1');
		}
		$this->Find($fields,$where_clause,NULL,NULL,$from_table);
		return $this->GetOneRow();
	}
	/*
		增加阅读计数器r_count
		@param $eid int
	*/
	public function AddRCount($eid)
	{
		$this->UpdateOne(array('eid'=>$eid),'r_count','r_count + 1');
	}
	/*
		发布一篇文章
		@param $eid int
	*/
	public function PublishPost($eid)
	{
		$this->UpdateOne(array('eid'=>$eid),'draft','0');
	}
}
?>