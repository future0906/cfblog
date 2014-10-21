<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:友情连接的模型类
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*	2007-12-3	Start writing this file
*	2008-8-1	Change cache initialize SQL
*/
//check is directly access
if(!defined('rootpath')) {
	die("Access denied");
}
//include section
include_once rootpath.'/includes/core/model/BaseModel.php';
//define section
/*
	友情链接操作模型，通过该模型，可以对友情链接进行增删改的操作
	该类是基于BaseModel提供的基本操作
	@package /includes/core/model/
	@copyright future0906
	@version 0.1.0
*/
class LinkModel extends BaseModel
{
	/*
		构造函数，要扩展BaseModel，必须在构造函数中把BaseModel的各属性填写好
		@param rDbo 数据库操作对象
		@return nul
	*/
	public function __construct(&$rDbo)
	{
		$this->mrDbo = &$rDbo;
		$this->mTableName = 'links';
		$this->mFullTableName = db_prefix.$this->mTableName;
		$this->mColumn = array(
						'eid'=>'int(32) unsigned AUTO_INCREMENT',
						'url'=>'TEXT',
						'name'=>'TEXT',
						'logo'=>'TEXT',
						'visible'=>'boolean',
						'groupid'=>'int(32) unsigned'
						);
		$this->mPrimaryKey = 'eid';
		$this->mOneToOne = null;
		$this->mOneToMore = null;
		$this->InitCache('links.php',"select * from {$this->mFullTableName} order by groupid");
	}
	/*
		获取某一个链接
		@param eid 链接编号
	*/
	public function GetOneLink($eid)
	{
		$fields = '*';
		$where_clause = "where eid = $eid";
		$this->Find($fields,$where_clause);
		return $this->GetOneRow();
	}
}
?>