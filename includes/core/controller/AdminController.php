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
*	2008-1-17	Start writing this file
*	2008-2-12	Bugfix:上传图片后的上传菜单链接错误
*	2008-7-28	对action=category添加一个新的mode=addcat,用于显示页面表单
*/
if(!defined('rootpath')) {//can't direct access this file
	die("Access forbidden.");
}
//include_once section
include_once rootpath.'/config/config.php';
include_once rootpath.'/includes/core/controller/BaseController.php';
include_once rootpath.'/includes/libs/Common.php';
include_once rootpath.'/includes/libs/Dbo.php';
include_once rootpath.'/includes/libs/smarttemplate/class.smarttemplate.php';
/*
	后台页面控制器
	由于功能上有很多不同，因此与前台实现分开
*/
class AdminController extends BaseController
{
	/*
		构造函数
	*/
	public function __construct()
	{
		$this->mEntrance = 'admin';
		
		$this->mrDbo = &new DBO();
		$this->mrDbo->Connect();
		
		$this->mrView = &new SmartTemplate();
		$this->mrView->template_dir = (realpath(rootpath.'/tpl/Adminpage/'));
		$this->mrView->temp_dir = realpath(rootpath.'/temp/');
		$this->mrVIew->cache_dir = realpath(rootpath.'/cache/smarttemplate').'/';
		
		$this->mAction = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
		$this->mMode = isset($_GET['mode']) ? $_GET['mode'] : 'normal';
		
		$this->mPageVar['param']['action'] = $this->mAction;
		$this->mPageVar['param']['mode'] = $this->mMode;
		
		$this->mPageVar['param']['param1'] =  isset($_GET['param1']) ? $_GET['param1'] : null;
		$this->mPageVar['param']['param2'] =  isset($_GET['param2']) ? $_GET['param2'] : null;
		//TODO:这里添加一个动态的菜单，让插件能修改首页的菜单项
	}
	/*
		页面开始处理函数
	*/
	public function Start()
	{
		$this->mStartTime = microtime(true);
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
		session_start();
		date_default_timezone_set(system_timezone);//设置系统默认时区
		header('content-Type: text/html; charset=UTF-8');//UTF-8编码
	}
	/*
		页面结束处理函数
	*/
	public function End()
	{
		$this->mEndTime = microtime(true);
		$run_time = $this->mEndTime - $this->mStartTime;

		if(null != $this->mrView->get_templatefile()) {
			$this->mrView->output();
		}
		ob_flush();//输出内容
	}
	/*
		页面动作处理函数
	*/
	public function doAction()
	{
		//do action前一定要检查是否已经登录.
		$exempt_table = array('login');//豁免表，表示哪些action可以豁免登录
		if(!in_array($this->mAction,$exempt_table)) {
			if(!$this->CheckLogin()) {
				$this->Message('错误:请先登录','系统消息',array('登录'=>'./?go=admin&action=login'));
				return;
			}
		}
		switch($this->mAction)
		{
			case 'dashboard':
				$this->doDashboard();
				break;
			case 'login':
				$this->doLogin();
				break;
			case 'logout':
				$this->doLogout();
				break;
			case 'post':
				$this->doPost();
				break;
			case 'category':
				$this->doCategory();
				break;
			case 'comment':
				$this->doComment();
				break;
			case 'link':
				$this->doLink();
				break;
			case 'photo':
				$this->doPhoto();
				break;
            case 'system':
                $this->doSystem();
                break;
			case 'phpinfo':
				phpinfo();
				break;
			default:
				$this->Message('错误：未定义动作','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	'首页'=>'./?go=front'));
				break;
		}
	}
	/*
		登录页面
	*/
	private function doLogin()
	{
		if(empty($_POST)) {
			$this->mrView->set_templatefile('login.html');
			$this->mrView->assign('blogurl',system_blogurl);
		}
		else {
			if(empty($_POST['login_id']) || empty($_POST['password']) || empty($_POST['cookie'])) {
				$this->Message('错误:登录信息不完整','系统消息',array('登录'=>'./?go=admin&action=login'));
			}

			$loginid = get_magic_quotes_gpc() ? $_POST['login_id'] : addslashes($_POST['login_id']);
			$pwd = md5($_POST['password']);
			$expire = time() + intval($_POST['cookie']);//Cookie有效期
			
			include_once rootpath.'/includes/core/model/UserModel.php';
			$user = new UserModel($this->mrDbo);
			if($user->Check($loginid,$pwd,USER_AUTHORITY_ADMIN)) {
				//验证成功，设置Cookies、Session
				$_SESSION['logined'] = true;
				$_SESSION['loginid'] = $loginid;
				$_SESSION['pwd'] = $pwd;
				setcookie('loginid',$loginid,$expire);
				setcookie('pwd',$pwd,$expire);
				$this->Message('登录成功','系统消息',array('管理首页'=>'./?go=admin&action=dashboard'));
			}
			else {
				$this->Message('错误:登录失败，用户、密码、权限验证不正确','系统消息',array('登录'=>'./?go=admin&action=login'));
			}
		}
	}
	/*
		登出页面
	*/
	private function doLogout()
	{

		$_SESSION['logined'] = false;
		unset($_SESSION['logined']);
		unset($_SESSION['loginid']);
		unset($_SESSION['pwd']);
		setcookie('loginid',null,time());
		setcookie('pwd',null,time());
		$this->Message('注销成功','系统消息',
						array('管理首页'=>'./?go=admin&action=dashboard',
								'登录'=>'./?go=admin&action=login'));
	}
	/*
		后台管理首页
	*/
	private function doDashboard()
	{
		$system_info = array(
							array('text'=>'程序版本','value'=>system_version),
							array('text'=>'GZip压缩页面','value'=>system_gzip ? '开启' :'关闭'),
							array('text'=>'缓存时间','value'=>system_cache_expire."分钟"),
							array('text'=>'调试状态','value'=>system_debug ? '开启' :'关闭'),
							array('text'=>'时区','value'=>system_timezone)
						);
		//服务器信息
		$server_v['php_version'] = phpversion();
		$server_v['gd_version'] = current(gd_info());
		$server_v['mysql_version'] = $this->mrDbo->GetVersion();
		$server_v['os_version'] = PHP_OS;
		$server_v['apache_version'] = $_SERVER['SERVER_SOFTWARE'];
		$server_v['zend_version'] = zend_version();

		$this->mrView->set_templatefile('dashboard.html');
		$this->mrView->assign('blogurl',system_blogurl);
		$this->mrView->assign('system_info',$system_info);
		$this->mrView->assign('server_v',$server_v);
		$this->mrView->assign('blog_version',system_version);
	}
	/*
		文章管理
	*/
	private function doPost()
	{
		$mode = $this->mPageVar['param']['mode'];
		switch($mode)
		{
			case 'new':
				$this->doNewPost();
				break;
			case 'edit':
				$this->doEditPost();
				break;
			case 'manage':
			case 'normal':
				$this->doManagePost();
				break;
			case 'draft':
				$this->doDraftPost();
				break;
			case 'delete':
				$this->doDeletePost();
				break;
			case 'publish':
				$this->doPublishPost();
				break;
			default:
				$this->Message('错误：未定义模式','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	'首页'=>'./?go=front'));
				break;
		}
	}
	/*
		发表新文章
	*/
	private function doNewPost()
	{
		if(empty($_POST)) {//如果表单没有数据，表示
			include_once rootpath.'/includes/core/model/CategoryModel.php';
			$category = new CategoryModel($this->mrDbo);
			$this->mrView->set_templatefile('postblog.html');
			$this->mrView->assign('blogurl',system_blogurl);
			$this->mrView->assign('title',blog_title."---发表文章");
			$this->mrView->assign('category',$category->GetCache());
			$this->mrView->assign('blog_version',system_version);
		}
		else {
			include_once rootpath.'/includes/core/model/BlogModel.php';
			include_once rootpath.'/includes/core/model/CategoryModel.php';
			if(empty($_POST['title']) || empty($_POST['category']) || 
				empty($_POST['contents'])) {
				$this->Message('错误：表单参数不完整','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																		'发表文章'=>'./?go=admin&action=post&mode=new'));
				return false;
			}
			if(!get_magic_quotes_gpc()) {//处理表单数据，防止注入
				$newpost['title'] = "'".addslashes($_POST['title'])."'";//因数据为字符串，需要加单引号
				$newpost['content'] = "'".addslashes($_POST['contents'])."'";
			}
			else {
				$newpost['title'] = "'".$_POST['title']."'";
				$newpost['content'] = "'".$_POST['contents']."'";
			}
			//提取表单数据
			$newpost['on_top'] = (!empty($_POST['on_top']) && 'on' == $_POST['on_top']) ? 'true' : 'false';
			$newpost['c_allow'] = (!empty($_POST['c_allow']) && 'on' == $_POST['c_allow']) ? 'true' : 'false';
			$newpost['visible'] =(!empty($_POST['visible']) && 'on' == $_POST['visible']) ? 'true' : 'false';
			$newpost['draft'] = (!empty($_POST['draft']) && 'on' == $_POST['draft']) ? 'true' : 'false';
			$newpost['category'] = intval($_POST['category']);
			$newpost['password'] = empty($_POST['password']) ? 'null' : "'".md5($_POST['password'])."'";
			$newpost['createat'] = 'now()';
			$newpost['updateat'] = 'now()';

			$blog = new BlogModel($this->mrDbo);
            $category = new CategoryModel($this->mrDbo);

			$blog->Create($newpost);
			$blog->UpdateCache();
            $category->UpdateCache();
			$this->Message('发表文章成功','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
															'发表文章'=>'./?go=admin&action=post&mode=new'));
			return true;
		}
	}
	/*
		管理文章函数
	*/
	private function doManagePost()
	{
		include_once rootpath.'/includes/core/model/BlogModel.php';
		include_once rootpath.'/includes/core/model/CategoryModel.php';

		$blog = new BlogModel($this->mrDbo);
		$category = new CategoryModel($this->mrDbo);
		
		$category_id = null == $this->mPageVar['param']['param2'] ? null : intval($this->mPageVar['param']['param2']);//获取分类
		$current_page = null == $this->mPageVar['param']['param1'] ? 1 : intval($this->mPageVar['param']['param1']);//当前页
		if($current_page < 1) {
			$this->Message('错误：页号不能少于1','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	 '文章管理'=>'./?go=admin&action=post&mode=manage'));
			return;
		}
		$blogcount = $blog->GetBlogCount($category_id,BLOG_MODE_ADMINLIST);

		$multi_page = MultiPage($blogcount,blog_adminlist_count,$current_page);
		$blog_result = $blog->GetBlog($current_page,BLOG_MODE_ADMINLIST,$category_id);//获取博客文章

		$this->mrView->set_templatefile('managelog.html');
		$this->mrView->assign('blogurl',system_blogurl);
		$this->mrView->assign('title',blog_title.'---文章管理');
		$this->mrView->assign('blog_version',system_version);
		$this->mrView->assign('category',$category->GetCache());
		$this->mrView->assign('blog',$blog_result);
		$this->mrView->assign('multi_page',$multi_page);
	}
	/*
		草稿箱
	*/
	private function doDraftPost()
	{
		include_once rootpath.'/includes/core/model/BlogModel.php';

		$blog = new BlogModel($this->mrDbo);

		$current_page = null == $this->mPageVar['param']['param1'] ? 1 : intval($this->mPageVar['param']['param1']);//当前页
		if($current_page < 1) {
			$this->Message('错误：页号不能少于1','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	 '文章管理'=>'./?go=admin&action=post&mode=manage'));
			return;
		}
		$blogcount = $blog->GetBlogCount(null,BLOG_MODE_DRAFTLIST);

		$multi_page = MultiPage($blogcount,blog_draftlist_count,$current_page);
		$blog_result = $blog->GetBlog($current_page,BLOG_MODE_DRAFTLIST,null);//获取博客文章

		$this->mrView->set_templatefile('draft.html');
		$this->mrView->assign('blogurl',system_blogurl);
		$this->mrView->assign('title',blog_title.'---草稿箱');
		$this->mrView->assign('blog_version',system_version);
		$this->mrView->assign('blog',$blog_result);
		$this->mrView->assign('multi_page',$multi_page);
	}
	/*
		删除文章
	*/
	private function doDeletePost()
	{
		include_once rootpath.'/includes/core/model/BlogModel.php';
		include_once rootpath.'/includes/core/model/CategoryModel.php';

		if (!empty($_POST)) {//判读POST是否为空?
			//将提交的ID转换为内部的整形数组
			$selection = $_POST['selection'];
			$selectedpost = array();
			foreach ($selection as $oneSelect) {
				$selectedpost[]['eid'] = intval($oneSelect);
			}
			$blog = new BlogModel($this->mrDbo);
            $category = new CategoryModel($this->mrDbo);

			$blog->DeleteBatch($selectedpost);
            $blog->UpdateCache();
            $category->UpdateCache();
			$this->Message('文章批量删除成功','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
															 '文章管理'=>'./?go=admin&action=post&mode=manage'));
		}
		else {
			$blog_eid = null == $this->mPageVar['param']['param1'] ? null : intval($this->mPageVar['param']['param1']);//当前页
			if(null == $blog_eid) {
				$this->Message('错误：文章编号不能为空','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																		 '文章管理'=>'./?go=admin&action=post&mode=manage'));
				return false;
			}
			$blog = new BlogModel($this->mrDbo);
            $category = new CategoryModel($this->mrDbo);

			$blog_array = array('eid'=>$blog_eid);
			$blog->Delete($blog_array);
            $blog->UpdateCache();
            $category->UpdateCache();
			$this->Message('文章删除成功','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
															 '文章管理'=>'./?go=admin&action=post&mode=manage'));
		}
	}
	/*
		编辑文章
	*/
	private function doEditPost()
	{
		$blog_eid = null == $this->mPageVar['param']['param1'] ? null : intval($this->mPageVar['param']['param1']);//当前页
		if(null == $blog_eid) {//检查文章编号
			$this->Message('错误：文章编号不能为空','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	 '文章管理'=>'./?go=admin&action=post&mode=manage'));
			return false;
		}
		include_once rootpath.'/includes/core/model/BlogModel.php';
		$blog = new BlogModel($this->mrDbo);

		if(empty($_POST)) {//表单数据为空
			include_once rootpath.'/includes/core/model/CategoryModel.php';
			$category = new CategoryModel($this->mrDbo);
			$blog_result = $blog->GetOneBlog($blog_eid,false,BLOG_MODE_ADMIN);

			$this->mrView->assign('blogurl',system_blogurl);
			$this->mrView->assign('title',blog_title.'---修改文章');
			$this->mrView->assign('blog',$blog_result);
			$this->mrView->assign('blog_version',system_version);
			$this->mrView->assign('category',$category->GetCache());
			$this->mrView->set_templatefile('editblog.html');
			return true;
		}
		else {
			if(empty($_POST['title']) || empty($_POST['category']) || 
				empty($_POST['contents'])) {
				$this->Message('错误：表单参数不完整','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																		'发表文章'=>'./?go=admin&action=post&mode=new'));
				return false;
			}
			if(!get_magic_quotes_gpc()) {//处理表单数据，防止注入
				$updatepost['title'] = "'".addslashes($_POST['title'])."'";//因数据为字符串，需要加单引号
				$updatepost['content'] = "'".addslashes($_POST['contents'])."'";
			}
			else {
				$updatepost['title'] = "'".$_POST['title']."'";
				$updatepost['content'] = "'".$_POST['contents']."'";
			}
			//提取表单数据
			$updatepost['on_top'] = (!empty($_POST['on_top']) && 'on' == $_POST['on_top']) ? 'true' : 'false';
			$updatepost['c_allow'] = (!empty($_POST['c_allow']) && 'on' == $_POST['c_allow']) ? 'true' : 'false';
			$updatepost['visible'] =(!empty($_POST['visible']) && 'on' == $_POST['visible']) ? 'true' : 'false';
			$updatepost['draft'] = (!empty($_POST['draft']) && 'on' == $_POST['draft']) ? 'true' : 'false';
			$updatepost['category'] = intval($_POST['category']);
			$updatepost['password'] = empty($_POST['password']) ? ('on' == $_POST['deletepwd'] ? 'null' : null ) : "'".md5($_POST['password'])."'";
			$updatepost['updateat'] = 'now()';

			if(null == $updatepost['password']) {
				unset($updatepost['password']);
			}

			$updatepost['eid'] = $blog_eid;
			$blog->Save($updatepost);
			$this->Message('文章修改成功','系统消息',array('文章管理'=>'./?go=admin&action=post&mode=manage',
															'发表文章'=>'./?go=admin&action=post&mode=new'));
			return true;
		}
	}
	/*
		发布文章
	*/
	private function doPublishPost()
	{
		$blog_eid = null == $this->mPageVar['param']['param1'] ? null : intval($this->mPageVar['param']['param1']);//当前页
		if(null == $blog_eid) {//检查文章编号
			$this->Message('错误：文章编号不能为空','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	 '文章管理'=>'./?go=admin&action=post&mode=manage'));
			return false;
		}
		include_once rootpath.'/includes/core/model/BlogModel.php';
		$blog = new BlogModel($this->mrDbo);
		$blog->PublishPost($blog_eid);
		$this->Message('文章发布成功','系统消息',array('文章管理'=>'./?go=admin&action=post&mode=manage',
														'发表文章'=>'./?go=admin&action=post&mode=new'));
		return true;
	}
	/*
		分类操作
	*/
	private function doCategory()
	{
		$mode = $this->mPageVar['param']['mode'];
		switch($mode)
		{
			case 'normal':
			case 'manage':
				$this->doManageCategory();
				break;
			case 'addcat':
				$this->dAddCategory();
				break;
			case 'new':
				$this->doNewCategory();
				break;
			case 'delete':
				$this->doDeleteCategory();
				break;
			case 'edit':
				$this->doEditCategory();
				break;
			default:
				$this->Message('错误：未定义模式','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	'首页'=>'./?go=front'));
				break;
		}
	}
	/*
		分类管理
	*/
	private function doManageCategory()
	{
		include_once rootpath.'/includes/core/model/CategoryModel.php';
		$category = new CategoryModel($this->mrDbo);

		$this->mrView->set_templatefile('managecategory.html');
		$this->mrView->assign('blogurl',system_blogurl);
		$this->mrView->assign('title',blog_title.'---管理分类');
		$this->mrView->assign('blog_version',system_version);
		$this->mrView->assign('category',$category->GetCache());
	}
	/*
		删除分类
	*/
	private function doDeleteCategory()
	{
		include_once rootpath.'/includes/core/model/CategoryModel.php';		
		if (!empty($_POST)) {//判读POST是否为空?
			//将提交的ID转换为内部的整形数组
			$selection = $_POST['selection'];
			$selectedcategory = array();
			foreach ($selection as $oneSelect) {
				$selectedcategory[]['eid'] = intval($oneSelect);
			}

			$category = new CategoryModel($this->mrDbo);
			$category->DeleteBatch($selectedcategory);
			$category->UpdateCache();//更新缓存
			
			$this->Message('分类批量删除成功','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
															'分类管理'=>'./?go=admin&action=category'));
		}
		else {
			$category_eid = null == $this->mPageVar['param']['param1'] ? null : intval($this->mPageVar['param']['param1']);//分类号
			if(null == $category_eid) {//检查文章编号
				$this->Message('错误：文章编号不能为空','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																		 '分类管理'=>'./?go=admin&action=category'));
				return false;
			}
			$category = new CategoryModel($this->mrDbo);
			$category->Delete(array('eid'=>$category_eid));
			$category->UpdateCache();//更新缓存
			$this->Message('分类已删除','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
															'分类管理'=>'./?go=admin&action=category'));
		}
		return true;
	}
	/*
		显示创建分类页面
	*/
	private function dAddCategory()
	{
		$this->mrView->set_templatefile('addcategory.html');
		$this->mrView->assign('blogurl',system_blogurl);
		$this->mrView->assign('title',blog_title.'---管理分类');
		$this->mrView->assign('blog_version',system_version);
	}
	/*
		新建分类
	*/
	private function doNewCategory()
	{
		include_once rootpath.'/includes/core/model/CategoryModel.php';
		$category = new CategoryModel($this->mrDbo);

		$newcategory['name'] = "'".(get_magic_quotes_gpc() ? $_POST['name'] : addslashes($_POST['name']))."'";
		$newcategory['seq'] = intval($_POST['seq']);

		$category->Save($newcategory);//保存数据库
		$category->UpdateCache();//更新缓存
		$this->Message('分类创建成功','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
														'分类管理'=>'./?go=admin&action=category'));
	}
	/*
		编辑分类
	*/
	private function doEditCategory()
	{
		$category_eid = null == $this->mPageVar['param']['param1'] ? null : intval($this->mPageVar['param']['param1']);//分类号
		if(null == $category_eid) {//检查分类编号
			$this->Message('错误：文章编号不能为空','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	 '分类管理'=>'./?go=admin&action=category'));
			return false;
		}
		include_once rootpath.'/includes/core/model/CategoryModel.php';//分类
		$category = new CategoryModel($this->mrDbo);

		if(empty($_POST)) {
			$result = $category->GetOneCategory($category_eid);

			$this->mrView->set_templatefile('editcategory.html');
			$this->mrView->assign('blogurl',system_blogurl);
			$this->mrView->assign('title',blog_title.'---管理分类');
			$this->mrView->assign('blog_version',system_version);
			$this->mrView->assign('category',$result);
		}
		else {
			$newcategory['name'] = "'".(get_magic_quotes_gpc() ? $_POST['name'] : addslashes($_POST['name']))."'";
			$newcategory['seq'] = intval($_POST['seq']);
			$newcategory['eid'] = $category_eid;

			$category->Save($newcategory);//保存数据库
			$category->UpdateCache();//更新缓存
			$this->Message('分类修改成功','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
															'分类管理'=>'./?go=admin&action=category'));
		}
	}
	/*
		评论操作
	*/
	private function doComment()
	{
		$mode = $this->mPageVar['param']['mode'];
		switch($mode)
		{
			case 'normal':
			case 'manage':
				$this->doManageComment();
				break;
			case 'delete':
				$this->doDeleteComment();
				break;
			case 'reply':
				$this->doReplyComment();
				break;
			default:
				$this->Message('错误：未定义模式','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	'首页'=>'./?go=front'));
				break;
		}
	}
	/*
		评论管理
	*/
	private function doManageComment()
	{
		include_once rootpath.'/includes/core/model/CommentModel.php';

		$comment = new CommentModel($this->mrDbo);

		$current_page = null == $this->mPageVar['param']['param1'] ? 1 : intval($this->mPageVar['param']['param1']);//当前页
		if($current_page < 1) {
			$this->Message('错误：页号不能少于1','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	 '文章管理'=>'./?go=admin&action=comment&mode=manage'));
			return;
		}
		$commentcount = $comment->GetCommentCount();

		$multi_page = MultiPage($commentcount,blog_comment_adminlist_count,$current_page);
		$comment_result = $comment->GetComment($current_page);//获取博客文章

		$this->mrView->set_templatefile('managecomment.html');
		$this->mrView->assign('blogurl',system_blogurl);
		$this->mrView->assign('title',blog_title.'---管理评论');
		$this->mrView->assign('blog_version',system_version);
		$this->mrView->assign('comment',$comment_result);
		$this->mrView->assign('multi_page',$multi_page);

	}
	/*
		删除评论
	*/
	private function doDeleteComment()
	{
		include_once rootpath.'/includes/core/model/CommentModel.php';
		if (!empty($_POST)) {//判读POST是否为空?
			//将提交的ID转换为内部的整形数组
			$selection = $_POST['selection'];
			$selectedcomment = array();
			foreach ($selection as $oneSelect) {
				$selectedcomment[]['eid'] = intval($oneSelect);
			}

			$comment = new CommentModel($this->mrDbo);
			$comment->DeleteBatch($selectedcomment);
			$comment->UpdateCache();//更新缓存
			
			$this->Message('评论批量删除成功','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
														'评论管理'=>'./?go=admin&action=comment'));
		}
		else {
			$comment_eid = null == $this->mPageVar['param']['param1'] ? null : intval($this->mPageVar['param']['param1']);//分类号
			if(null == $comment_eid) {//检查评论编号
				$this->Message('错误：文章编号不能为空','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																		 '评论管理'=>'./?go=admin&action=comment'));
				return false;
			}
			$comment = new CommentModel($this->mrDbo);
			$comment->Delete(array('eid'=>$comment_eid));
			$comment->UpdateCache();//更新缓存
			$this->Message('评论已删除','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
														'评论管理'=>'./?go=admin&action=comment'));
		}
		return true;
	}
	/*
		回复评论
	*/
	private function doReplyComment()
	{
		$comment_eid = null == $this->mPageVar['param']['param1'] ? null : intval($this->mPageVar['param']['param1']);//评论号
		if(null == $comment_eid) {//检查评论编号
			$this->Message('错误：文章编号不能为空','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	 '评论管理'=>'./?go=admin&action=comment'));
			return false;
		}
		include_once rootpath.'/includes/core/model/CommentModel.php';
		$comment = new CommentModel($this->mrDbo);
		if(empty($_POST)) {
			$content = $comment->GetContent($comment_eid);
			$this->mrView->set_templatefile('replycomment.html');
			$this->mrView->assign('blogurl',system_blogurl);
			$this->mrView->assign('title',blog_title.'---回复评论');
			$this->mrView->assign('content',$content);
			$this->mrView->assign('blog_version',system_version);
			return true;
		}
		else {
			$reply = (get_magic_quotes_gpc() ? $_POST['reply'] : addslashes($_POST['reply']));
			$comment->Reply($comment_eid,$reply);//回复评论
			$this->Message('回复成功','系统信息',array('管理首页'=>'./?go=admin&action=dashboard',
														'评论管理'=>'./?go=admin&action=comment'));
			return true;
		}
	}
	/*
		链接操作
	*/
	private function doLink()
	{
		$mode = $this->mPageVar['param']['mode'];
		switch($mode)
		{
			case 'new':
				$this->doNewLink();
				break;
			case 'normal':
			case 'manage':
				$this->doManageLink();
				break;
			case 'delete':
				$this->doDeleteLink();
				break;
			case 'edit':
				$this->doEditLink();
				break;
			default:
				$this->Message('错误：未定义模式','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	'首页'=>'./?go=front'));
				break;
		}		
	}
	/*
		链接管理
	*/
	private function doManageLink()
	{
		include_once rootpath.'/includes/core/model/LinkModel.php';

		$link = new LinkModel($this->mrDbo);

		$this->mrView->set_templatefile('managelink.html');
		$this->mrView->assign('blogurl',system_blogurl);
		$this->mrView->assign('title',blog_title.'---链接管理');
		$this->mrView->assign('link',$link->GetCache());
		$this->mrView->assign('blog_version',system_version);
	}
	/*
		删除链接
	*/
	private function doDeleteLink()
	{
		include_once rootpath.'/includes/core/model/LinkModel.php';
		if (!empty($_POST)) {//判读POST是否为空?
			//将提交的ID转换为内部的整形数组
			$selection = $_POST['selection'];
			$selectedlink = array();
			foreach ($selection as $oneSelect) {
				$selectedlink[]['eid'] = intval($oneSelect);
			}

			$link = new LinkModel($this->mrDbo);
			$link->DeleteBatch($selectedlink);
			$link->UpdateCache();//更新缓存
			
			$this->Message('链接批量删除成功','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
															'链接管理'=>'./?go=admin&action=link'));
		}

		else {
			$link_eid = null == $this->mPageVar['param']['param1'] ? null : intval($this->mPageVar['param']['param1']);
			if(null == $link_eid) {
				$this->Message('错误：文章编号不能为空','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																		 '链接管理'=>'./?go=admin&action=link'));
				return false;
			}
			
			$link = new LinkModel($this->mrDbo);

			$link->Delete(array('eid'=>$link_eid));//删除链接
			$link->UpdateCache();
			$this->Message('链接已删除','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
															'链接管理'=>'./?go=admin&action=link'));
		}
		return true;
	}
	/*
		新建链接
	*/
	private function doNewLink()
	{
		if(empty($_POST)) {
			$this->mrView->set_templatefile('newlink.html');
			$this->mrView->assign('blogurl',system_blogurl);
			$this->mrView->assign('title',blog_title.'---新建链接');
			$this->mrView->assign('blog_version',system_version);
		}
		else {
			if(empty($_POST['name']) || empty($_POST['url']) || empty($_POST['group'])) {
				$this->Message('错误：表单参数不完整','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																		'链接管理'=>'./?go=admin&action=link&mode=new'));
				return false;
			}
			if(!get_magic_quotes_gpc()) {
				$newlink['name'] = "'".addslashes($_POST['name'])."'";
				$newlink['url'] = "'".addslashes($_POST['url'])."'";
			}
			else {
				$newlink['name'] = "'".$_POST['name']."'";
				$newlink['url'] = "'".$_POST['url']."'";
			}
			$newlink['groupid'] = intval($_POST['group']);//链接所属组

			if (2 == $newlink['groupid'] = intval($_POST['group'])) {
				$filePath = UploadFile('logo','logos',array('jpg','gif','png'));//上传logo到/upload/logos/
				if (!$filePath) {//文件上传失败
					$this->Message('错误：文件上传失败','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																			'链接管理'=>'./?go=admin&action=link&mode=new'));
					return false;
				}
				$newlink['logo'] = "'".addslashes($filePath)."'";
			}

			include_once rootpath.'/includes/core/model/LinkModel.php';
			
			$link = new LinkModel($this->mrDbo);
			$link->Save($newlink);
			$link->UpdateCache();
			$this->Message('链接已创建','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
															'链接管理'=>'./?go=admin&action=link'));
		}
	}
	/*
		编辑链接
	*/
	private function doEditLink()
	{
		$link_eid = null == $this->mPageVar['param']['param1'] ? null : intval($this->mPageVar['param']['param1']);
		if(null == $link_eid) {
			$this->Message('错误：文章编号不能为空','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	 '链接管理'=>'./?go=admin&action=link'));
			return false;
		}
		include_once rootpath.'/includes/core/model/LinkModel.php';
		$link = new LinkModel($this->mrDbo);

		if(empty($_POST)) {
			$onelink = $link->GetOneLink($link_eid);

			$this->mrView->set_templatefile('editlink.html');
			$this->mrView->assign('blogurl',system_blogurl);
			$this->mrView->assign('title',blog_title.'---修改链接');
			$this->mrView->assign('link',$onelink);
			$this->mrView->assign('blog_version',system_version);
		}
		else {
			if(empty($_POST['name']) || empty($_POST['url'])) {
				$this->Message('错误：表单参数不完整','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																		'链接管理'=>'./?go=admin&action=link'));
				return false;
			}
			if(!get_magic_quotes_gpc()) {
				$newlink['name'] = "'".addslashes($_POST['name'])."'";
				$newlink['url'] = "'".addslashes($_POST['url'])."'";
				$newlink['description'] = empty($_POST['desc']) ? 'null' : "'".addslashes($_POST['desc'])."'";
			}
			else {
				$newlink['name'] = "'".$_POST['name']."'";
				$newlink['url'] = "'".$_POST['url']."'";
				$newlink['description'] = empty($_POST['desc']) ? 'null' : "'".addslashes($_POST['desc'])."'";
			}
			$newlink['seq'] = intval($_POST['seq']);
			$newlink['eid'] = $link_eid;

			$link->Save($newlink);
			$link->UpdateCache();
			$this->Message('链接已修改','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
															'链接管理'=>'./?go=admin&action=link'));
		}
	}
	/*
		图片管理
	*/
	private function doPhoto()
	{
		$mode = $this->mPageVar['param']['mode'];
		switch($mode)
		{
			case 'normal':
			case 'upload':
				$this->doUploadPhoto();
				break;
			default:
				$this->Message('错误：未定义模式','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	'首页'=>'./?go=front'));
				break;
		}		
	}
	/*

	*/
	private function doUploadPhoto()
	{
		if(empty($_POST)) {
			$this->mrView->set_templatefile('uploadphoto.html');
			$this->mrView->assign('blogurl',system_blogurl);
			$this->mrView->assign('title',blog_title.'---上传图片');
			$this->mrView->assign('blog_version',system_version);
		}
		else {
			include_once rootpath.'/includes/core/model/LocalPhoto.php';
			$photo = new LocalPhoto($this->mrDbo);
			if($photo->Upload()) {
				$this->Message('文件上传成功','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	 '上传'=>'./?go=admin&action=photo&mode=upload'));
			}
			else {
				$this->Message('错误：文件上传失败','系统消息',array('管理首页'=>'./?go=admin&action=dashboard',
																	 '上传'=>'./?go=admin&action=photo&mode=upload'));
			}			
		}
	}
    /*
        系统设置函数 
    */
    private function doSystem()
    {
        include_once rootpath.'/includes/libs/ConfigUtil.php'; 
        $this->mrView->set_templatefile('setting.html');
        $this->mrView->assign('configs', $config_column);
        $this->mrView->assign('blogurl',system_blogurl);
        $this->mrView->assign('title',blog_title.'---系统设置');
        $this->mrView->assign('blog_version',system_version);
    }
	/*
		显示消息函数
	*/
	private function Message($message,$category,$shortcut)
	{
		$this->mrView->set_templatefile('msg_short.html');
		$this->mrView->assign('blogurl',system_blogurl);
		$this->mrView->assign('category',$category);
		$this->mrView->assign('message',$message);
		foreach($shortcut as $text=>$url) {
			$shortcut_array[] = array('text'=>$text,'url'=>$url);
		}
		$this->mrView->assign('shortcut',$shortcut_array);
	}
	/*
		检查登录函数
		先检查Session再检查Cookies
		成功返回true，失败返回false
	*/
	private function CheckLogin()
	{
		//判断是否已经登录过,检查Session
		if(isset($_SESSION['logined']) && (true == $_SESSION['logined'])) {
			return true;
		}
		//再检查Cookie是否有保存密码和用户名
		if(isset($_COOKIE['loginid']) && isset($_COOKIE['pwd'])) {
			$loginid = get_magic_quotes_gpc() ? $_COOKIE['loginid'] : addslashes($_COOKIE['loginid']);
			$pwd = md5($_COOKIE['pwd']);
			include_once rootpath.'/includes/core/model/UserModel.php';
			$user = new UserModel($this->mrDbo);
			return $user->Check($loginid,$pwd,USER_AUTHORITY_ADMIN);
		}
		return false;
	}
}
?>
