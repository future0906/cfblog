<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $usage:��Ƭ��������
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*   2009-6-18   Start writing this file
*/
//check is directly access
if(!defined('rootpath')) {
    die("Access denied");
}
//include section
include_once rootpath.'/config/config.php';
/*
    ��������࣬Ϊ�Ժ��ṩ�������
    @package /includes/core/model/
    @copyright future0906
    @version 0.1.0
*/
interface BasePhoto
{
    /*��ȡ����б�*/
    function getAlbumList();

    /*��ȡ��Ƭ�б�*/
    function getPhotoList($ablum_id);
}
?>
