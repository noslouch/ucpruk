<?php
//upload page
define('WP_ADMIN', true);
require_once("../../../wp-load.php");
include("thumbnail.class.php");

function oqey_wtmk_position($w){
    
    switch ($w) {
    case "TL":
        return array( 'TOP', 'LEFT' );
        break;
    case "TM":
        return array( 'TOP', 'CENTER' );
        break;
    case "TR":
        return array( 'TOP', 'RIGHT' );
        break;
    case "ML":
        return array( 'CENTER', 'LEFT' );
        break;
    case "MM":
        return array( 'CENTER', 'CENTER' );
        break;
    case "MR":
        return array( 'CENTER', 'RIGHT' );
        break;
    case "BL":
        return array( 'BOTTOM', 'LEFT' );
        break;
    case "BM":
        return array( 'BOTTOM', 'CENTER' );
        break;
    case "BR":
        return array( 'BOTTOM', 'RIGHT' );
        break;
  } 
}

function oqey_img_process($from, $to, $name, $height, $width, $wt, $id){
    
    $to = $to.$name;
    $thumb=new Thumbnail($from);    	 
    $thumb->size($width,$height);
    $thumb->quality=100;                      //default 75 , only for JPG format
        
    if(is_plugin_active('oqey-addons/oqeyaddons.php') && $wt){
        
      global $wpdb;
      $oqey_galls = $wpdb->prefix . "oqey_gallery";
      $d = $wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_galls WHERE id = %d ", esc_sql($id) ));
      
      if($d->wtmrk_status=="default"){ 
        
         $settings = json_decode(get_option("oqey_addons_watermark_settings"));    
         $pos = oqey_wtmk_position($settings->oqey_addons_watermark);
        
       }else{
        
         $settings = json_decode($d->wtmrk_status);      
         $pos = oqey_wtmk_position($settings->oqey_addons_watermark); 
         // {"oqey_addons_watermark_default":"1c","oqey_addons_watermark":"BR","oqey_W_X_margin":"150","oqey_W_Y_margin":"150"}
        
       }
      
      $thumb->output_format='JPG'; 
      $thumb->img_watermark=OQEY_ABSPATH.'wp-content/oqey_gallery/watermark/'.oqey_getBlogFolder($wpdb->blogid).'watermark.png';	    // [OPTIONAL] set watermark source file, only PNG format [RECOMENDED ONLY WITH GD 2 ]
      $thumb->img_watermark_Valing= $pos[0];   	    // [OPTIONAL] set watermark vertical position, TOP | CENTER | BOTTOM
      $thumb->img_watermark_Haling=$pos[1]; 
      //$thumb->txt_watermark='Watermark text';
      $thumb->txt_watermark_Hmargin=$settings->oqey_W_X_margin;          // [OPTIONAL] set watermark text horizonatal margin in pixels
      $thumb->txt_watermark_Vmargin=$settings->oqey_W_Y_margin;           // [OPTIONAL] set watermark text vertical margin in pixels
    }
    
      $thumb->memory_limit='128M';
      $thumb->max_execution_time='60';  
      $thumb->process();   				        // generate image
      //$thumb->show();						        // show your thumbnail, or    
      $thumb->save($to);			    // save your thumbnail to file, or
}

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
  if ( !wp_verify_nonce($nonce, 'oqey-upload') ) die("Access denied. Security check failed! What are you trying to do? It`s not working like that. ");
  if ( !is_user_logged_in() ) die('Login failure. You must be logged in.');
  if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to upload files.'));

  $oqey_images = $wpdb->prefix . "oqey_images";
  $oqey_galls = $wpdb->prefix . "oqey_gallery";

 if( isset($_REQUEST['loc']) || isset($_REQUEST['id'])){
  
  $folder = oqey_get_gallery_folder($gal_id);
  $filespath = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder."/galimg/";
  $filespaththumb= OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder."/galthmb/";
  $iphonepath= OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder."/iphone/";
  $gal_id = mysql_real_escape_string($gal_id);

 }

if($_FILES['Filedata']['size']>0){
    
    	if($_FILES["Filedata"]["size"]>0){
    	   
    		$path = pathinfo($_FILES["Filedata"]["name"]);	
            		
			$ext = array('jpeg', 'jpg', 'png'); 
		    if (in_array(strtolower($path['extension']), $ext)){
		      
			  if(!isset($path['filename'])){ 
			    
                $name = trim(sanitize_title(substr($path['basename'], 0,strpos($path['basename'],'.')))).".".$path['extension'];
                $name = ltrim($name, "_");	
                                
               }else{ 
                
                $name = ltrim(trim(sanitize_title($path['filename']).".".$path['extension'])); 
                $name = ltrim($name, "_");	
                                
              }

  			while(1){	
  			   
				if(!file_exists($filespath.$name)){	
				    
				   $name = trim(sanitize_title($path['filename']).".".$path['extension']);
				   $image_alt = $path['filename'];
				
                }else{		
				
                   $name = trim(sanitize_title($path['filename'])."_".time().".".$path['extension']);
				   $image_alt = $path['filename'];							
				
                }		
				
                $name = trim(strtolower($name));
                $name = ltrim($name, "_");
				$file = $filespath.$name; 	
                
                    ini_set('memory_limit', '-1');
                    $exif = wp_read_image_metadata($_FILES["Filedata"]["tmp_name"]); 
                   
                    oqey_img_process( $_FILES["Filedata"]["tmp_name"], $filespaththumb, $name, "100", "150", false, $gal_id);	
                    
                    if( is_plugin_active('oqey-addons/oqeyaddons.php') ){
                        
                        oqey_img_process( $_FILES["Filedata"]["tmp_name"], $filespath, $name, "1200", "1800", true, $gal_id);                         
                    
                    }else{
                        
  					    @move_uploaded_file($_FILES["Filedata"]["tmp_name"],$file);
                    
                    }
                                        
                    if(empty($exif)){
                        
                        $exif = "default";
                        
                    }                          
           
                      $wpdb->query( $wpdb->prepare( "INSERT INTO $oqey_images (title, gal_id, alt, status, img_type, meta_data) 
                                                                       VALUES (%s, %d, %s, %d, %s, %s)",
                                                                       $name,
                                                                       $gal_id,
                                                                       $image_alt,
                                                                       '0',
                                                                       'oqey',
                                                                       json_encode($exif)                                                                     
                                                  ) 
                                                  );

                      break;  	
          
  			}
    	}
	}
}
echo 'File uploaded';
?>