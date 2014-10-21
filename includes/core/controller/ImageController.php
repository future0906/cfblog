<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:image controller,use to show some images
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*   2008-4-16   Start writing this file
*/
if(!defined('rootpath')) {//can't direct access this file
    die("Access forbidden.");
}

//include section
include_once rootpath.'/config/config.php';
include_once rootpath.'/includes/core/controller/BaseController.php';
include_once rootpath.'/includes/libs/Common.php';
//define section
class ImageController extends BaseController
{
    /*
        构造函数
    */
    public function __construct()
    {
        $this->mEntrance = 'image';
        
        $this->mAction = isset($_GET['action']) ? $_GET['action'] : 'robot';
        $this->mMode = isset($_GET['mode']) ? $_GET['mode'] : 'normal';
        
        $this->mPageVar['param']['action'] = $this->mAction;
        $this->mPageVar['param']['mode'] = $this->mMode;
        
    }
    /*
        开始函数
    */
    public function Start()
    {
        session_start();
        //错误报告
        if (system_debug) {//如果处于调试模式，则报告所有的错误
            error_reporting(E_ALL);
        }
        else {//只显示严重的错误
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
        }
        //设置HTTP头，输出图片
        header("Content-type: image/PNG");
        header('Cache-Control: no-cache');
    }
    /*
        控制器主体函数
    */
    public function doAction()
    {
        switch($this->mAction)
        {
            case 'robot':
                $this->doRobot();
                break;
            default:
                break;
        }
    }
    /*
        控制器结束函数
    */
    public function End()
    {
    }

    /*
        显示验证码函数
    */
    public function doRobot()
    {
        if ($this->mMode == 'renew') {
            $_SESSION['robotcode'] = RandStr(5);//重新生成验证
        }
        include_once rootpath.'/includes/libs/Robotimg.php';
    }
}
?>
