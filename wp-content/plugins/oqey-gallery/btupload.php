<?php
define('WP_ADMIN', true);
require_once("../../../wp-load.php");

if(isset($_REQUEST['loc'])){
$arr = explode("--", base64_decode($_REQUEST['loc']) );
$gal_id = $arr[0];
$auth_cookie = $arr[1];
$logged_in_cookie = $arr[2];
$nonce = $arr[3];
}
if ( is_ssl() && empty($_COOKIE[SECURE_AUTH_COOKIE]) && !empty($auth_cookie) )
	$_COOKIE[SECURE_AUTH_COOKIE] = $auth_cookie;
elseif ( empty($_COOKIE[AUTH_COOKIE]) && !empty($auth_cookie) )
	$_COOKIE[AUTH_COOKIE] = $auth_cookie;
if ( empty($_COOKIE[LOGGED_IN_COOKIE]) && !empty($logged_in_cookie) )
	$_COOKIE[LOGGED_IN_COOKIE] = $logged_in_cookie;

unset($current_user);
global $wpdb;
require_once(OQEY_ABSPATH . 'wp-admin/admin.php');
if ( !wp_verify_nonce($nonce, 'oqey-skins') ) die("Access denied. Security check failed! What are you trying to do? It`s not working like that.");
if ( !is_user_logged_in() ) die('Login failure.');
if ( !current_user_can('oQeySkins') ) die(__('You do not have sufficient permissions to upload files.'));


if($_FILES['Filedata']['size']>0){
        if($_FILES["Filedata"]["size"]>0){
    		$path = pathinfo($_FILES["Filedata"]["name"]);			
    		$ext = array('zip', 'ZIP'); 
		if(in_array( strtolower($path['extension']), $ext)){        
  	    while(1){			
		if ( class_exists('ZipArchive') ){
			$zip = new ZipArchive;
	        $zip_file = $_FILES["Filedata"]["tmp_name"];
            $zip->open($zip_file);
	        $zip_extract = OQEY_ABSPATH."wp-content/oqey_gallery/skins/".oqey_getBlogFolder($wpdb->blogid);
            $zip->extractTo($zip_extract);
            $zip->close();
		}else{
			require_once(OQEY_ABSPATH . 'wp-admin/includes/class-pclzip.php');				
			$zip_file = $_FILES["Filedata"]["tmp_name"];
			$zip_extract = OQEY_ABSPATH."wp-content/oqey_gallery/skins/".oqey_getBlogFolder($wpdb->blogid);
			$archive = new PclZip($zip_file);
            $list = $archive->extract($zip_extract);
            if ($list == 0) {
            die("ERROR : '".$archive->errorInfo(true)."'");
            }
		}
    		break;
  			}
    	}
	}
}
echo 'File uploaded';
?>