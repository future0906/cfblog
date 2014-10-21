<?php
/*
* $project:cfblog
* $version:0.2
* $author:future0906
* $usage:record system message into a file
* $filepath:/core/Log.php
* $envrionment: PHP-5.2 MySQL-5.0 Apache:2.2.2
*/

/*
every core php file would have this statements,
it use to get rootpath and prevent nest include problem
*/
if (!defined('rootpath')) {
	define('rootpath',dirname(__FILE__).'/../../');
}

/*
	This class used for log some error infomation easily.
	usage:
		1.Create a log object by using Log::new_log.Log::new_log is factory method.
		2.To log informations,use $object->add method.
		3.By the end of page handling,you must call $object->close.
*/
class Log {
	private $log_file;
	
	/*
		$log_dir:Specify the log directory.Notice,you do not need to give the file name to $log_dir,it will auto-generate a log file name with date.
		$mode:How to open log file, it was just the same as fopen.
	*/
	static public function new_log($log_dir,$mode = 'a')
	{
		$log = new Log();
		$log_file_name = date('Y-m-d').'.log';
		$file_handle = fopen($log_dir.$log_file_name,$mode);
		
		if (null == $file_handle) {
			//TODO:This play a error message
		}
		
		$log->log_file = $file_handle;
		return $log;
	}
	
	/*
		$message:what is the error message
		$type:type of message
	*/
	public function add($message,$type = '')
	{
		$remote_info = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '[!address error,check programme]';
		$log_date = date('c');
		$pid = getmypid();
		$log_content = "$remote_info [$log_date] - [PID:$pid] [$type] - $message\n";
		fwrite($this->log_file,$log_content);
	}
	
	/*close log file safely*/
	public function close()
	{
		if (null != $this->log_file) {
			fclose($this->log_file);
		}
	}
}
?>