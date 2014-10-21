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
*	2008-2-2	Start writing this file
*/
//check is directly access
if(!defined('rootpath')) {
	die("Access denied");
}
//include section
include_once rootpath.'/includes/core/model/BaseModel.php';

//define section
define('USER_AUTHORITY_ADMIN',1);
define('USER_AUTHORITY_COMMENT',2);
/*
	用户管理类，提供删除，修改，增加，验证。
	该类是基于BaseModel提供的基本操作
	@package /includes/core/model/
	@copyright future0906
	@version 0.1.0	
*/
class UserModel extends BaseModel
{
	/*
		构造函数，设置数据库操作对象
		@param rDbo 数据库操作对象
		@return nul
	*/
	public function __construct(&$rDbo)
	{
		$this->mrDbo = &$rDbo;
		$this->mTableName = 'users';
		$this->mFullTableName = db_prefix.$this->mTableName;
		$this->mColumn = array(
								'eid'=>'int(32) unsigned not null primary key auto_increment',
								'loginid'=>'varchar(32) not null',
								'pwd'=>'char(32) not null',
								'regdate'=>'datetime not null',
								'nickname'=>'varchar(255)',
								'fullname'=>'varchar(255)',
								'email'=>'varchar(255)',
								'homepage'=>'varchar(255)',
								'authority'=>'int(32) not null default 0',
								'role'=>'int(32) not null default 0'
						);
		$this->mPrimaryKey = 'eid';
		$this->mOneToOne = null;
		$this->mOneToMore = null;
	}
	/*
		检查用户名、密码、权限是否匹配
		@param loginid string 登录ID
		@param pwd string 登录密码
		@param authority int 权限验证
		@return bool 验证是否成功
	*/
	public function Check($loginid,$pwd,$authority)
	{
		$fields = 'eid,authority';
		$where_clause = "where loginid = '$loginid' and pwd = '$pwd'";
		$this->Find($fields,$where_clause);
		$result = $this->GetOneRow();//获取查询结果
		if(null === $result) {//密码和用户名不正确
			return false;
		}
		//验证权限
		$user_authority = $result['authority'];
		if(($authority & $user_authority) != $authority) {
			return false;
		}
		return true;
	}
}
?>