<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:Some useful function
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*	2007-1-20	Start writing this file
*/
if(!defined('rootpath')) {//can't direct access this file
	die("Access forbidden.");
}
/*
	分页函数
*/
function MultiPage($count,$viewperpage,$current_page)
{
	$total_page = ceil($count/$viewperpage);//计算一共有多少页
	$multi_page = "共<span class=\"red\">$total_page</span>页&nbsp;&nbsp;";
	
	$multi_page .= "第<span class=\"red\">$current_page</span>页 &nbsp;&nbsp;";
	$http_get = $_GET;
	$http_get['param1'] = 1;
	$http_query = http_build_query($http_get);
	$multi_page .= " <a href=\"./?$http_query\">第一页</a>&nbsp;&nbsp;";
	
	if($current_page > 1) {//如果不是第一页
		$http_get['param1'] = $current_page - 1;
		$http_query = http_build_query($http_get);		
		$multi_page .= " <a href=\"./?$http_query\">上一页</a>&nbsp;&nbsp;";
	}
	if($current_page < $total_page) {//如果不是最后一页
		$http_get['param1'] = $current_page + 1;
		$http_query = http_build_query($http_get);		
		$multi_page .= " <a href=\"./?$http_query\">下一页</a>&nbsp;&nbsp;";
	}
	
	$http_get['param1'] = $total_page;
	$http_query = http_build_query($http_get);
	$multi_page .= " <a href=\"./?$http_query\"> 最后一页</a>&nbsp;&nbsp;";
	
	return $multi_page;
}
/*
	随机生成一个验证码
*/
function RandStr($length)
{
	srand();//种子
	$charTable = array('0','1','2','3','4','5','6','7','8','9',
						'a','b','c','d','e','f','g','h','i','j',
						'k','l','m','n','o','p','q','r','s','t',
						'u','v','w','x','y','z','A','B','C','D',
						'E','F','G','H','I','J','K','L','M','N',
						'O','P','Q','R','S','T','U','V','W','X',
						'Y','Z');
	$retstr = "";
	for($i = 0;$i < $length;$i++) {
		$retstr .= $charTable[rand(0,61)];
	}
	return $retstr;
}
/*
	上传文件
	@formName 文件上传时表单的名字
	@relativePath 上传文件保存的相对路径,相对于uploadroot
	@single 是否单个文件
	@return 成功则返回一个数组或一个字符串,包含每个文件相对于程序根目录的路径,否则返回false
*/
function UploadFile($formName,$relativePath,$acceptExt,$single=true)
{
	$uppath = rootpath.system_uploadroot.$relativePath;//上传路径
	if (!file_exists($uppath)) {//检查上传目录是否存在
		return false;
	}
	$file_prefix = time(true);
	$file_suffix=0;
	if ($single) {//单个文件
		if (0 != $_FILES[$formName]['error']) {//上传时存在错误
			return false;
		}

		if (!is_uploaded_file($_FILES[$formName]['tmp_name'])) {//非法上传
			return false;
		}
		$ext = end(explode(".",$_FILES[$formName]['name']));
		if (!in_array($ext,$acceptExt)) {//非法扩展名
			return false;
		}
		do {//不允许文件名重复
			$filename = "{$file_prefix}_{$file_suffix}.{$ext}";
			$file_suffix++;
		}while(file_exists("{$uppath}/$filename"));	
		

		if (!move_uploaded_file($_FILES[$formName]['tmp_name'],"$uppath/$filename")) {
			return false;
		}

		return system_uploadroot.$relativePath.'/'.$filename;
	}
}