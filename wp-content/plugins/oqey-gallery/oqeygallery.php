<?php
// oQey Gallery
// Copyright (c) 2012 oqeysites.com
// This is an add-on for WordPress
// http://wordpress.org/
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// *****************************************************************

/*
Plugin Name: oQey Gallery
Version: 0.5.3
Description:oQey Gallery is a premium grade plugin for managing images and video, creating photo slideshows with music, photo & video galleries that works fine under iPhone / iPad and other mobile devices. Flash version of the slideshow is automatically replaced by the HTML5 | Java slideshow on a non-flash device. Flash gallery supports customizable skins, so you can change the way it looks with a few clicks using the skin options tool. Commercial skins are also available as well as custom built photo / video galleries and slideshows for professionals. This plugin uses built-in WP functions and a simple batch upload system.
Author: oqeysites.com
Author URI: http://oqeysites.com/
*/
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'oqeygallery.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!');

	
define('OQEY_ABSPATH', str_replace('\\', '/', ABSPATH) ); //oqey path
require("oqey-gallery-functions.php");
require_once(OQEY_ABSPATH . 'wp-admin/includes/plugin.php');
global $oqey_db_version;	
       $oqey_db_version = "0.5";
       $plugin_name = "oQey Gallery plugins";

function oqey_db_install(){
   global $wpdb, $oqey_db_version, $wp_filesystem, $wp_roles;

	if ( !current_user_can('activate_plugins') ) 
		return;

	$perm = get_role('administrator');	
	$perm->add_cap('oQeySettings');
	$perm->add_cap('oQeyGalleries');
    $perm->add_cap('oQeyVideo');	
	$perm->add_cap('oQeySkins');
	$perm->add_cap('oQeyMusic');
	$perm->add_cap('oQeyTrash');
    $perm->add_cap('oQeyRoles');    
  
	add_option("oqey_width", "900" ,'', 'no');
	add_option("oqey_height", "600" ,'', 'no');
	add_option("oqey_bgcolor", "#ffffff" ,'', 'no');
	add_option("oqey_thumb_width", "120" ,'', 'no');
	add_option("oqey_thumb_height", "80" ,'', 'no');
	add_option("oqey_default_gallery_skin", "0" ,'', 'no');
	add_option("oqey_effects_trans_time", "0.5" ,'', 'no');
	add_option("oqey_pause_between_tran", "6" ,'', 'no');
	add_option("oqey_LoopOption", "on" ,'', 'no');
	add_option("oqey_gall_title_no", "on" ,'', 'no');
    //add_option("oqey_flash_gallery_true", "on" ,'', 'no');
	add_option("oqey_backlinks", "on" ,'', 'no');
	add_option("oqey_BorderOption", "on" ,'', 'no');
	add_option("oqey_AutostartOption", "" ,'', 'no');
	add_option("oqey_CaptionsOption", "" ,'', 'no');
	add_option("oqey_options", "TM" ,'', 'no');
	add_option("oqey_noflash_options", "injsarr" ,'', 'no');	
	add_option("oqey_gall_title_no", "on" ,'', 'no');
	add_option("oqey_border_bgcolor", "#000000" ,'', 'no');
	add_option("oqey_effect_transition_type", "fade" ,'', 'no');
    add_option("oqey_effect_transition_type", "fade" ,'', 'no');
    add_option("oqey_show_captions_under_photos", "on" ,'', 'no');
    
    add_option("oqey_gallery_version", "0.5");
    add_option("oqey_db_version", $oqey_db_version);
			
  $gal_dir_up = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid);   
  wp_mkdir_p ($gal_dir_up); // make the gallery folder - root

  $music_dir = OQEY_ABSPATH.'wp-content/oqey_gallery/music/'.oqey_getBlogFolder($wpdb->blogid);    
  wp_mkdir_p ($music_dir); // make the music folder - root
  
  $skins_dir = OQEY_ABSPATH.'wp-content/oqey_gallery/skins/'.oqey_getBlogFolder($wpdb->blogid); 
  wp_mkdir_p ($skins_dir); // make the music folder - root
  
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_images = $wpdb->prefix . "oqey_images";
   $oqey_music = $wpdb->prefix . "oqey_music";
   $oqey_music_rel = $wpdb->prefix . "oqey_music_rel";
   $oqey_skins = $wpdb->prefix . "oqey_skins";
   $oqey_video = $wpdb->prefix . "oqey_video";
   
   if(!$wpdb->get_var("SHOW TABLES LIKE '$oqey_galls'")){
    
    add_option("oqey_flash_gallery_true", "on" ,'', 'no');//add this option if on the fisrt install
    
	$sql = "CREATE TABLE `" . $oqey_galls . "` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`title` tinytext NOT NULL,
		`splash_img` int(11) NOT NULL DEFAULT '0',
		`splash_only` int(11) NOT NULL DEFAULT '0',
		`post_id` int(11) NOT NULL DEFAULT '0',
		`gall_order` int(11) NOT NULL DEFAULT '0',
		`status` int(1) NOT NULL DEFAULT '0',
		`author` int(11) NOT NULL DEFAULT '0',  
		`folder` varchar(255) NOT NULL DEFAULT '',
		`skin_id` int(11) NOT NULL DEFAULT '0',
        `wtmrk_status` varchar(255) NOT NULL DEFAULT 'default',
        `permalink` varchar(255) NOT NULL DEFAULT 'default',
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";	
	 $wpdb->query($sql);
	}
    
    if(!$wpdb->get_var("SHOW TABLES LIKE '$oqey_images'")){
	$sql2 = "CREATE TABLE `" . $oqey_images . "` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`title` tinytext NOT NULL,
		`gal_id` int(11) NOT NULL DEFAULT '0',
		`img_order` int(11) NOT NULL DEFAULT '0',
		`alt` text NOT NULL,
		`comments` text NOT NULL,
		`status` int(1) NOT NULL DEFAULT '0',
        `img_link` text NOT NULL DEFAULT '',
        `img_path` text NOT NULL DEFAULT '',
        `img_type` varchar(255) NOT NULL DEFAULT 'oqey',
        `video_id` int(11) NOT NULL DEFAULT '0',  
        `meta_data` longtext NOT NULL DEFAULT '',     
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";	
	 $wpdb->query($sql2);
    }
    
    if(!$wpdb->get_var("SHOW TABLES LIKE '$oqey_music'")){
    $sql4 = "CREATE TABLE `" . $oqey_music . "` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`link` varchar(255) NOT NULL DEFAULT '',
        `path` varchar(255) NOT NULL DEFAULT '',
		`title` tinytext NOT NULL,
		`artist` varchar(255) NOT NULL DEFAULT '',
		`music_order` int(11) NOT NULL DEFAULT '0',
		`status` int(1) NOT NULL DEFAULT '0', 
        `type` varchar(55) NOT NULL DEFAULT '',       
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";	
    $wpdb->query($sql4);	
    }
    
    if(!$wpdb->get_var("SHOW TABLES LIKE '$oqey_music_rel'")){
	$sql5 = "CREATE TABLE `" . $oqey_music_rel . "` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`music_id` int(11) NOT NULL DEFAULT '0',
		`gallery_id` int(11) NOT NULL DEFAULT '0',
		`mrel_order` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";	
    $wpdb->query($sql5);
	}
    
    if(!$wpdb->get_var("SHOW TABLES LIKE '$oqey_skins'")){
	$sql6 = "CREATE TABLE `" . $oqey_skins . "` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` tinytext NOT NULL,
		`description` tinytext NOT NULL,
		`comkey` varchar(255) NOT NULL DEFAULT '',
		`folder` tinytext NOT NULL,
		`status` int(1) NOT NULL DEFAULT '0',
		`commercial` varchar(3) NOT NULL DEFAULT 'no',
		`skinid` varchar(55) NOT NULL DEFAULT '',
		`firstrun` int(1) NOT NULL DEFAULT '1',
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";	
    $wpdb->query($sql6);	
    }

    if(!$wpdb->get_var("SHOW TABLES LIKE '$oqey_video'")){
     $sql7 = "CREATE TABLE `" . $oqey_video . "` (
		`id` int NOT NULL AUTO_INCREMENT,
		`post_id` int(11) NOT NULL DEFAULT '0',
		`oqey_parent` int(11) NOT NULL DEFAULT '0',
		`title` varchar(255) NOT NULL DEFAULT '',
		`video_link` varchar(255) NOT NULL DEFAULT '',
        `video_image` varchar(255) NOT NULL DEFAULT '',
        `type` varchar(55) NOT NULL DEFAULT '',
        `description` text NOT NULL DEFAULT '',
        `status` int(1) NOT NULL DEFAULT '0',
        `vorder` int(1) NOT NULL DEFAULT '0',
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
    $wpdb->query($sql7);
    }
   
}

if (function_exists('register_activation_hook')){
	register_activation_hook( __FILE__, 'oqey_db_install' );
}

/*Upgrade database if the DB version is lower than 0.4.7*/
function oqey_check_upgrade(){
    global $wpdb, $oqey_db_version, $wp_roles;
    
    $oqey_db_version = "0.5";
    
    $oqey_images = $wpdb->prefix . "oqey_images";
    $oqey_galls = $wpdb->prefix . "oqey_gallery";
    $oqey_video = $wpdb->prefix . "oqey_video";
    $oqey_music = $wpdb->prefix . "oqey_music";
    $oqey_skins = $wpdb->prefix . "oqey_skins";
    
    $installed_oqey_ver = get_option( "oqey_db_version" );
          
    if (version_compare($installed_oqey_ver, '0.4.6', '<')){
	 $perm = get_role('administrator');	
	 $perm->add_cap('oQeySettings');
	 $perm->add_cap('oQeyGalleries');
     $perm->add_cap('oQeyVideo');	
	 $perm->add_cap('oQeySkins');
	 $perm->add_cap('oQeyMusic');
	 $perm->add_cap('oQeyTrash');
     $perm->add_cap('oQeyRoles');
	}
   
    if (version_compare($installed_oqey_ver, '0.4.7', '<')){     
	
	 add_option("oqey_effect_transition_type", "fade" ,'', 'yes');//default effect transition - fade
	
     $wpdb->query("ALTER TABLE $oqey_images ADD img_link TEXT NOT NULL DEFAULT '' AFTER status");
     $wpdb->query("ALTER TABLE $oqey_images ADD img_path TEXT NOT NULL DEFAULT '' AFTER img_link");
     $wpdb->query("ALTER TABLE $oqey_images ADD img_type varchar(255) NOT NULL DEFAULT 'oqey' AFTER img_path");
	}
    
    if (version_compare($installed_oqey_ver, '0.4.9', '<')){     
	
     $wpdb->query("ALTER TABLE $oqey_galls ADD wtmrk_status varchar(255) NOT NULL DEFAULT 'default' AFTER skin_id");
     $wpdb->query("ALTER TABLE $oqey_galls ADD permalink varchar(255) NOT NULL DEFAULT 'default' AFTER wtmrk_status");

	}
    
    if (version_compare($installed_oqey_ver, '0.4.9.1', '<')){     

      $wpdb->query("ALTER TABLE $oqey_skins MODIFY skinid varchar(55)");

	}
   
    if (version_compare($installed_oqey_ver, '0.5', '<')){
	 $perm = get_role('administrator');	
     $perm->add_cap('oQeyVideo');	
     
     //$wpdb->query("ALTER TABLE $oqey_skins MODIFY skinid varchar(55)");
     
     $wpdb->query("ALTER TABLE $oqey_images ADD video_id int(11) NOT NULL DEFAULT '0' AFTER img_type");
     $wpdb->query("ALTER TABLE $oqey_images ADD meta_data longtext NOT NULL DEFAULT '' AFTER video_id");
     $wpdb->query("ALTER TABLE $oqey_music ADD path varchar(255) NOT NULL DEFAULT '' AFTER link");
     $wpdb->query("ALTER TABLE $oqey_music ADD type varchar(55) NOT NULL DEFAULT '' AFTER status");
     
     
     if(!$wpdb->get_var("SHOW TABLES LIKE '$oqey_video'")){
     $sql7 = "CREATE TABLE `" . $oqey_video . "` (
		`id` int NOT NULL AUTO_INCREMENT,
		`post_id` int(11) NOT NULL DEFAULT '0',
		`oqey_parent` int(11) NOT NULL DEFAULT '0',
		`title` varchar(255) NOT NULL DEFAULT '',
		`video_link` varchar(255) NOT NULL DEFAULT '',
        `video_image` varchar(255) NOT NULL DEFAULT '',
        `type` varchar(55) NOT NULL DEFAULT '',
        `description` text NOT NULL DEFAULT '',
        `status` int(1) NOT NULL DEFAULT '0',
        `vorder` int(1) NOT NULL DEFAULT '0',
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
    $wpdb->query($sql7);
    }
     
  /*Scan server fo the mp3 files*/ 
  $ext = array('mp3' );
  $root = rtrim(OQEY_ABSPATH, '/');
  $files = oqey_get_all_files($root, $ext );
  $oqey_music = $wpdb->prefix . "oqey_music";
  $root = $root."/";
  $site_url = get_option('siteurl').'/';

  foreach($files as $file){
    
    $path = pathinfo($file);
   
    $pre_link = str_replace(OQEY_ABSPATH, $site_url, $path['dirname']);    
    $audio_file = trim(sanitize_title($path['filename']).".".$path['extension']);//new song title
    $audio_link = $pre_link."/".$audio_file;
    $audio_path = $path['dirname']."/".$audio_file;
    $music_title = $path['filename'];
    
    $ext = array('mp3', 'MP3'); 
    if ( in_array( strtolower($path['extension']), $ext) ){	
  
       if(!$sql=$wpdb->get_row( $wpdb->prepare("SELECT * FROM $oqey_music WHERE link = %s ", $audio_file) ) ){
      
         $wpdb->query( $wpdb->prepare( "INSERT INTO $oqey_music (link, path, title, type) 
                                                         VALUES ( %s, %s, %s, %s)", 
                                                                 $audio_link,
                                                                 $audio_path,
                                                                 $music_title,
                                                                 "other"
                                                         )
                                                         );
         rename($file, $audio_path);
    
       }else{
        
           $s = $wpdb->query( $wpdb->prepare("UPDATE $oqey_music SET link = %s, path = %s, type = %s WHERE link = %s ", $audio_link, $audio_path, "oqey", $audio_file ));
        
       }
    
    }

  }
     
 }
 /*
 if (version_compare($installed_oqey_ver, '0.5.1', '<')){     
	
     $wpdb->query("ALTER TABLE $oqey_galls ADD wtmrk_status varchar(255) NOT NULL DEFAULT 'default' AFTER skin_id");
     $wpdb->query("ALTER TABLE $oqey_galls ADD permalink varchar(255) NOT NULL DEFAULT 'default' AFTER wtmrk_status");

 } */
 
    
    update_option( "oqey_db_version", $oqey_db_version );        
}

//.................................................................//	
function oqey_no_skins_installed(){
      echo '<div class="error fade" style="background-color:#ff8c7a;width:887px;"><p>';
      printf(__('oQey Gallery Plugin didn`t detect any active slideshow skins. Would you like to install one? If so, please click <a href="%s">here</a>.', 'oqey-gallery'), admin_url('admin.php?page=oQeySkins&showskins=yes') );
      echo '</p></div>';
}

function oqey_init_method() { 
    
   $oqey_dir = basename(dirname(__FILE__));
   load_plugin_textdomain('oqey-gallery', false, $oqey_dir . '/languages'); 
   oqey_check_upgrade();//make update if need to do

   wp_enqueue_script('swfobject'); 
   wp_enqueue_script('jquery');  
   
   wp_enqueue_script('oqey-social', WP_PLUGIN_URL . '/oqey-gallery/js/oqey-social.js', array('jquery')); 
   //wp_enqueue_script('oqey-social', WP_PLUGIN_URL . '/oqey-gallery/js/oqey.js', array('jquery')); 
   
   //wp_enqueue_script('oqeygalleryjs', WP_PLUGIN_URL . '/oqey-gallery/js/oqey-gallery.js');   
   wp_register_style('oQey-front-css', WP_PLUGIN_URL . '/oqey-gallery/css/oqeystyle.css');
   wp_enqueue_style('oQey-front-css');
   
   if(is_admin() && ($_GET['page']=='oQeysettings' || $_GET['page']=='oQeyGalleries' || $_GET['page']=='oQeySkins' || $_GET['page']=='oQeyMusic' || $_GET['page']=='oQeyTrash' || $_GET['page']=='oQeyVideo' ) ){
      
      wp_register_style('oQey-admin-css', WP_PLUGIN_URL . '/oqey-gallery/css/oqeyadmin.css');
      wp_enqueue_style('oQey-admin-css');
      wp_register_style('oQey-admin-pop-css', WP_PLUGIN_URL . '/oqey-gallery/css/jquery-ui.css');
      wp_enqueue_style('oQey-admin-pop-css');
      wp_enqueue_script('jquerysimplemodal', WP_PLUGIN_URL . '/oqey-gallery/js/jquery.loadimages.min.js', array('jquery'));
      wp_enqueue_script('jqueryeditable', WP_PLUGIN_URL . '/oqey-gallery/js/jquery.jeditable.js', array('jquery'));   
      wp_enqueue_script('jqueryfarbtastic', WP_PLUGIN_URL . '/oqey-gallery/js/farbtastic.js', array('jquery'));   
   
    if($_GET['page']=='oQeyGalleries'){
     
     wp_enqueue_script('jqueryajaxupload', WP_PLUGIN_URL . '/oqey-gallery/js/ajaxupload.js', array('jquery')); 
   
     /*Admin if no skins is installed*/
      global $wpdb;
      $oqey_skins = $wpdb->prefix . "oqey_skins";
      $r = $wpdb->get_results( "SELECT skinid FROM $oqey_skins WHERE status !='2'"); 
      if(empty($r)){ add_action( 'admin_notices', 'oqey_no_skins_installed'); } 
     /*END*/
   
    }
    
    wp_enqueue_script('jquery-ui-core ');
    wp_enqueue_script('jquery-ui-sortable');  
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-selectable');
    wp_enqueue_script('jquery-ui-dialog');
   
   if (is_plugin_active('oqey-photo-cropper/oqeycropper.php') && $_GET['page']=='oQeyGalleries'){
    wp_enqueue_script('jqueryoqeyjCrop', WP_PLUGIN_URL . '/oqey-photo-cropper/js/jquery.jcrop.js', array('jquery')); 
    //wp_enqueue_script('jqueryoqeycropper', WP_PLUGIN_URL . '/oqey-photo-cropper/js/oqey.cropper.js', array('jquery'));
    wp_register_style('oQey-admin-jCrop', WP_PLUGIN_URL . '/oqey-photo-cropper/css/jquery.jcrop.css');
    wp_enqueue_style('oQey-admin-jCrop');
   }elseif(is_plugin_active('oqey-addons/oqeyaddons.php')){
    
    wp_enqueue_script('jqueryoqeyjCrop', WP_PLUGIN_URL . '/oqey-addons/js/jquery.jcrop.js', array('jquery')); 
    //wp_enqueue_script('jqueryoqeycropper', WP_PLUGIN_URL . '/oqey-photo-cropper/js/oqey.cropper.js', array('jquery'));
    wp_register_style('oQey-admin-jCrop', WP_PLUGIN_URL . '/oqey-addons/css/jquery.jcrop.css');
    wp_enqueue_style('oQey-admin-jCrop');
    
   }
   
   if($_GET['page']=="oQeyGalleries"){ 
    
     if(!function_exists("gd_info")){ add_action( 'admin_notices', 'oqey_gd_error'); }
 
   }
   
   if($_GET['page']=="oQeyTrash"){ 
     wp_enqueue_script('jquery-ui-tabs'); 
   }
  
 }
}  
 
add_action('init', 'oqey_init_method');

function oqey_admin_custom_css_head(){
    
   $css = WP_PLUGIN_URL . '/oqey-gallery/css/oqeyadmin-ie.css';
   echo '<!--[if IE]><link rel="stylesheet" type="text/css" href="'.$css.'" /><![endif]-->';
   echo "\n";

}

add_action('admin_head', 'oqey_admin_custom_css_head');

/* reactivate the plugin for multisite subdomains*/
function oqey_init_method_gallery_multisite(){
global $wpdb;
$oqey_galls = $wpdb->prefix . "oqey_gallery";
if($wpdb->get_var("show tables like '$oqey_galls'") != $oqey_galls ){ oqey_db_install(); }
}
/*end*/ 

 if ( is_multisite() ) {
     
     add_action('init', 'oqey_init_method_gallery_multisite');
 
 } 
 
function oqey_gd_error(){
       echo '<div class="error fade" style="background-color:#E36464;">
             <p>'.__( 'Attention! Graphic Library missing. oQey Gallery requires GD library installed in order to run properly. Please install this php extension!', 'oqey-gallery' ).'</p></div>';
}

function oqey_php_version(){
     echo '<div class="error fade" style="background-color:#E36464;">
           <p>';
     printf(__('Attention! Your server php version is: %d oQey Gallery requires php version 5.2+ in order to run properly. Please upgrade your server!', 'oqey-gallery'), phpversion() );
     echo '</p></div>';
}
if( version_compare( '5.2', phpversion(), '>' ) ){ add_action( 'admin_notices', 'oqey_php_version'); }

function oqey_safe_mode(){
   if(ini_get('safe_mode')){
   
      echo '<div class="error fade" style="background-color:#E36464;">
            <p>'.__( 'Attention! Your server safe mode is: ON. oQey Gallery requires safe mode to be OFF in order to run properly. Please set your server safe mode option!', 'oqey-gallery' ).'
            </p></div>';
   }
}

if( version_compare( '5.3', phpversion(), '>' ) ){ add_action( 'admin_notices', 'oqey_safe_mode'); }

require("gallcore.php");
?>