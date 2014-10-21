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
*	2008-2-12	修改了system_photopath环境变量，去掉最后的/
*/
if(!defined('rootpath')) {//can't direct access this file
	die("Access forbidden.");
}
define('blog_summary_allow_tags','<br><p><img>');
define('blog_summary_byte',1000);
define('blog_template_name','chris');
define('blog_normal_count',10);
define('blog_comment_adminlist_count',10);
define('blog_adminlist_count',10);
define('blog_draftlist_count',10);
define('blog_newest_count',10);
define('blog_list_count',40);
define('blog_photos_count',16);
define('blog_author','future0906');
define('blog_author_email','chris-future@gmail.com');
define('blog_title','阿肠\'s Blog');
define('blog_subtitle','处女座的男人');
define('blog_keywords','cfblog v2.0 beta 1');
define('blog_description','cfblog v2.0 beta 1');
define('blog_newcomment',5);
define('blog_comment_byte',14);
define('photo_google_username','future0906');

define('system_uploadroot','/upload/');
define('system_photopath','/upload/photo');
define('system_photosize',2048);
define('system_version','2.0.01 Beta 1');
define('system_blogurl','http://localhost:8080/cfblog/');
define('system_gzip',true);
define('system_cache_expire',20);
define('system_debug',true);
define('system_timezone','Asia/Hong_Kong');
?>