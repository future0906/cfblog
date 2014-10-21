<?php
/*
* $project:cfblog2 v0.1.0
* $version:0.1.0 
* $author:future0906
* $todo:
* $environment: Mysql 5.0.21/PHP 5.2.1/Apache 2.2.2(Win32)/Windows Server 2003 
*
*ChangeLog:
*	2007-12-3	Start writing this file
*/
$robot_code = $_SESSION['robotcode'];
if (isset($robot_code)) {
	$noise_count = 50; //how many noise point
	$robot_image_height = 20;
	$robot_image_width = 45;
	
	//创建图像
	$im=imagecreate($robot_image_width,$robot_image_height);
	$bgcolor=imagecolorallocate($im,255,255,255);
	$fontcolor=imagecolorallocate($im,0,0,0);
	imagefill($im,0,0,$bgcolor);
	imagestring($im,5,0,0,$robot_code,$fontcolor);
	//绘制噪点
	for ($i=0;$i<$noise_count;$i++) {
		$noise_color = imageColorAllocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
		imageSetPixel($im, rand(0, $robot_image_width), rand(0, $robot_image_height), $noise_color);
	}
	imagepng($im);
	imagedestroy($im);//删除图像句柄	
}
?>
