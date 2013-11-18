<?php
require_once("../../../wp-load.php");

if($_REQUEST['img_type']=="nextgen"){
    $path = OQEY_ABSPATH.'/'.urldecode($_REQUEST['img_f_path']).'/';
    $img  = $path.$_REQUEST['img'];    
}else{
    $path = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$_REQUEST['folder'];
    $img  = $path . '/galimg/' . $_REQUEST['img'];
}



  if(file_exists($img)){
    
	$width = $_REQUEST['width'];
	$height = $_REQUEST['new_height'];
    $x = @getimagesize($img);
    if($x){
       $oqeyw = $x[0];
       $oqeyh = $x[1];
    }
  }

if ($_REQUEST['process']!="off") {
    
    $im = ImageCreateFromJPEG ($img) or 
    $im = ImageCreateFromPNG ($img) or 
    $im = false;
}

if (!$im || $_REQUEST['process']=="off") {
   /* 
    header ("Content-type: image/jpeg");
	readfile ($img);
    */
    $fisier = str_replace(OQEY_ABSPATH, get_option("siteurl")."/", $img);
    header('Location: '.$fisier);
    exit();
    
} else {
    header ("Content-type: image/jpeg");
	$thumb = @ImageCreateTrueColor ($width, $height);
	@ImageCopyResampled ($thumb, $im, 0, 0, 0, 0, $width, $height, $oqeyw, $oqeyh);
	@ImageJPEG ($thumb, NULL, 95);
    @imagedestroy($thumb);
}
?>