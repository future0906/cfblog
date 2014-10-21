<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:All plugins interface
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*	2008-5-3	Start writing this file
*/
if(!defined('rootpath')) {//can't direct access this file
	die("Access forbidden.");
}

/*define plugins common method*/
abstract class PluginInterface{
	static private $plugin_manager;
	abstract public function initialize(&$plugin_manager);
}
?>