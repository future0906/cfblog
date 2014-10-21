<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:数据库操作对象dbo
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*	2007-12-2	增加设置事务提交模式方法SetAutoCommit和事务的提交和回滚Commit,Rollback
*			更改类名，改为DBO，并将一些方法命名规范化
*/

//check is directly access
if(!defined('rootpath')) {
	die("Access denied");
}
//出错消息定义区
//若以后要国际化，则更改这里
define('err_db_connect_failed','数据库连接失败。');
define('err_db_query_failed','数据库查询失败。');
define('err_db_result_null','错误！无法获取查询结果。');
define('err_db','数据库出错。');

include_once(rootpath.'/config/db_config.php');

/*dbo  数据库操作对象*/
class DBO {
	private static $querycount=0;//执行查询次数
	private $db=null;
	private $lastresult=null;
	//连接到数据库
	function ConnectGP($dbhost,$dbname,$username,$password){
		$this->db=@mysqli_connect($dbhost,$username,$password,$dbname);
		if(mysqli_connect_errno()){
			$this->DBDie(err_db_connect_failed);
		}
		//设置UTF8编码存放
		mysqli_query($this->db,'set names \'UTF8\'');//设置UTF-8
		mysqli_query($this->db,"set @@session.time_zone='".db_timezone."'");
		mysqli_set_charset($this->db,'UTF8');
	}
	//连接到数据库,使用默认设置用户和密码
	function Connect(){
		$this->db=@mysqli_connect(db_host,db_username,db_password,db_name);
		if(mysqli_connect_errno()){
			$this->DBDie(err_db_connect_failed);
		}
		//设置UTF8编码存放
		mysqli_query($this->db,'set names \'UTF8\'');//设置UTF-8
		mysqli_query($this->db,"set @@session.time_zone='".db_timezone."'");
		mysqli_set_charset($this->db,'UTF8');
	}
	//执行数据库查询
	function Query($query,$querytype=MYSQLI_STORE_RESULT){
		if($this->lastresult!=null&&!is_bool($this->lastresult)){//关闭已经打开的连接
			mysqli_free_result($this->lastresult);
		}
		$this->lastresult=mysqli_query($this->db,$query,$querytype);
		if($this->lastresult===FALSE){
			$this->DBDie(err_db_query_failed);
		}
		DBO::$querycount++;
	}
	//执行只有一个数据的查询
	function GetOne($query,$querytype=MYSQLI_USE_RESULT){
		if($this->lastresult!=null&&!is_bool($this->lastresult)){//关闭已经打开的连接
			mysqli_free_result($this->lastresult);
		}
		$this->lastresult=mysqli_query($this->db,$query,$querytype);
		if($this->lastresult===FALSE){
			$this->DBDie(err_db_query_failed);
		}
		$temp = mysqli_fetch_row($this->lastresult);
		$temp = $temp[0];
		DBO::$querycount++;
		return $temp;
	}
	//提取下一行数据
	function GetRow(){
		if($this->lastresult==null){
			//TODO:显示出错页面
			echo err_db_result_null;
			return null;
		}
		return is_bool($this->lastresult) ? null : mysqli_fetch_row($this->lastresult);
	}
	//提取下一行数据（关联数组）
	function GetAssocRow(){
		if($this->lastresult==null){
			//TODO:显示出错页面
			echo err_db_result_null;
			return null;
		}
		return is_bool($this->lastresult) ? null :mysqli_fetch_assoc ($this->lastresult);
	}
	//提取所有结果，以关联数组返回
	function GetAllAssoc(){
		$temp = null;
		$i = 0;
		if($this->lastresult==null){
			//TODO:显示出错页面
			echo err_db_result_null;
			return null;
		}
		if(is_bool($this->lastresult)){
			return null;
		}
		else{
			while($row = mysqli_fetch_assoc($this->lastresult)){
				$temp[$i]=$row;
				$i++;
			}
		}
		return $temp;
	}
	//返回上一次查询结果的行数(如果是非select ,show 等语句，则返回影响行数
	function GetRowsNum(){
		if($this->lastresult==null){
			return 0;
		}
		return is_bool($this->lastresult) ? mysqli_affected_rows($this->db) : mysqli_num_rows($this->lastresult);
	}
	//出错显示
	function DBDie($msg){
		$err_code=$this->GetLastErrCode();
		$err_desc=$this->GetLastErrDesc();
		$datetime=date("Y-m-d @ H:i:s");
		$path='http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		$message = "<html>\n<head>\n";
		$message .= "<meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\">\n";
		$message .= "<style type=\"text/css\">\n";
		$message .=  "body,td,p,pre {\n";
		$message .=  "font-family : Verdana, sans-serif;font-size : 12px;\n";
		$message .=  "}\n";
		$message .=  "</style>\n";
		$message .= "</head>\n";
		$message .= "<body bgcolor=\"#FFFFFF\" text=\"#000000\" link=\"#006699\" vlink=\"#5493B4\">\n";

		$message .= "<p>".err_db.":</p><pre><b>".htmlspecialchars($msg)."</b></pre>\n";
		$message .= "<b>Mysql error description</b>: ".$err_desc."\n<br />";
		$message .= "<b>Mysql error number</b>: ".$err_code."\n<br />";
		$message .= "<b>Date</b>: ".$datetime."\n<br />";
		$message .= "<b>Script</b>: ".$path."\n<br />";

		$message .= "</body>\n</html>";
		$this->Close();
		die($message);		
	}
	//出错代码
	function GetLastErrCode(){
		return intval($this->db ? mysqli_errno($this->db) : mysqli_connect_errno());
	}
	//出错信息
	function GetLastErrDesc(){
		return $this->db ? mysqli_error($this->db) : mysqli_connect_error();
	}
	//数据库版本
	function GetVersion(){
		return mysqli_get_server_info($this->db);
	}
	//直接将结果集资源提取出来
	//不建议使用此函数，若使用此函数则以后移植其他数据库会比较麻烦
	function GetResult(){
		return $this->lastresult;
	}
	//返回最后一次Insert语句生成的ID
	function GetInsertID(){
		return mysqli_insert_id($this->db);
	}
	//关闭所有连接和资源
	function Close(){
		if(!is_bool($this->lastresult) && $this->lastresult!=null){
			mysqli_free_result($this->lastresult);
		}
		if(!is_bool($this->db) && $this->db!=null){
			mysqli_close($this->db);
		}
		unset($this->db);
	}
	//设置是否自动提交到数据库
	function SetAutoCommit($mode){
		mysqli_autocommit($this->db,$mode);
	}
	//开始一个新的事务
	function StartTrans(){
		mysqli_autocommit($this->db,false);
		$this->Query('begin');
	}
	//完成一个事务
	function EndTrans(){
		mysqli_autocommit($this->db,true);
		$this->Query('commit');
	}
	//回滚一个事务
	function Rollback(){
		mysqli_autocommit($this->db);
		$this->Query('rollback');
	}
	//返回查询次数
	static function GetQueryCount(){
		return DBO::$querycount;
	}
}
?>