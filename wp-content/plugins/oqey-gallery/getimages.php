<?php
include ("../../../wp-load.php");
global $wpdb;   

if(isset($_REQUEST['gal_id'])){

   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_images = $wpdb->prefix . "oqey_images";

   $data = explode("-", $_REQUEST['gal_id']);
   $id = esc_sql( $data[0] );
   $pid = $data[1];//post id
   $s = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_galls WHERE id = %d ", $id ) );

   $gthmb = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$s->folder.'/galthmb/';
   $gimg = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$s->folder.'/galimg/';


   if(!empty($m[0])){
    
     $gthmb2 = "";
     $gimg2 = "";
     
   }else{
    
     $gthmb2 = $gthmb;
     $gimg2 = $gimg;
     $gthmbnew = "";
     $gimgnew = "";
     
  }

      if($_REQUEST['withvideo']=="true"){
         
         $sqlrequest = "";
        
      }else{
        
          $sqlrequest = "AND img_type!='video'";
        
      }
     
     if($s->splash_img !=0){
        
	 	 $bg = $wpdb->get_row("SELECT * FROM $oqey_images WHERE id ='".$s->splash_img."' AND status!=2 ");
									   
     if(!$bg){	
        
         $bg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE gal_id = %d AND status!= %d AND img_type != %s ORDER BY img_order ASC LIMIT 0,1 ", $id, "2", "video" ) );	
	 
     }
		
	 }else{ 

         $bg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE gal_id = %d AND status!=%d AND img_type != %s ORDER BY img_order ASC LIMIT 0,1 ", $id, "2", "video" ) );         
     } 
      
      
     if( $s->splash_only == 1){ 
        
         $imgs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE gal_id = %d AND id !='".$s->splash_img."' AND status!=2 ".$sqlrequest." ORDER BY img_order ASC", $id ) );
     
     }else{ 
     
         $imgs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE gal_id = %d AND status!=2 ".$sqlrequest." ORDER BY img_order ASC", $id ) ); 
     
     }
   
     if($bg->img_type=="nextgen"){
        
         $bg_image = get_option('siteurl').'/'.trim($bg->img_path).'/'.trim($bg->title);   
     
     }else{
     
         $bg_image = $gimg.trim($bg->title);
     
     }  
   
   header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
   $r .= '<?xml version="1.0" encoding="UTF-8"?>';
   $r .= '<oqeygallery bgpath="'.$bg_image.'" galtitle="'.urlencode($s->title).'" path="" imgPath="">'; 
    
   foreach($imgs as $i) { 
    
    if($i->img_type=="nextgen"){
        
       $gthmbnew = get_option('siteurl').'/'.trim($i->img_path).'/thumbs/thumbs_';
       $gimgnew = get_option('siteurl').'/'.trim($i->img_path).'/';   
     
     }elseif($i->img_type=="video"){
        
        $imgroot = OQEY_ABSPATH.trim($i->img_path);
        
        if(is_file($imgroot)){       
           
            $gthmbnew = get_option('siteurl').'/'.trim($i->img_path);

        
        }else{
            
            $gthmbnew = oQeyPluginUrl().'/images/no-2-photo.jpg';
            
        }
        
    }else{
     
       $gthmbnew = $gthmb;
       $gimgnew = $gimg;
     
    }
    
    if($i->img_type=="video" && $_REQUEST['withvideo']=="true"){
        
       $url = parse_url(urldecode($i->title));
       if( !empty($url['host']) ){
        
          $vurl = trim($i->title);
        
       }else{
        
          $vurl = get_option('siteurl').'/'.trim($i->title);
        
       }
          
       $r .= '<item>';
       $r .= '<thumb file="'.$gthmbnew.'" alt="'.urlencode(trim($i->alt)).'" comments="'.urlencode(trim($i->comments)).'" link="'.urlencode(trim($i->img_link)).'"/>';
       $r .= '<video type="video" file="'.$vurl.'" alt="'.urlencode(trim($i->alt)).'" comments="'.urlencode(trim($i->comments)).'" link="'.urlencode(trim($i->img_link)).'"/>';
       $r .= '</item>';
    
      }else{
      
       $r .= '<item>';
       $r .= '<thumb file="'.$gthmbnew.trim($i->title).'" alt="'.urlencode(trim($i->alt)).'" comments="'.urlencode(trim($i->comments)).'" link="'.urlencode(trim($i->img_link)).'"/>';
       $r .= '<image file="'.$gimgnew.trim($i->title).'" alt="'.urlencode(trim($i->alt)).'" comments="'.urlencode(trim($i->comments)).'" link="'.urlencode(trim($i->img_link)).'">';
       if($_REQUEST['withexif']=="true"){
        
        $r .= '<exif>';
                $exif = json_decode($i->meta_data);
             
             if(!empty($exif->Make)){ 
                
                $r .='<parametru name="Make" value="'.urlencode($exif->Make).'" />';
             
             }
             if(!empty($exif->Model)){
                
               $r .='<parametru name="Model" value="'.urlencode($exif->Model).'" />';
             
             }
             if(!empty($exif->DateTime)){
                
               $r .='<parametru name="DateTime" value="'.urlencode($exif->DateTime).'" />';
             
             }
             if(!empty($exif->Software)){
                
               $r .='<parametru name="Software" value="'.urlencode($exif->Software).'" />';
             
             }
             if(!empty($exif->Artist)){
                
               $r .='<parametru name="Artist" value="'.urlencode($exif->Artist).'" />';
             
             }
             if(!empty($exif->ExposureTime)){
                
               $r .='<parametru name="ExposureTime" value="'.urlencode($exif->ExposureTime).'" />';
             
             }
             if(!empty($exif->FNumber)){ 
                
                $r .='<parametru name="FNumber" value="'.urlencode($exif->FNumber).'" />';
             
             }
             if(!empty($exif->ExposureProgram)){ 
                
                $r .='<parametru name="ExposureProgram" value="'.urlencode($exif->ExposureProgram).'" />';
             
             }
             if(!empty($exif->ISOSpeedRatings)){ 
                
                $r .='<parametru name="ISOSpeedRatings" value="'.urlencode($exif->ISOSpeedRatings).'" />';
             
             }
             if(!empty($exif->COMPUTED->CCDWidth)){ 
                
                $r .='<parametru name="CCDWidth" value="'.urlencode($exif->COMPUTED->CCDWidth).'" />';
             
             }
             $exif = '';
        
        $r .= '</exif>';       
                
       }
       $r .= '</image>';
       $r .= '</item>';
       
    }    
    
 }

   $r .= '</oqeygallery>';
   
   echo $r;
  
  }else{ 
    
    die();
    
}
?>