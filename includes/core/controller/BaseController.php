<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:all controller base class,defined some common method and member variable
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*	2007-11-26	Start writing this file
*/
if(!defined('rootpath')) {//can't direct access this file
	die("Access forbidden.");
}
//define section
abstract class BaseController
{
	/*
		入口名，仅用于记录该控制器由哪个入口进入
		@var string
	*/
	protected $mEntrance;
	/*
		记录控制器开始时间
		@var integer
	*/
	protected $mStartTime;
	/*
		记录控制器结束时间
		@var integer
	*/
	protected $mEndTime;
	/*
		控制器对应的视图
		@var object
	*/
	protected $mrView;
	/*
		数据库操作对象
		@var object
	*/
	protected $mrDbo;
	/*
		控制器的动作
		@var string
	*/
	protected $mAction;
	/*
		控制器的工作模式
		@var string
	*/
	protected $mMode;
	/*
		页面变量
		@var array
	*/
	protected $mPageVar;
	/*
		函数接口
	*/
	abstract public function Start();
	abstract public function doAction();
	abstract public function End();
}
//EOF
?>