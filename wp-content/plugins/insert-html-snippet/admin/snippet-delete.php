<?php
global $wpdb;

$_POST = stripslashes_deep($_POST);
$_GET = stripslashes_deep($_GET);

$xyz_ihs_snippetId = intval($_GET['snippetId']);
$xyz_ihs_pageno = intval($_GET['pageno']);

if($xyz_ihs_snippetId=="" || !is_numeric($xyz_ihs_snippetId)){
	header("Location:".admin_url('admin.php?page=insert-html-snippet-manage'));
	exit();

}
$snippetCount = $wpdb->query( 'SELECT * FROM '.$wpdb->prefix.'xyz_ihs_short_code WHERE id="'.$xyz_ihs_snippetId.'" LIMIT 0,1' ) ;

if($snippetCount==0){
	header("Location:".admin_url('admin.php?page=insert-html-snippet-manage&xyz_ihs_msg=2'));
	exit();
}else{
	
	$wpdb->query( 'DELETE FROM  '.$wpdb->prefix.'xyz_ihs_short_code  WHERE id="'.$xyz_ihs_snippetId.'" ' ) ;
	
	header("Location:".admin_url('admin.php?page=insert-html-snippet-manage&xyz_ihs_msg=3&pagenum='.$xyz_ihs_pageno));
	exit();
	
}
?>