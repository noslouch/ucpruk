<?php
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'insert_in_post.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!');
   error_reporting(0);
   if(!$_SESSION){ session_start(); }
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_images = $wpdb->prefix . "oqey_images";

   $list = $wpdb->get_results("SELECT * FROM $oqey_galls WHERE status!=2 ORDER BY id DESC");
   $img_path = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="5" style="font-family:'Century Gothic'; font-size:12px;" class="tablesorter">
<thead>
<tr>
<th align="left">
*<?php _e('just click on picture to add the gallery', 'oqey-gallery'); ?>
</th>
</tr>
</thead>
<tbody>
<?php 
   $j=0;	
   if(!empty($list)){
   foreach ($list as $i){   // $list->qgal_id $list->title  
   
   $images = $wpdb->get_results("SELECT * FROM $oqey_images WHERE gal_id='".$i->id."' AND status!=2");
   
   if(!empty($images)){
    
   if($i->splash_img!=0){
    
    $img = $wpdb->get_row("SELECT * FROM $oqey_images WHERE id='".$i->splash_img."'");  
    
     if($img->img_type=="nextgen"){
       $gthmbnew = OQEY_ABSPATH.'/'.trim($img->img_path).'/thumbs/thumbs_'.$img->title;
     }else{
       $imgroot = $img_path.$i->folder.'/galthmb/'.$img->title;
     }
    
   if(!is_file($imgroot) || $img->status==2){
	
     $img = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE gal_id = %d AND status!= %d AND img_type != %s ORDER BY img_order ASC LIMIT 0,1 ", $i->id, "2", "video" ) );
   
   }
   
   }else{
   
     $img = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE gal_id = %d AND status!= %d AND img_type != %s ORDER BY img_order ASC LIMIT 0,1 ", $i->id, "2", "video" ) );
      
   }
   
   if($j%2){ $colorb = '#F4FAFF'; }else{ $colorb = '#E8F4FF'; }  
   
     if($img->img_type=="nextgen"){
       $gthmbnew = get_option('siteurl').'/'.trim($img->img_path).'/thumbs/thumbs_';
     }else{
       $gthmbnew = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).'/'.$i->folder.'/galthmb/';
     } 
    
	echo '<tr>	
	          <td style="background-color:'.$colorb.';">
			  <div>			  
		      <div style="float:left; width:120;">  
			  <a href="#null" onclick="inoQeyContent(\''.$i->id.'\')">
			  <img src="'.$gthmbnew.$img->title.'" alt="image" class="addinpost" style="border:0; max-width:120px; max-height:80px;"/></a>
              </div>	           
		      <div align="left" style="float:left; padding-left:15px;">'.$i->title.'</div>
			  </div>
			  </td>
          </tr>';
		  $j++;
	}
   }
 }else{ 
    
    echo '<tr><td><div alighn="left">';
    _e('There is no galleries found. Please create a gallery first.', 'oqey-gallery');
    echo '</div></td></tr>'; 
 
 }
?>
</tbody>
</table>
<script type="text/javascript">
function inoQeyContent($id){
var win = window.dialogArguments || opener || parent || top;
var html = "[oqeygallery id=" + $id + "]";
win.send_to_editor(html);
}
</script>