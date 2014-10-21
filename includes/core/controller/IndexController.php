<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:Index controller class.
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*   2008-1-17   Start writing this file
*   2008-8-8    Add 'resume' action
*BugFix:
*   2008-2-21   修复了发表评论时不会更新缓存的问题
*/
if(!defined('rootpath')) {//can't direct access this file
    die("Access forbidden.");
}
//include section
include_once rootpath.'/config/config.php';
include_once rootpath.'/includes/core/controller/BaseController.php';
include_once rootpath.'/includes/libs/Dbo.php';
include_once rootpath.'/includes/libs/smarttemplate/class.smarttemplate.php';
include_once rootpath.'/includes/libs/Common.php';

//define section
class IndexController extends BaseController
{
    /*
        构造函数
    */
    public function __construct()
    {
        $this->mEntrance = 'front';
        
        $this->mrDbo = &new DBO();
        $this->mrDbo->Connect();
        
        $this->mrView = &new SmartTemplate();
        $this->mrView->template_dir = (realpath(rootpath.'/tpl/Frontpage/'.blog_template_name.'/'));
        $this->mrView->temp_dir = realpath(rootpath.'/temp/');
        $this->mrVIew->cache_dir = realpath(rootpath.'/cache/smarttemplate').'/';
        
        $this->mAction = isset($_GET['action']) ? $_GET['action'] : 'index';
        $this->mMode = isset($_GET['mode']) ? $_GET['mode'] : 'normal';
        
        $this->mPageVar['param']['action'] = $this->mAction;
        $this->mPageVar['param']['mode'] = $this->mMode;
        
        $this->mPageVar['param']['param1'] =  isset($_GET['param1']) ? $_GET['param1'] : null;
        $this->mPageVar['param']['param2'] =  isset($_GET['param2']) ? $_GET['param2'] : null;
        //TODO:这里添加一个动态的菜单，让插件能修改首页的菜单项
    }
    /*
        控制器开始函数
    */
    public function Start()
    {
        $this->mStartTime = microtime(true);
        session_start();
        //启动缓冲区压缩
        if (system_gzip) {
            ob_start("ob_gzhandler");
        }
        else {
            ob_start();//关闭缓冲区压缩
        }
        //错误报告
        if (system_debug) {//如果处于调试模式，则报告所有的错误
            error_reporting(E_ALL);
        }
        else {//只显示严重的错误
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
        }
        date_default_timezone_set(system_timezone);//设置系统默认时区
        header('content-Type: text/html; charset=UTF-8');//UTF-8编码
    }
    /*
        控制器主体函数
    */
    public function doAction()
    {
        switch($this->mAction)
        {
            case 'index'://首页和日志暂时相等
            case 'blog':
                $this->doBlog();
                break;
            case 'category':
                $this->doCategory();
                break;
            case 'ccommit':
                $this->doCommentCommit();
                break;
            case 'photo':
                $this->doPhoto();
                break;
            case 'resume':
                $this->doResume();
                break;
            default:
                $this->Message("错误:未实现的action");
                break;
        }
    }
    /*
        控制器结束函数
    */
    public function End()
    {
        $this->mEndTime = microtime(true);
        $run_time = $this->mEndTime - $this->mStartTime;
        if(system_debug) {
            $this->mrView->assign('run_time',$run_time);
            $this->mrView->assign('query_times',$this->mrDbo->GetQueryCount());
        }
        if(null != $this->mrView->get_templatefile()) {
            $this->mrView->output();
        }
        ob_flush();
    }
    /*
        显示首页日志
    */
    private function doBlog()
    {
        include_once rootpath.'/includes/core/model/BlogModel.php';
        include_once rootpath.'/includes/core/model/CategoryModel.php';
        include_once rootpath.'/includes/core/model/CommentModel.php';
        include_once rootpath.'/includes/core/model/LinkModel.php';
        
        $blog = new BlogModel($this->mrDbo);
        $category = new CategoryModel($this->mrDbo);
        $comment = new CommentModel($this->mrDbo);
        $link = new LinkModel($this->mrDbo);
        
        //从缓存读取链接数据，并在视图层显示
        $this->mrView->assign('links',$link->GetCache());
        //从缓存读取分类数据，并在视图层显示
        $this->mrView->assign('categories',$category->GetCache());
        //从缓存读取最新评论，并在视图层显示
        $this->mrView->assign('comments',$comment->GetCache());
        //从缓存读取最新日志，并在视图层显示
        $this->mrView->assign('newestblog',$blog->GetCache());
        //从缓存读取最新文章，并在视图层显示
        //这里负责根据显示的模式和参数，提取相应的数据记录
        //        然后通过View 来显示出来。
        
        $mode = $this->mMode;
        switch($mode)
        {
            case 'normal':
                $current_page = null == $this->mPageVar['param']['param1'] ? 1 : intval($this->mPageVar['param']['param1']);//当前页
                if($current_page < 1) {
                    $this->Message('错误：页号不能少于1');
                    return;
                }
                //获取数据库记录，并在视图层显示
                $result = $blog->GetBlog($current_page);
                $blogcount = $blog->GetBlogCount();
                $multi_page = MultiPage($blogcount,blog_normal_count,$current_page);
                $http_get = $_GET;
                $http_get['mode'] = 'list';
                unset($http_get['param1']);
                $list_url = http_build_query($http_get);
                $this->mrView->assign('list_url',$list_url);
                //显示分页
                $this->mrView->assign('multi_page',$multi_page);
                $this->mrView->assign('blogs',$result);
                $this->mrView->set_templatefile('blog.html');
                break;
            
            case 'list':
                $current_page = null == $this->mPageVar['param']['param1'] ? 1 : intval($this->mPageVar['param']['param1']);//当前页
                if($current_page < 1) {
                    $this->Message('错误：页号不能少于1');
                    return;
                }               
                //以列表形式获取记录，并显示在视图中
                $result = $blog->GetBlog($current_page,BLOG_MODE_LIST);
                $blogcount = $blog->GetBlogCount();
                $multi_page = MultiPage($blogcount,blog_list_count,$current_page);
                $http_get = $_GET;
                $http_get['mode'] = 'normal';
                unset($http_get['param1']);
                $normal_url = http_build_query($http_get);

                $this->mrView->assign('normal_url',$normal_url);
                //显示分页
                $this->mrView->assign('multi_page',$multi_page);
                $this->mrView->assign('blogs_list',$result);
                $this->mrView->set_templatefile('list.html');
                break;

            case 'show':
                if(null == $this->mPageVar['param']['param1']) {//博客记录编号不能为空
                    $this->Message('参数错误，无法正确显示页面');
                    return;
                }
                $blog_eid = intval($this->mPageVar['param']['param1']);
                $blog_result = $blog->GetOneBlog($blog_eid);//获取日志内容
                
                if(!empty($blog_result['password'])) {
                    $password = $blog_result['password'];
                    if(empty($_POST['password'])) {
                        $this->Message('这篇文章需要密码才能访问');
                        return;
                    }
                    $in_password = md5($_POST['password']);
                    if($password != $in_password) {
                        $this->Message('密码错误');
                        return;
                    }
                }
                //生成验证码
                $_SESSION['robotcode'] = RandStr(5);//生成一个5字节验证码

                $blog_result['r_count'] = $blog_result['r_count'] + 1;
                $comment_result = $comment->GetCommentByBlog($blog_eid);
                $blog->AddRCount($blog_eid);
                $this->mrView->assign('blog_comments',$comment_result);
                $this->mrView->assign('blog_show',$blog_result);
                $this->mrView->assign('blog_id',$blog_eid);
                $this->mrView->set_templatefile('show.html');
                break;
        }       
        $this->mrView->assign('blogurl',system_blogurl);
        $this->mrView->assign('keywords',blog_keywords);
        $this->mrView->assign('description',blog_description);
        $this->mrView->assign('title',blog_title);
        $this->mrView->assign('templatename',blog_template_name);
        $this->mrView->assign('author',blog_author);
        $this->mrView->assign('blog_title',blog_title);
        $this->mrView->assign('blog_subtitle',blog_subtitle);
    }
    /*
        处理提交的评论
        将其保存在数据库中
    */
    private function doCommentCommit()
    {
        $blog_id = intval($_POST['blogid']);
        if(!get_magic_quotes_gpc()) {//Magic引用没有开启
            $nick_name = addslashes($_POST['nick_name']);
            $address = addslashes($_POST['address']);
            $content = addslashes($_POST['content']);
            $email = addslashes($_POST['email']);
            $robotcode = addslashes($_POST['robotcode']);
        }
        else {
            $nick_name = $_POST['nick_name'];
            $address = $_POST['address'];
            $content = $_POST['content'];
            $email = $_POST['email'];
            $robotcode = $_POST['robotcode'];
        }
        //检查必要的字段是否为空
        if(empty($blog_id) || empty($content) || empty($robotcode)) {
            $this->Message('提交失败,请填写内容和验证码内容.',"./?action=blog&mode=show&param1=$blog_id");
            $_SESSION['robotcode'] = RandStr(5);//重新生成验证码
            return;
        }
        //检查验证码
        if(strcasecmp($_SESSION['robotcode'], $robotcode) != 0) {
            $this->Message('验证码错误,请正确填写验证码.',"./?action=blog&mode=show&param1=$blog_id");
            $_SESSION['robotcode'] = RandStr(5);//重新生成验证码
            return;
        }
        //如果昵称为空,自动设置匿名
        if(empty($nick_name)) {
            $nick_name = '匿名';
        }
        //地址为空,设置默认
        if(empty($address)) {
            $address = 'http://www.cf-blog.net';
        }
        $ip = $_SERVER['REMOTE_ADDR'];//获取远程IP
        //新建一个记录
        $newcomment = array('blogid'=>"$blog_id",'content'=>"'$content'",
                            'nick_name'=>"'$nick_name'",'homepage'=>"'$address'",
                            'email'=>"'$email'",'pub_date'=>'now()','IP'=>"'$ip'"
                            );
        include_once rootpath.'/includes/core/model/CommentModel.php';
        $comment = new CommentModel($this->mrDbo);
        $comment->CommitComment($newcomment);
        $comment->UpdateCache();
        $_SESSION['robotcode'] = RandStr(5);//重新生成验证码
        $this->Message('评论发表成功......',"./?action=blog&mode=show&param1=$blog_id");
    }
    /*
        根据分类显示
    */
    private function doCategory()
    {
        include_once rootpath.'/includes/core/model/BlogModel.php';
        include_once rootpath.'/includes/core/model/CategoryModel.php';
        include_once rootpath.'/includes/core/model/CommentModel.php';
        include_once rootpath.'/includes/core/model/LinkModel.php';
        
        $blog = new BlogModel($this->mrDbo);
        $category = new CategoryModel($this->mrDbo);
        $comment = new CommentModel($this->mrDbo);
        $link = new LinkModel($this->mrDbo);

        //从缓存读取链接数据，并在视图层显示
        $this->mrView->assign('links',$link->GetCache());
        //从缓存读取分类数据，并在视图层显示
        $this->mrView->assign('categories',$category->GetCache());
        //从缓存读取最新评论，并在视图层显示
        $this->mrView->assign('comments',$comment->GetCache());
        //从缓存读取最新日志，并在视图层显示
        $this->mrView->assign('newestblog',$blog->GetCache());
        $category = intval($this->mPageVar['param']['param2']);
        if(empty($category)) {
            $this->Message('分类号不能为空');
            return 0;
        }

        $current_page = null == $this->mPageVar['param']['param1'] ? 1 : intval($this->mPageVar['param']['param1']);//当前页
        if($current_page < 1) {
            $this->Message('错误：页号不能少于1');
            return;
        }
        $mode = $this->mMode;
        switch($mode)
        {
            case 'normal':
                //获取数据库记录，并在视图层显示
                $result = $blog->GetBlog($current_page,BLOG_MODE_DETAIL,$category);
                $blogcount = $blog->GetBlogCount($category);
                $multi_page = MultiPage($blogcount,blog_normal_count,$current_page);
                $http_get = $_GET;
                $http_get['mode'] = 'list';
                unset($http_get['param1']);
                $list_url = http_build_query($http_get);

                $this->mrView->assign('list_url',$list_url);
                //显示分页
                $this->mrView->assign('multi_page',$multi_page);
                $this->mrView->assign('blogs',$result);
                $this->mrView->set_templatefile('blog.html');
                break;
            
            case 'list':
                //以列表形式获取记录，并显示在视图中
                $result = $blog->GetBlog($current_page,BLOG_MODE_LIST,$category);
                $blogcount = $blog->GetBlogCount($category);
                $multi_page = MultiPage($blogcount,blog_list_count,$current_page);
                $http_get = $_GET;
                $http_get['mode'] = 'normal';
                unset($http_get['param1']);
                $normal_url = http_build_query($http_get);

                $this->mrView->assign('normal_url',$normal_url);                
                //显示分页
                $this->mrView->assign('multi_page',$multi_page);
                $this->mrView->assign('blogs_list',$result);
                $this->mrView->set_templatefile('list.html');
                break;
            
            default:
                $this->Message('错误:未实现的模式');
                break;
        }
        $this->mrView->assign('blogurl',system_blogurl);
        $this->mrView->assign('keywords',blog_keywords);
        $this->mrView->assign('description',blog_description);
        $this->mrView->assign('title',blog_title);
        $this->mrView->assign('templatename',blog_template_name);
        $this->mrView->assign('author',blog_author);
        $this->mrView->assign('blog_title',blog_title);
        $this->mrView->assign('blog_subtitle',blog_subtitle);
    }
    /*
        照片显示函数
    */
    private function doPhoto()
    {
        $current_page = null == $this->mPageVar['param']['param1'] ? 1 : intval($this->mPageVar['param']['param1']);//当前页
        if($current_page < 1) {
                $this->Message('内部错误：页号不能少于1');
                return ;
        }

        include_once rootpath.'/includes/core/model/LocalPhoto.php';

        $photo = new LocalPhoto($this->mrDbo);

        $photo_list = $photo->GetPhotos($current_page);
        $photo_count = intval($photo->GetPhotoCount());
        $multi_page = MultiPage($photo_count,blog_photos_count,$current_page);

        $this->mrView->assign('photos',$photo_list);
        $this->mrView->assign('multi_page',$multi_page);
        $this->mrView->assign('photopath','.'.system_photopath);
        $this->mrView->assign('blogurl',system_blogurl);
        $this->mrView->assign('keywords',blog_keywords);
        $this->mrView->assign('description',blog_description);
        $this->mrView->assign('title',blog_title);
        $this->mrView->assign('templatename',blog_template_name);
        $this->mrView->assign('author',blog_author);
        $this->mrView->assign('blog_title',blog_title);
        $this->mrView->assign('blog_subtitle',blog_subtitle);
        $this->mrView->set_templatefile('photo.html');

    }
    /*
        显示简历
    */
    private function doResume()
    {

        include_once rootpath.'/includes/core/model/LinkModel.php';
        include_once rootpath.'/includes/core/model/BlogModel.php';

        $link = new LinkModel($this->mrDbo);
        $blog = new BlogModel($this->mrDbo);

        //从缓存读取链接数据，并在视图层显示
        $this->mrView->assign('links',$link->GetCache());
        //从缓存读取最新日志，并在视图层显示
        $this->mrView->assign('newestblog',$blog->GetCache());

        $this->mrView->assign('blogurl',system_blogurl);
        $this->mrView->assign('keywords',blog_keywords);
        $this->mrView->assign('description',blog_description);
        $this->mrView->assign('title',blog_title);
        $this->mrView->assign('templatename',blog_template_name);
        $this->mrView->assign('author',blog_author);
        $this->mrView->assign('blog_title',blog_title);
        $this->mrView->assign('blog_subtitle',blog_subtitle);

        $this->mrView->set_templatefile('resume.html');
    }
    /*
        显示消息函数
    */
    private function Message($msg,$redirect='./',$category='Message')
    {
        $this->mrView->set_templatefile('message.html');
        $this->mrView->assign('message',$msg);
        $this->mrView->assign('category',$category);
        $this->mrView->assign('redirect',$redirect);
    }
}
?>
