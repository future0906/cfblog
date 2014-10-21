<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:数据层的抽象对象，定义数据层对象的抽象操作
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*	2007-12-2	Start writing this file
*TODO::
*	添加级联操作功能
*/
//check is directly access
if(!defined('rootpath')) {
	die("Access denied");
}
//include section
include_once rootpath.'/config/config.php';
//define section

/*
	所有模型层的类都要继承该类，该类提供一些通用的方法
	可以简化数据层的操作。但这继承不是必须的。
	@package /includes/core/model/
	@copyright future0906
	@version 0.1.0
*/
class BaseModel
{
	/*
		模型对应的表名，不包含前缀
		@var string
	*/
	protected $mTableName;
	/*
		模型对应的表名，包含前缀
		@var string
	*/
	protected $mFullTableName;
	/*
		模型对应关系表中的属性列表
		数组中每一个元素代表一个属性，关联数组的key代表属性名，value代表类型
		ColumnName=>Type
		@var array
	*/
	protected $mColumn;
	/*
		主键名
		@var string
	*/
	protected $mPrimaryKey;
	/*
		标识该对象与其他对象是否有一对一的关系
		该属性是一个数组，关联数组的key代表自己的属性，value代表另外对象的属性
		value的格式应该是：表名.属性名
		@var array
	*/
	protected $mOneToOne;
	/*
		标识该对象与其他对象是否有一对多的关系
		该属性是一个数组，关联数组的key代表自己的属性，value代表另外对象的属性
		value的格式应该是：表名.属性名
		@var array
	*/
	protected $mOneToMore;
	/*
		表示与某一个的某个字段的一一对应关系
		查找时查询将会自动将链接的字段查询出来
		@var array
	*/
	protected $mTableLink;
	/*
		数据库操作对象
		@var DBO
	*/
	protected $mrDbo;
	/*
		缓存保存对象
		@var array
	*/
	protected $mCache;
	/*
		缓存目录
		@var string
	*/
	private $mCacheDir;
	/*
		缓存文件名
		@var string
	*/
	private $mCacheFile;
	/*
		要缓存的ＳＱＬ语句的结果
		@var string
	*/
	private $mCacheSql;
	/*
		为该对象创建一个新的记录
		需要注意的是，该方法并不验证数据的正确性和对数据过滤
		请在控制器中调用相应的方法对数据进行过滤
			必须注意，目前还没实验对所属的表进行级联插入
		@param oneRow 数组
	*/
	public function Create($oneRow)
	{
		$full_table_name = $this->mFullTableName;
		$rDbo = &$this->mrDbo;
		//fetch column and values
		$column_names = array_keys($oneRow);
		$column_values = array_values($oneRow);
		//implode as a list
		$column_names_list = implode(',',$column_names);
		$column_values_list = implode(',',$column_values);
		$insert_sql = "insert into 
						$full_table_name($column_names_list)
						values($column_values_list)";
		$rDbo->Query($insert_sql);
		return true;
	}
	/*
		查找数据，数据并不马上返回，通过其他方法提取数据
		$fields表示要查找哪几个字段
		$sort表示对根据哪些字段进行排序
		@param condition string
		@param where string
		@param orderby string
		@param limit string
		@param linkTable string	
		@return null
	*/
	public function Find($fields = '*', $where = null, $orderBy = null, $limit=null, $linkTable = null)
	{
		$rdbo = &$this->mrDbo;
		$from_table = ' from ' . (null === $linkTable ?
							$this->mFullTableName :
							$linkTable);//check link table
		$select_sql = "select $fields
						$from_table
						$where
						$orderBy
						$limit";//select sql
		$rdbo->Query($select_sql);//query
	}
	/*
		查找数据，数据马上通过该函数返回
		@param condition string
		@param where string
		@param orderby string
		@param limit string
		@param linkTable string	
		@return array		
	*/
	public function FindAll($fields = '*', $where = null, $orderBy = null, $limit=null, $linkTable = null)
	{
		$rdbo = &$this->mrDbo;
		$from_table = ' from ' . (null === $linkTable ?
							$this->mFullTableName :
							$linkTable);//check link table
		$select_sql = "select $fields
						$from_table
						$where
						$orderBy
						$limit";//select sql
		$rdbo->Query($select_sql);//query
		return $rdbo->GetAllAssoc();
	}
	/*
		提取下一行数据
		@param type int
	*/
	public function GetOneRow($type=1)
	{
		return 1 === $type ? //get associate row?
					$this->mrDbo->GetAssocRow() :
					$this->mrDbo->GetRow();
	}
	/*
		获取第一个记录的第一列
	*/
	public function GetOne()
	{
		$row = $this->mrDbo->GetRow();
		return $row[0];
	}	
	/*
		提取所有数据
	*/
	public function GetAllRow()
	{
		return $this->mrDbo->GetAllAssoc();
	}
	/*
		更新某一行的数据，参数中的row必须包含主键，而且是依据主键更新该行
		@param row arry
	*/
	public function Update($oneRow,$where = null)
	{
		if(!isset($oneRow[$this->mPrimaryKey]) && null === $where) {
			return false;
		}
		//if primary key is seted
		if(isset($oneRow[$this->mPrimaryKey])) {
			$where_clause = "where {$this->mPrimaryKey} = {$oneRow[$this->mPrimaryKey]}";
		}
		else {
			$where_clause = $where;
		}
		unset($oneRow[$this->mPrimaryKey]);//remove primary key from field list
		foreach($oneRow as $field_name => $field_value) {//iterator
			$set_pairs[] = $field_name . ' = ' . $field_value;
		}
		//convert  as set_clause
		$set_clause = implode(',',$set_pairs);
		$set_clause = "set \n".$set_clause;
		
		$update_sql = "update {$this->mFullTableName} 
					   $set_clause
					   $where_clause";
		//update_sql is ready,execute query
		$this->mrDbo->Query($update_sql);
		//done
		return true;
	}
	/*
		更新某一行的某一个字段
		@param match array 匹配条件
		@param field 字段名
		@param value 属性值
	*/
	public function UpdateOne($match,$field,$value)
	{
		if(empty($match)) {//更新条件为空，不允许
			return false;
		}
		$match_field = key($match);
		$match_value = current($match);
		$where_clause = "where $match_field = $match_value";
		$set_clause = "set $field = $value";
		$update_sql = "update {$this->mFullTableName}
						$set_clause
						$where_clause";
		$this->mrDbo->Query($update_sql);
		//更新完毕
		return true;
	}
	/*
		数据保存方法，检查是否设置主键，若已设置，则调用更新，否则调用Create
		@param row array
	*/
	public function Save($oneRow)
	{
		if(!isset($oneRow[$this->mPrimaryKey])) {//no primary key
			return $this->Create($oneRow);
		}
		else {
			return $this->Update($oneRow);
		}
	}
	/*
		删除给定的数据行
		@param oneRow array
	*/
	public function Delete($oneRow,$trans=false,$cascade=false)
	{
		if(!isset($oneRow[$this->mPrimaryKey])) {//no primary key,can't delete
			return false;
		}
		$where_clause = "where {$this->mPrimaryKey} = {$oneRow[$this->mPrimaryKey]}";
		$delete_sql = "delete from {$this->mFullTableName}
						$where_clause";
		$this->mrDbo->Query($delete_sql);
		return true;
	}
	/*
		批量删除给定的数据行
		@param rows array
	*/
	public function DeleteBatch($rows)
	{
		$this->mrDbo->SetAutoCommit(false);//取消自动提交
		$this->mrDbo->StartTrans();//开始一个事务
		foreach ($rows as $onerow) {
			if(!isset($onerow[$this->mPrimaryKey])) {//no primary key,can't delete
				return false;
			}
			$where_clause = "where {$this->mPrimaryKey} = {$onerow[$this->mPrimaryKey]}";
			$delete_sql = "delete from {$this->mFullTableName}
							$where_clause";
			$this->mrDbo->Query($delete_sql);
		}
		$this->mrDbo->EndTrans();//事务结束
		return true;
	}
	/*
		初始化缓存
		@param cache_file 缓存的文件名
		@param cache_sql 缓存的SQL语句
		@param dbo 数据库操作对象
	*/
	public function InitCache($cache_file,$cache_sql)
	{
		$this->mCacheDir = rootpath.'/cache/';
		$this->mCacheFile = $cache_file;
		$this->mCacheSql = $cache_sql;
		if (file_exists($this->mCacheDir.$this->mCacheFile)) {
			$this->mCache = include($this->mCacheDir.$this->mCacheFile);
			$current_timestamp = time();
			if($current_timestamp >= $expire) { //if a cache is out of date
				$this->UpdateCache();//update cache
			}
		}
		else {
			$this->UpdateCache();
		}
	}
	/*
		更新缓存，从数据库中取出数据
	*/
	public function UpdateCache()
	{
		global $global_vars;
		$this->mrDbo->Query($this->mCacheSql);
		$this->mCache = $this->mrDbo->GetAllAssoc();
		//setting cache expire date,if want to change this,modiy system_cache_expire in Config.php file
		$cache_expire = time() + system_cache_expire * 60;
		if (is_writeable($this->mCacheDir)) {
			$config_content = "<?php\n";
			$config_content .= "/*\n";
			$config_content .= "This file is auto generate by cfblog.\n";
			$config_content .= "Please don not modify this file, else you won't get programme run normal\n";
			$config_content .= "\$TimeStamp:$_SERVER[REQUEST_TIME]\n";
			$config_content .= "*/\n";
			$config_content .= "\$expire = $cache_expire;\n";
			$config_content .= 'return ';
			$config_content .= var_export($this->mCache,true);
			$config_content .= ";\n";
			$config_content .= "?>\n";
			file_put_contents($this->mCacheDir.$this->mCacheFile,$config_content);	
		}
		else {//cache dir isn't writable
			die("缓存文件不可写，请检查缓存目录");
			exit();
		}
	}
	/*
		获取缓存内容
		@return array
	*/
	public function &GetCache()
	{
		return $this->mCache;
	}
}
?>