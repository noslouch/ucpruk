<?php
define('WP_ADMIN', true);
require_once("../../../wp-load.php");

if(isset($_REQUEST['loc'])){
$arr = explode("--", base64_decode($_REQUEST['loc']) );
}
if(isset($_REQUEST['id'])){
$arr = explode("--", base64_decode($_REQUEST['id']) );	
}
if( isset($_REQUEST['loc']) || isset($_REQUEST['id'])){
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
if ( !wp_verify_nonce($nonce, 'oqey-video') ) die("Access denied. Security check failed! What are you trying to do? It`s not working like that. ");
if ( !is_user_logged_in() ) die('Login failure.');
if ( !current_user_can('oQeyVideo') ) die(__('You do not have sufficient permissions to upload video files.'));

$oqey_video = $wpdb->prefix . "oqey_video";
$filespath = OQEY_ABSPATH.'wp-content/oqey_gallery/video/'.oqey_getBlogFolder($wpdb->blogid);


    	if($_FILES["Filedata"]["size"]>0){
    		$path = pathinfo($_FILES["Filedata"]["name"]);			
			$ext = array('jpg', 'flv', 'f4p', 'fpv', 'mp4', 'm4v', 'm4a', 'mov', 'mp4v', '3gp', '3g2' ); 
		    if (in_array(strtolower($path['extension']), $ext)){	
    		$j = 0;
  			while(1){
  			   
				$video_link = trim(strtolower(sanitize_title($path['filename']).".".$path['extension']));	
				$title = trim($path['filename']);
  				if(file_exists($filespath . $video_link)){ 
  				  
					$video_link = trim(strtolower($path['filename']."_".time().".".$path['extension']));
					$title = trim($path['filename'])."_".time();
				
                }	
				
                	ini_set('memory_limit', '-1');
  					@move_uploaded_file($_FILES["Filedata"]["tmp_name"],$filespath.$video_link);
                    
					if(strtolower($path['extension'])!="jpg"){
					   
                        $video_link = 'wp-content/oqey_gallery/video/'.oqey_getBlogFolder($wpdb->blogid).$video_link;
                                            
                        $wpdb->query( $wpdb->prepare( "INSERT INTO $oqey_video (title, video_link, type) 
                                                                        VALUES ( %s, %s, %s)",                                                                               
                                                                                $title,
                                                                                $video_link,
                                                                                'oqey'                                                                                                                                                                                                                            
                                                    )
                                                    );
					
                    }
  					break;
  				
  				$j++;
  			}
    	}	
	}

echo 'File uploaded';
?>