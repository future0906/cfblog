<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:Cfblog2 main entrance.Also this is the only entrance of this blog
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*	2007-11-26	Start writing this file
*/
//define section
define('rootpath',dirname(__FILE__));//indicate the programme path,this would be use in other php file.
//var initialize section
$entrance	= isset($_GET['go']) ? $_GET['go'] : 'front';//get entrance,if can't get any entrance default is index page
$controller	= null;

//将请求传送到相应控制器
//若控制器较多，调度比较复杂，应该实现一个调度器dispatcher
//TODO:实现一个调度器

switch($entrance)
{
	case 'front'://if is a front page,create IndexController
		include_once rootpath.'/includes/core/controller/IndexController.php';
		$controller = new IndexController();
		break;
	case 'admin'://admin,create AdminController
		include_once rootpath.'/includes/core/controller/AdminController.php';
		$controller = new AdminController();
		break;
	case 'image'://course image use different header,so we need a new controller
		include_once rootpath.'/includes/core/controller/ImageController.php';
		$controller = new ImageController();
		break;
	default://not implements controller,print error message
		die("未实现的控制器,请求页面失败");
		break;
}

//start page handling
$controller->Start();
$controller->doAction();
$controller->End();
//EOF
?>