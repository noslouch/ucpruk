<?php
if( 'POST' != $_SERVER['REQUEST_METHOD'] ){
header('Allow: POST');
header('HTTP/1.1 405 Method Not Allowed');
header('Content-Type: text/plain');
die("Access denied. Security check failed! What are you trying to do? It`s not working like that. ");
}
 
    $pathtome = dirname( dirname( dirname(__FILE__) ) );
    
    if($pathtome=="oqey_gallery"){
    	 
       require_once ("../../../../wp-load.php"); 
       global $wpdb;
       
    }else{
    	  
    	 require_once ("../../../../../wp-load.php");  
    	 global $wpdb;
    
   }

    unset($_POST['onLoad']);

/*make color for flash*/
function oqey_makeFlashColor($csscolor){	  
  $color = preg_replace('/#/i', '0x', $csscolor);
  return $color;
}

/*get domain name*/
function get_oqey_domain($url){
   
  $pieces = parse_url($url);  
  $domain = isset($pieces['host']) ? $pieces['host'] : '';
  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,7})$/i', $domain, $regs)){ return $regs['domain']; }
  return false;

}

/*objects to array*/
function object_to_array($mixed) {
    if(is_object($mixed)) $mixed = (array) $mixed;
    if(is_array($mixed)) {
        $new = array();
        foreach($mixed as $key => $val) {
            $key = preg_replace("/^\\0(.*)\\0/","",$key);
            $new[$key] = object_to_array($val);
        }
    }
    else $new = $mixed;
    return $new;       
}

/*get skin key*/
function oqey_get_skin_key($data){
    
    global $wpdb;
    $oqey_skins = $wpdb->prefix . "oqey_skins";
    $r = $wpdb->get_row( $wpdb->prepare( "SELECT comkey FROM $oqey_skins WHERE folder = %s ", esc_sql($data) ));
    
    return $r->comkey;
}

/*get skin ID*/
function oqey_get_skin_id($data){
    
    global $wpdb;
    $oqey_skins = $wpdb->prefix . "oqey_skins";
    $r = $wpdb->get_row( $wpdb->prepare( "SELECT skinid FROM $oqey_skins WHERE folder = %s ", esc_sql($data) ));
    
    return $r->skinid;
}

/*Save skins options*/
if(isset($_POST["senderchck"])){

 if (md5($_POST["senderchck"]) == "4a33f7eff916ee2388841e3dee2e6c92") {
    
     define('WP_ADMIN', true);
     
     if(isset($_POST['spntype'])){
      
        $arr = explode("--", base64_decode($_POST['spntype']) );	
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
     if ( !wp_verify_nonce($nonce, 'oqey-options-save') ) die("Access denied. Security check failed! What are you trying to do? It`s not working like that. ");
     if ( !is_user_logged_in() ) die('Login failure. You must be logged in.');
  
     $all = $_POST;
     unset($all['senderchck']);
     unset($all['spntype']);        
     
     $path = dirname(__FILE__);
     $dir = basename(rtrim($path, '/'));
     $options = "oqey_skin_options_".$dir;
     //update_option( $options , json_encode($all) ); //save options into db
     
      if ( get_option( $options ) != json_encode($all) && get_option( $options ) !='' ) {
           
           update_option( $options, json_encode($all) );
      
      } else {
           
           add_option( $options, json_encode($all), '', 'no' );
     
      }
     
     print("sent=1");
     die();
       
 }else{
   
     print("sent=0");
	  die();
 
 }
 
}
/*END - save skins options*/

/*GET skins options*/
if(!empty($_POST["requestchecker"])){

      $def = array(); 
       
      if(get_option('oqey_crop_images')=="on"){ 
        
        $crop = "true"; 
        $def["CropOption"] = "true";
        
      }else{ 
      
        $crop = "false"; 
        $def["CropOption"] = "false";
      
      }
      
      if(get_option('oqey_HideThumbs')=="on"){ 
        
        $HideThumbs = "true"; 
        $def["HideThumbs"] = "true";
                
      }else{ 
        
        $HideThumbs = "false"; 
        $def["HideThumbs"] = "false";
      
      }
      
      if(get_option('oqey_LoopOption')=="on"){ 
        
        $LoopOption = "true"; 
        $def["LoopOption"] = "true";
      
      }else{ 
        
        $LoopOption = "false"; 
        $def["LoopOption"] = "false";
        
      }

        $maxw = get_option('oqey_width');
        $maxh = get_option('oqey_height');

      if(get_option('oqey_limitmax')=="on"){
    
        if(get_option('oqey_max_width')!=""){ $maxw = get_option('oqey_max_width'); }
        if(get_option('oqey_max_height')!=""){ $maxh = get_option('oqey_max_height'); }
        
        $maximum .= 'MaximumWidth='.$maxw.'&MaximumHeight='.$maxh.'&';
        
        $def["MaximumWidth"] = $maxw;
        $def["MaximumHeight"] = $maxh;

      }else{ 
     
        $maximum .= ''; 
      
      }

      if(get_option('oqey_BorderOption')=="on"){ 
        
        $BorderOption = "true"; 
        $def["BorderOption"] = "true";
        
       }else{ 
        
        $BorderOption = "false"; 
        $def["BorderOption"] = "false";
        
      }
      
      if(get_option('oqey_AutostartOption')=="on"){ 
        
          $AutostartOption = "true"; 
          $def["AutostartOption"] = "true";
         
        }else{ 
            
          $AutostartOption = "false"; 
          $def["AutostartOption"] = "false";
        
        }
 
      if(get_option('oqey_CaptionsOption')=="on"){
  
        $CaptionsOption = "true";
        $CaptionPosition = get_option('oqey_options');
        $maximum .= 'CaptionsOption='.$CaptionsOption.'&CaptionPosition='.$CaptionPosition.'&';
        $def["CaptionsOption"] = $CaptionsOption;
        $def["CaptionPosition"] = $CaptionPosition;
        
      }else{ 

        $CaptionsOption = "false";
        $maximum .= 'CaptionsOption='.$CaptionsOption.'&'; 
        $def["CaptionsOption"] = $CaptionsOption;
      
      }
      
      $def["TransitionType"] = get_option('oqey_effect_transition_type');
      $def["domain"] = get_oqey_domain(get_option('siteurl'));
      $def["BackgroundColor"] = oqey_makeFlashColor(get_option('oqey_bgcolor'));
      $def["BorderColor"] = oqey_makeFlashColor(get_option('oqey_border_bgcolor'));
      $def["TransitionInterval"] = get_option('oqey_pause_between_tran');
      $def["TransitionTime"] = get_option('oqey_effects_trans_time');
      $def["ThumbHeight"] = get_option('oqey_thumb_height');
      $def["ThumbWidth"] = get_option('oqey_thumb_width');
      $def["GalleryHeight"] = get_option('oqey_height');
      $def["GalleryWidth"] = get_option('oqey_width');
     

 if (md5($_POST["requestchecker"]) == "b898ec8530e7e5985263acafcd6a61ee"){
    
     $path = dirname(__FILE__);
     $dir = basename(rtrim($path, '/'));
     $options = "oqey_skin_options_".$dir;
 
     $all = json_decode(get_option($options));
         
     if(!empty($all)){
        
        $all = object_to_array($all);        
        $result = array_merge($def, $all);
        
     $j = 1;
     $r = "";

     foreach($result as $data=>$d){
     
      $d = stripslashes($d);
    
      if($j==1){
        
        $r .= $data."=".urlencode($d);     
        
      }else{
        
        $r .= "&".$data."=".urlencode($d);  
        
     }
    
     $j++;  
     
     }
     
      echo $r."&skinoptionsrecorded=true&registrationkey=".oqey_get_skin_key($dir);
 
    }else{
    
      echo $maximum.'skinoptionsrecorded=false&GalleryWidth='.get_option('oqey_width').'&GalleryHeight='.get_option('oqey_height').'&CropOption='.$crop.'&ThumbWidth='.get_option('oqey_thumb_width').'&ThumbHeight='.get_option('oqey_thumb_height').'&TransitionTime='.get_option('oqey_effects_trans_time').'&TransitionInterval='.get_option('oqey_pause_between_tran').'&HideThumbs='.$HideThumbs.'&LoopOption='.$LoopOption.'&BorderOption='.$BorderOption.'&BorderColor='.oqey_makeFlashColor(get_option('oqey_border_bgcolor')).'&BackgroundColor='.oqey_makeFlashColor(get_option('oqey_bgcolor')).'&AutostartOption='.$AutostartOption.'&domain='.get_oqey_domain(get_option('siteurl')).'&TransitionType='.get_option('oqey_effect_transition_type')."&registrationkey=".oqey_get_skin_key($dir);

    }   
 
  }

} 
/*END - GET skins options*/

/*Retrieve commercial key*/

    $oqey_skins = $wpdb->prefix . "oqey_skins"; 
    $path = dirname(__FILE__);
    $dir = basename(rtrim($path, '/'));
    $skinid = oqey_get_skin_id($dir);
    $skopt = "oqey_request_key_".$skinid;
    
    if( get_option($skopt) != "yes" ){
      
      if( $sql=$wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_skins WHERE skinid = %s AND commercial = %s AND firstrun = %d ", $skinid, "yes", 1 ) ) ){ 
        
        $url = "http://oqeysites.com/oqey/skins/oqeyverifykey.php";
        	
        $response = wp_remote_post( $url, array(
	    'method' => 'POST',
	    'timeout' => 45,
	    'redirection' => 5,
	    'httpversion' => '1.0',
	    'blocking' => true,
	    'headers' => array('User-Agent' => 'oQeySitesKeySpider'),
	    'body' => array( 'skinid' => esc_sql($skinid), 'domainurl' => get_option('siteurl') )
        )
        );
        
        $code = (int) wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        
           if( $code == 200 ){
            
            if($body!=""){
            
            $sql = $wpdb->query( $wpdb->prepare("UPDATE $oqey_skins SET comkey = %s WHERE skinid= %s ", 
                                                 esc_sql($body),
                                                 esc_sql($skinid)
                                     ) );
            
            }
            
              add_option( $skopt, 'yes', '', 'no' );
            
           }
        
        }
        
    }
/*END retrieve commercial key*/
?>