<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:分类模型类
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*	2007-1-23	Start writing this file
*/
//check is directly access
if(!defined('rootpath')) {
	die("Access denied");
}
//include section
include_once rootpath.'/includes/core/model/BaseModel.php';
//define section
/*
	分类操作模型，通过该模型，可以对分类进行增删改操作
	该类是基于BaseModel提供的基本操作
	@package /includes/core/model/
	@copyright future0906
	@version 0.1.0
*/
class CategoryModel extends BaseModel
{
	/*
		构造函数，要扩展BaseModel，必须在构造中把BaseModel的各属性填写好
		@param rDbo 数据库操作对象
		@return null
	*/
	public function __construct(&$rDbo)
	{
		$this->mrDbo = &$rDbo;
		$this->mTableName = 'categories';
		$this->mFullTableName = db_prefix.$this->mTableName;
		$this->mColumn = array(
						'eid'=>'int(32) unsigned not null AUTO_INCREMENT',
						'name'=>'text not null',
						'description'=>'text null',
						'seq'=>'int(20) unsigned not null'
						);
		$this->mPrimaryKey = 'eid';
		$this->mOneToOne = null;
		$this->mOneToMore = array(
							'blogs.category'=>'categories.eid'
							);
		/*
			*****Changelog*****
			Add a new field at categories cache
			Datetime:2008-7-25 14:56:58
		*/
		$this->InitCache('categories.php',"select *,
							(select count(eid) from ".db_prefix."blogs where {$this->mFullTableName}.eid=category) blogcount
							from {$this->mFullTableName} order by seq asc");
	}
	/*
		获取一个记录
		@param eid int
		@return array
	*/
	public function GetOneCategory($eid)
	{
		$fields='*';
		$where_clause = "where eid = $eid";
		$this->Find($fields,$where_clause);
		return $this->GetOneRow();
	}

}
?>