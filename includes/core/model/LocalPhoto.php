<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:相片操作对象
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*   2008-2-1    Start writing this file
*   2008-2-12   修改GetPhotos函数，修改$fields变量，截取固定长度的字符
*   2008-2-12   Add:增加缩略图处理,添加新函数GenThumb，修改了Upload函数，调用GenThumb函数
*   2008-7-25   Add:添加upload_flag变量,用于监视是否没有成功上传任何文件
*TODO:
*   添加文件名支持
*/
//check is directly access
if(!defined('rootpath')) {
    die("Access denied");
}
//include section
include_once rootpath.'/config/config.php';
include_once rootpath.'/includes/core/model/BaseModel.php';
include_once rootpath.'/includes/core/model/BasePhoto.php';
//define section
/*
    提供对相册的基本操作，包括删除、修改、插入
    该类是基于BaseModel提供的基本操作
    @package /includes/core/model/
    @copyright future0906
    @version 0.1.0
*/
class LocalPhoto extends BaseModel implements BasePhoto
{
    private $mAcceptExt;
    private $mMaxFilesize;
    private $mPhotoPath;
    private $mThumbPath;
    private $mThumbWidth;
    private $mThumbHeight;
    private $mThumbRatio;
    /*
        构造函数
    */  
    public function __construct(&$rDbo)
    {
        $this->mrDbo = &$rDbo;
        $this->mTableName = 'photos';
        $this->mFullTableName = db_prefix.$this->mTableName;
        $this->mColumn = array(
                                'eid'=>'int(32) unsigned not null primary key AUTO_INCREMENT',
                                'originalname'=>'varchar(255) not null',
                                'storename'=>'char(64) not null',
                                'uploaddate'=> 'datetime not null',
                                'album'=>'int(32) unsigned not null default 0'                      
                        );
        $this->mPrimaryKey = 'eid';
        $this->mOneToOne = null;
        $this->mOneToMore = null;
        $this->mAcceptExt = array('jpg','jpeg','png','gif','bmp');//设置允许的扩展名
        $this->mMaxFilesize = system_photosize * 1024;//以byte为单位的大小限制
        $this->mPhotoPath = rootpath.system_photopath;
        $this->mThumbPath = $this->mPhotoPath.'/thumbs';
        $this->mThumbHeight = 150;//缩略图大小
        $this->mThumbWidth = 200;
        $this->mThumbRatio = $this->mThumbWidth / $this->mThumbHeight;//缩略图比例
    }
    /*
        获取相片记录
        @param $page int 要获取的页数
        @param $album int 相册编号
    */
    public function GetPhotos($page,$album=0)
    {
        if($page < 1) {
            die("内部错误:页号不能少于1");
        }
        $limit = blog_photos_count;
        $offset = ($page - 1) * $limit;//计算偏移位置
        $fields = 'eid,substr(originalname,1,30) originalname,storename';
        $limit_clause = "limit $limit offset $offset ";
        if(0 != $album) {//不是默认相册
            $where_clause = "where album = $album";
        }
        else {
            $where_clause = null;
        }
        $order_clause = 'order by uploaddate desc';
        
        return $this->FindAll($fields,$where_clause,$order_clause,$limit_clause);
    }
    /*
        获取相片数目
        @param $album int 相册编号
    */
    public function GetPhotoCount($album=0)
    {
        $fields = 'count(eid)';
        if( 0 != $album) {
            $where_clause = "where album = $album";
        }
        else {
            $where_clause = null;
        }
        $this->Find($fields,$where_clause);

        return $this->GetOne();
    }
    /*
        上传照片
        @return bool
    */
    public function Upload()
    {
        $upload = $_FILES['photo'];
        $upload_flag = false;
        $file_prefix = time(true);//当前时间，作为上传文件的一个前缀
        $file_suffix = 0;
        $file_count = count($upload['name']);//文件个数
        $this->mrDbo->StartTrans();//开始一个事务
        for($i = 0;$i < $file_count;$i++) {
            if(0 != $upload['error'][$i]) {//如果上传文件有错误，则忽略该上传，继续下一次迭代
                continue;
            }
            //检查文件是否合法上传文件
            if(!is_uploaded_file($upload['tmp_name'][$i])) {
                return false;
            }

            $ext=strtolower(end(explode(".",$upload['name'][$i])));
            if(!in_array($ext,$this->mAcceptExt)) {//扩展名不允许
                return false;
            }

            do {//不允许文件名重复
                $file_fullname = "{$file_prefix}_{$file_suffix}.{$ext}";
                $file_suffix++;
            }while(file_exists("{$this->mPhotoPath}/$file_fullname"));

            //创建缩略图
            $this->GenThumbs($upload['tmp_name'][$i],$file_fullname,$ext);//Add by future0906 at [2008-2-12]

            if(!move_uploaded_file($upload['tmp_name'][$i],"{$this->mPhotoPath}/$file_fullname")) {//移动文件
                //假如，移动文件失败
                return false;
            }

            $newfile['originalname'] = "'".$upload['name'][$i]."'";
            $newfile['storename'] = "'".$file_fullname."'";
            $newfile['uploaddate'] = "now()";
            $this->Save($newfile);
            $upload_flag = true;
        }
        $this->mrDbo->EndTrans();//事务结束
        return $upload_flag ? true : false;
    }
    public function getAlbumList()
    {

    }

    public function getPhotoList($ablumn_id)
    {
    }
    /*
        创建缩略图
    */
    private function GenThumbs($src_file_path,$storename,$ext)
    {
        //打开图像
        switch($ext)
        {
            case 'jpg':
            case 'jpeg':
                $srcim_handle = imagecreatefromjpeg($src_file_path);
                break;
            case 'png':
                $srcim_handle = imagecreatefrompng($src_file_path);
                break;
            case 'gif':
                $srcim_handle = imagecreatefromgif($src_file_path);
                break;
            case 'bmp':
                $srcim_handle = imagecreateformwbmp($src_file_path);
                break;
            default:
                die("未知图像格式");
                break;
        }
        $srcim_width = imagesx($srcim_handle);
        $srcim_height = imagesy($srcim_handle);
        $srcim_ratio = $srcim_width / $srcim_height;

        if($srcim_width < $this->mThumbWidth && $srcim_height < $this->mThumbHeight) {//如果图片比缩略图还小，直接copy
            copy($src_file_path,$this->mThumbPath.'/'.$storename);
            return true ;
        }
        
        if($srcim_ratio > $this->mThumbRatio) {//宽比较大，以宽为中心
            $thumb_width = $this->mThumbWidth;
            $thumb_height = $this->mThumbWidth / $srcim_ratio;
        }
        else if($srcim_ratio < $this->mThumbRatio) {//高比较大，以高中心
            $thumb_width = $this->mThumbHeight * $srcim_ratio ;
            $thumb_height = $this->mThumbHeight;
        }
        else{//高和宽比例一样，直接
            $thumb_width = $this->mThumbWidth;
            $thumb_height = $this->mThumbHeight;
        }
        $thumb_handle = imagecreatetruecolor($thumb_width,$thumb_height);
        imagecopyresampled($thumb_handle,$srcim_handle,0,0,0,0,$thumb_width,$thumb_height,$srcim_width,$srcim_height);  
        imagepng($thumb_handle,$this->mThumbPath.'/'.$storename);
        return true;
    }
    /*
        重新根据photo目录的照片生成一个新的缩略图   
    */
    function FixThumbs()
    {
        if(is_dir($this->mPhotoPath)) {
            if($photodir = opendir($this->mPhotoPath)) {//打开目录
                while(($photofile = readdir($photodir)) != false) {
                    if('dir' != filetype($this->mPhotoPath.'/'.$photofile)) {
                        $ext = end(explode('.',$photofile));
                        $this->GenThumbs($this->mPhotoPath.'/'.$photofile,$photofile,$ext);
                    }
                }
            }
        }
        closedir($photodir);
    }
}

?>
