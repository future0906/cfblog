<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:Config util files
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*/
if(!defined('rootpath')) {//can't direct access this file
	die("Access forbidden.");
}
//属性设置的所有列
//格式：属性名,输入方式,默认值
$config_column = array(
    array('name'=>'blog_summary_allow_tags','dis_name'=>'摘要允许标签','type'=>'TextLine','cur'=>constant('blog_summary_allow_tags')),
    array('name'=>'blog_summary_byte','dis_name'=>'摘要显示字节数','type'=>'Integer', 'cur'=>constant('blog_summary_byte')),
    /*
    array('blog_template_name','TemplateChoice','chris'),
    array('blog_normal_count','Integer',10),
    array('blog_comment_adminlist_count','Integer',10),
    array('blog_adminlist_count','Integer',10),
    array('blog_draftlist_count','Integer',10),
    array('blog_newest_count','Integer',10),
    array('blog_list_count','Integer',40),
    array('blog_photos_count','Integer',16),
    array('blog_author','TextLine', 'future0906'),
    array('blog_author_email','TextLine','chris-future@gmail.com'),
    array('blog_title','TextLine','阿肠\'s Blog'),
    array('blog_subtitle','TextLine','处女座的男人'),
    array('blog_keywords','TextLine','cfblog v2.0 beta 1'),
    array('blog_description','TextLine','cfblog v2.0 beta 1'),
    array('blog_newcomment','Integer',5),
    array('blog_comment_byte','Integer',14),
    array('photo_google_username','TextLine','future0906'),
    
    array('system_uploadroot','TextLine','/upload/'),
    array('system_photopath','TextLine','/upload/photo'),
    array('system_photosize','Integer',2048),
    array('system_version','TextLine','2.0.01 Beta 1'),
    array('system_blogurl','TextLine', 'http://localhost:8080/cfblog/'),
    array('system_gzip','Boolean',true),
    array('system_cache_expire','Integer',20),
    array('system_debug','Boolean', true),
    array('system_timezone','TextLine','Asia/Hong_Kong'),
     */
);

/*
*/
function GenerateFile($config)
{
    CheckConfig($config);
}
/*
    配置检查函数
*/
function CheckConfig($config)
{

}
