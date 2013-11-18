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
if ( !wp_verify_nonce($nonce, 'oqey-music') ) die("Access denied. Security check failed! What are you trying to do? It`s not working like that. ");
if ( !is_user_logged_in() ) die('Login failure.');
if ( !current_user_can('oQeyMusic') ) die(__('You do not have sufficient permissions to upload files.'));

  global $wpdb;
  $oqey_music = $wpdb->prefix . "oqey_music";
  $filespath = OQEY_ABSPATH."wp-content/oqey_gallery/music/".oqey_getBlogFolder($wpdb->blogid);
  if(!is_file($filespath)){   
  
     wp_mkdir_p ($gal_dir_up);
  
  }


if($_FILES['Filedata']['size']>0){
    	if($_FILES["Filedata"]["size"]>0){
    		$path = pathinfo($_FILES["Filedata"]["name"]);
			
    		$ext = array('mp3', 'MP3'); 
		    if (in_array( strtolower($path['extension']), $ext) ){	
		
			    $name = trim(sanitize_title($path['filename']).".".$path['extension']);
				$music_title = $path['filename'];
  
             
  			while(1){
				if(!file_exists($filespath.$name)){	
				    
                    $name = trim(sanitize_title($path['filename']).".".$path['extension']);
				    $music_title = $path['filename'];
				
                }else{		
				    
                    $name = trim(sanitize_title($path['filename'])."_".time().".".$path['extension']);
				    $music_title = $path['filename'];							
				
                }
                
                $audio_link = rtrim(get_option('siteurl'), "/")."/wp-content/oqey_gallery/music/".oqey_getBlogFolder($wpdb->blogid).$name;
                $audio_path = $filespath.$name;
                
					ini_set('memory_limit', '-1');
  					@move_uploaded_file($_FILES["Filedata"]["tmp_name"],$audio_path);
                    
                    $wpdb->query( $wpdb->prepare( "INSERT INTO $oqey_music (link, path, title, type) 
                                                         VALUES ( %s, %s, %s, %s)", 
                                                                 $audio_link,
                                                                 $audio_path,
                                                                 $music_title,
                                                                 "oqey"
                                                         )
                                                         );
                    		
  					break;
  			}
    	}
	}
}
echo 'File uploaded';
?>