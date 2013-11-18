<?php if (!empty($_SERVER['SCRIPT_FILENAME']) && 'managetrash.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!'); 
error_reporting(0);

global $wpdb;
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_images = $wpdb->prefix . "oqey_images";
   $oqey_music = $wpdb->prefix . "oqey_music";
   $oqey_music_rel = $wpdb->prefix . "oqey_music_rel";
   $oqey_skins = $wpdb->prefix . "oqey_skins";
   $oqey_video = $wpdb->prefix . "oqey_video";

if(isset($_REQUEST['empty_trash']) && $_REQUEST['empty_trash']=="yes" && isset($_REQUEST['wpnonce'])){

if ( !wp_verify_nonce($_REQUEST['wpnonce'], 'oqey_empty_trash') ) die("You haven't rights to do this.");
if ( !current_user_can('oQeyTrash') ) die(__('You do not have sufficient permissions for this page.'));


	$imgs = $wpdb->get_results("SELECT * FROM $oqey_images WHERE status=2");

    foreach ($imgs as $img){
        
    $d = $wpdb->query("DELETE FROM $oqey_images WHERE id = '".$img->id."'");

     if($img->img_type=="nextgen"){
        //$ii = @unlink(OQEY_ABSPATH.'/'.trim($img->img_path).'/thumbs/thumbs_'.trim($img->title));
        //$tt = @unlink(OQEY_ABSPATH.'/'.trim($img->img_path).'/'.trim($img->title));       
      }else{
        
        $folder = oqey_get_gallery_folder($img->gal_id);
         
       	$ii = unlink(OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/galimg/'.trim($img->title));
        $tt = unlink(OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/galthmb/'.trim($img->title));
        
        if(is_file(OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/iphone/'.trim($img->title))){ 
           $ip = unlink(OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/iphone/'.trim($img->title));
        }
        
     } 
}	

/*end*/

/*delete all galleries*/
$get_g_list = $wpdb->get_results("SELECT * FROM $oqey_galls WHERE status = 2 ");

foreach ($get_g_list as $g){
    
  $d = $wpdb->query("DELETE FROM $oqey_galls WHERE id = '".$g->id."' ");
  $d = $wpdb->query("DELETE FROM $oqey_images WHERE gal_id = '".$g->id."'");
  $r = $wpdb->query("DELETE FROM $oqey_music_rel WHERE gallery_id = '".$g->id."' ");

  if($d){
   
   if(is_dir(OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).trim($g->folder))){   
     
     $dir = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).trim($g->folder);
     $do = oqey_rm($dir);
   } 
  }
}
/*end*/

/*delete skins*/
$get_skins = $wpdb->get_results("SELECT * FROM $oqey_skins WHERE status=2");
   
   foreach ($get_skins as $s){
    
     $skinid = oqey_get_skinid_by_id( esc_sql($s->id) ); 
     
     $d = $wpdb->query("DELETE FROM $oqey_skins WHERE id = '".$s->id."'");
     $u = $wpdb->query( $wpdb->prepare("UPDATE $oqey_galls SET skin_id = '0' WHERE skin_id = %d ", $s->id));

   if($d){
    
     $skopt = "oqey_request_key_".$skinid;
     delete_option($skopt);
    
     if(is_dir(OQEY_ABSPATH."wp-content/oqey_gallery/skins/".oqey_getBlogFolder($wpdb->blogid).trim($s->folder))){   
     
        $dir = OQEY_ABSPATH."wp-content/oqey_gallery/skins/".oqey_getBlogFolder($wpdb->blogid).trim($s->folder);
        $do = oqey_rm($dir);
     
     }
   }
}
/*end*/

/*delete song*/
$get_music = $wpdb->get_results("SELECT * FROM $oqey_music WHERE status = 2 ");
    
    foreach ($get_music as $m){	
       
       $d = $wpdb->query("DELETE FROM $oqey_music WHERE id = '".$m->id."'");
       $r = $wpdb->query("DELETE FROM $oqey_music_rel WHERE music_id = '".$m->id."' ");
 
       if($d){
          
           if($m->type=="oqey"){
             
               if(is_file($m->path)){    
	      
                  $i = unlink($m->path);
          
               }
           }
      }
}
/*end*/

/*Delete all videos*/
  $videos = $wpdb->get_results("SELECT * FROM $oqey_video WHERE status = 2 ORDER BY ID DESC");
  $d = $wpdb->query("DELETE FROM $oqey_video WHERE status = 2 ");
  
  foreach($videos as $v){
    
        $wpdb->query("DELETE FROM $oqey_images WHERE video_id = '".$v->id."'");
    
  }

}

  $gal_dir_musica = OQEY_ABSPATH."wp-content/oqey_gallery/music";  
  $oqeyImagesRoot = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid);

  $get_music = $wpdb->get_results("SELECT * FROM $oqey_music WHERE status = 2 ");
  $get_skins = $wpdb->get_results("SELECT * FROM $oqey_skins WHERE status = 2 ORDER BY id DESC");
  $get_g_list = $wpdb->get_results("SELECT * FROM $oqey_galls WHERE status = 2 ORDER BY gall_order ASC, id DESC");
  $galls = $wpdb->get_results("SELECT COUNT($oqey_images.id) as numartotal, $oqey_images.gal_id,  $oqey_galls.title, $oqey_galls.folder
                               FROM $oqey_images, $oqey_galls
						      WHERE $oqey_images.status = 2 AND $oqey_images.gal_id = $oqey_galls.id AND $oqey_images.img_type != 'video'
							  GROUP BY $oqey_images.gal_id");
  $videos = $wpdb->get_results("SELECT * FROM $oqey_video WHERE status = 2 ORDER BY ID DESC");
            
?>
<div class="wrap">
        <h2>Trash <?php if(!empty($get_music) || !empty($get_skins) || !empty($get_g_list) || !empty($galls) || !empty($videos) ){ echo "| <a href='#empty_trash' id='empty_trash' class='empty_trash'>".__('Empty Trash', 'oqey-gallery')."</a>"; } ?></h2>
<div id="save" style="width:953px; margin-bottom:10px;"><?php echo $mesaj; ?></div>
</div> 
<div id="tabs" style="display:none;">
	<ul id="taburile">
		<?php if(!empty($get_music)){ ?><li><a href="#tabs-1"><?php _e('Music', 'oqey-gallery'); ?></a></li><?php } ?>
        <?php if(!empty($get_skins)){ ?><li><a href="#tabs-2"><?php _e('Skins', 'oqey-gallery'); ?></a></li><?php } ?>
        <?php if(!empty($get_g_list)){ ?><li><a href="#tabs-3"><?php _e('Galleries', 'oqey-gallery'); ?></a></li><?php } ?>
        <?php if(!empty($galls)){ ?><li><a href="#tabs-4"><?php _e('Images', 'oqey-gallery'); ?></a></li><?php } ?>
        <?php if(!empty($videos)){ ?><li><a href="#tabs-5"><?php _e('Video', 'oqey-gallery'); ?></a></li><?php } ?>
	</ul>
    
<?php if(!empty($get_music)){ ?>    
<div id="tabs-1">
<div class="postbox" style="width:900px;" id="tabs-11">
<table width="900" border="0" cellspacing="0" cellpadding="3" id="musictable" class="tablesorter">
<tbody id="sortable">
<?php
	$i=1;
	$message = __('Are you sure you want to delete this song?', 'oqey-gallery');	
	$totalFiles = count($get_music);
	if($totalFiles>0){	
	foreach ($get_music as $music){
?>
  <tr style="padding:3px;" id="row_<?php echo $music->id; ?>">
    <td align="center" width="46" style="height:35px;">
    <div id="flashm<?php echo $i; ?>">
    <script type="text/javascript">
	var flashvars = {mpath:"<?php echo $music->link; ?>", flashId:"<?php echo $i; ?>", totalFiles:"<?php echo $totalFiles; ?>"};
	var params = {wMode:"transparent"};
	var attributes = {id: "playbtn<?php echo $i; ?>"};
	swfobject.embedSWF("<?php echo oQeyPluginUrl(); ?>/musiccs.swf", "flashm<?php echo $i; ?>", "30", "30", "8.0.0", "", flashvars, params, attributes);
    </script>
    </div>    
    </td>
    <td align="left" width="800">
    <div class="click" id="select_<?php echo $music->id; ?>"><?php echo $music->title; ?></div>    </td>
    <td width="27" align="right" valign="middle" class="lasttd">     
      <a href="#null" onclick="restoreSong('<?php echo $music->id; ?>'); return false;" class="hiddenm">
        <img src="<?php echo oQeyPluginUrl(); ?>/images/restore_button.png" width="24" height="24" title="<?php _e('Click to restore this song', 'oqey-gallery'); ?>"/></a>
    </td>
    <td width="27" align="left" valign="middle" class="lasttd">
      <a href="#null" onclick="deleteSong('<?php echo $music->id; ?>', '<?php echo $music->link; ?>'); return false;" class="hiddenm">
         <img src="<?php echo oQeyPluginUrl(); ?>/images/remove_button.png" width="24" height="24" title="<?php _e('Click to delete permanently this song', 'oqey-gallery'); ?>"/></a>
    </td>
  </tr>
<?php 
$i++;
} }else{ ?>
  <tr style="padding:3px;" id="row_<?php echo $music->id; ?>">
    <td colspan="4" align="center" style="height:35px;"><?php _e('There is no audio files for recovery.', 'oqey-gallery'); ?></td>
    </tr>
<?php } ?>
  </tbody>
</table>
</div>
</div>
<?php } ?>

<?php if(!empty($get_skins)){ ?>

<div id="tabs-2">
<div class="postbox" style="width:900px;" id="tabs-22">
<table width="900" border="0" cellspacing="0" cellpadding="15" class="tablesorter">
<tbody>
<?php if(!empty($get_skins)){ foreach ($get_skins as $r){ ?>
<tr id="skin_tr_<?php echo $r->id; ?>">
             <td width="170" height="120" align="center" valign="middle">
			 <img src="<?php echo oQeyPluginRepoUrl().'/skins/'.$r->folder.'/'.$r->folder; ?>.jpg" alt="skin" width="150" height="100" style="border:#999999 solid thin; margin-left:15px;"/>
			 </td>
             <td width="630" align="left" valign="top" style="margin-left:10px; padding:5px;">
			 <h4><?php echo urldecode($r->name); ?></h4>
             <p><?php echo urldecode($r->description); ?><br/>
             <?php _e('Skin files location', 'oqey-gallery'); ?>: <code>/skins/<?php echo $r->folder; ?></code>.</p>
			 <p>
               <a href="#restore" class="restore" onclick="restoreSkin('<?php echo $r->id; ?>'); return false;">Restore</a> | 
               <a href="#delete_this_skin" class="delete_this_skin" onclick="deleteSkinP('<?php echo $r->id; ?>', '<?php echo $r->folder; ?>'); return false;"><?php _e('Delete Permanently', 'oqey-gallery'); ?></a>
             </p>
			 </td>
             </tr>
<?php } } ?>
</tbody>
</table>
</div>
</div>
<?php } ?>

<?php if(!empty($get_g_list)){?>

<div id="tabs-3">
<?php 
$r = "";
$r .= '<div class="postbox" style="width:900px;" id="tabs-31">
       <table width="900" border="0" cellspacing="0" cellpadding="3" id="gallerytable" class="tablesorter">
       <tbody id="sortable">';
    if(!empty($get_g_list)){
	
    foreach ($get_g_list as $list){   
	 $r .=  '<tr id="row_'.$list->id.'">
             <td width="26" height="35" align="center" valign="middle">&nbsp;</td>
             <td width="820" height="35" align="left" valign="middle"><div id="gall_id_'.$list->id.'">'.$list->title.'</div></td>
             <td width="27" height="35" align="center" valign="middle" class="lasttd">
			 <a href="#restore" onClick="restoreGallery(\''.$list->id.'\'); return false;" class="hiddenm">
               <img src="'.oQeyPluginUrl().'/images/restore_button.png" width="24" height="24" title="'.__('Click to restore this gallery', 'oqey-gallery').'" /></a></td>
             <td width="27" align="center" valign="middle" class="lasttd"><a href="#delete" onClick="deleteGallery(\''.$list->id.'\', \''.$list->folder.'\'); return false;" class="hiddenm">
	           <img src="'.oQeyPluginUrl().'/images/remove_button.png" width="24" height="24" title="'.__('Click to delete permanently this gallery', 'oqey-gallery').'" /></a></td>
             </tr>';
	}
    
}else{ 

$r .= '<tr id="row_">
       <td align="center" height="35" valign="middle" colspan="3">'.__('There is no galleries for recovery.', 'oqey-gallery').'</td>
       </tr>';
}

$r .= '</tbody>
       </table>
	   </div>';
echo $r;
?>

</div><?php } ?>


<?php if(!empty($galls)){ ?>
<div id="tabs-4">
<?php 
$r = "";
$r .= '<table border="0" cellspacing="0" cellpadding="3">
       <tbody>';

foreach($galls as $gal){
    
	$imgs = $wpdb->get_results("SELECT * FROM $oqey_images WHERE status=2 AND img_type != 'video' AND gal_id = '".$gal->gal_id."' ORDER BY img_order ASC, id DESC");
    
    $r .= '<tr id="row_trashdiv'.$gal->gal_id.'"><td>';
	$r .= '<div class="trashdiv" id="trashdiv'.$gal->gal_id.'"><a href="#expand" class="expand_images">'.$gal->title.'</a><br/>';
	$r .= '<table width="800" border="0" cellspacing="0" cellpadding="0" class="tabledo">
           <tr>
            <td width="200" height="50" align="left" valign="middle">
             <div class="dodiv">
              <select name="dolist" class="dolist">
              <option value="0" selected="selected">'.__('Bulk Actions', 'oqey-gallery').'</option>
              <option value="2">'.__('Restore', 'oqey-gallery').'</option>
              <option value="3">'.__('Delete', 'oqey-gallery').'</option>
              </select>
	          <input type="button" name="doapply" class="doapply" value="'.__('Apply', 'oqey-gallery').'"/>
	          </div>
            </td>
            <td width="150" align="left" valign="middle"><input name="selectall" type="checkbox" class="selectall"/>&nbsp;<span class="seelectmessage">'.__('select all', 'oqey-gallery').'</span></td>
            <td width="396" align="left" valign="middle"><div class="messages">&nbsp;</div></td>
            <td width="54" align="left" valign="middle">&nbsp;</td>
          </tr>
          </table>';

	$r .= '<ul class="sortablegalls" style="display:none;">';

foreach($imgs as $img){
    
    if($img->img_type=="nextgen"){
       $gthmbnew = get_option('siteurl').'/'.trim($img->img_path).'/thumbs/thumbs_';
     }else{
       $gthmbnew = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$gal->folder.'/galthmb/';
     } 

if($s->splash_img==$img->id){ $b=' style="border:#7A82DE thin solid;" class="imgsel"'; }
                $r .= '<li id="img_li_'.$img->id.'"'.$b.'>
                       <div class="allbut" align="center">
		                 <input name="'.trim($img->title).'" type="checkbox" value="'.$img->id.'" class="styled" id="selected_'.$img->id.'" />
		               </div>
		               <img src="'.$gthmbnew.trim($img->title).'" alt="image_'.$img->id.'" class="trashimages"/>
                       </li>';
}
                $r .= '</ul>';
                $r .= '</div></td></tr>';
}
                $r .= '</tbody></table>';
                
                echo $r;
?>
</div>
<?php } ?>


<?php if(!empty($videos)){ ?>
<div id="tabs-5">
<div class="postbox" style="width:900px;" id="tabs-55">
<table width="900" border="0" cellspacing="0" cellpadding="15" class="tablesorter">
<tbody>
<?php if(!empty($videos)){ 
    
          foreach ($videos as $video){ 
            
            $imglink = oQeyPluginUrl().'/images/no-2-photo.jpg';
            
            if( !empty($video->video_image) ){ 
                
                $imgroot = OQEY_ABSPATH.trim($video->video_image);    
                $imglink = get_option('siteurl').'/'.trim($video->video_image); 
                
                if(!is_file($imgroot)){
                    
                    $imglink = oQeyPluginUrl().'/images/no-2-photo.jpg';
                    
                }
            
            }
?>
          <tr id="video_tr_<?php echo $video->id; ?>">
             <td width="170" height="120" align="center" valign="middle">
			 <img src="<?php echo $imglink ?>" alt="video" style="border:#999999 solid thin; margin-left:15px; height:100px; max-width:150px;"/>
			 </td>
             <td width="630" align="left" valign="top" style="margin-left:10px; padding:5px;">
			 <h4><?php echo urldecode($video->title); ?></h4>
             <p><?php echo urldecode($video->description); ?><br/></p>
			 <p><a href="#restore" class="restorevideo" onclick="restoreVideo('<?php echo $video->id; ?>'); return false;"><?php _e('Restore', 'oqey-gallery'); ?></a> | 
             <a href="#delete_this_video" class="delete_this_video" onclick="deleteVideo('<?php echo $video->id; ?>', '<?php echo $video->video_link; ?>'); return false;"><?php _e('Delete Permanently', 'oqey-gallery'); ?></a></p>
			 </td>
           </tr>
<?php } 
  
  } 
?>
</tbody>
</table>
</div>
</div>
<?php } ?>
</div>
<script type="text/javascript">
function hoverGallery(){
jQuery("#sortable tr").hover(
  function () {
  jQuery(this).children(".lasttd").children(".hiddenm").addClass("visiblem"); 
  },
  function () {
  jQuery(this).children(".lasttd").children(".hiddenm").removeClass("visiblem").addClass("hiddenm");
  }
);
}

jQuery(function() {
	
			jQuery(".trashdiv .expand_images").click( function(){ 
			 
			  if(jQuery(this).parent(".trashdiv").children(".sortablegalls").is(":visible")){			
			
                 jQuery(this).parent(".trashdiv").children(".sortablegalls").css("display", "none");
			     jQuery(this).parent(".trashdiv").children(".tabledo").css("display", "none");
			     jQuery(".ui-tabs").css("width", "945px");
			
              }else{
			    
                jQuery(".ui-tabs").css("width", "99%");
			    jQuery(this).parent(".trashdiv").children(".tabledo").fadeIn("slow");
			    jQuery(this).parent(".trashdiv").children(".sortablegalls").fadeIn("slow");
			  }
			
            });
			
			jQuery(".selectall").click(function(){ 
			   
               var x = jQuery(this).parents(".trashdiv");
			   var f = x.find('.styled');
			   var l = x.find('.sortablegalls li'); 
            
               if(jQuery(this).attr('checked')){
			
			       f.attr('checked', true);
                   jQuery(this).parent().children('.seelectmessage').text('clear all');
                   l.css('background-color', '#C0CFEF'); 
            
               }else{
                
                   f.attr('checked', false);
                   jQuery(this).parent().children('.seelectmessage').text('select all');
                   l.css('background-color', '#F8F8F8'); 
               }
			});//end of click 
			
  jQuery(".styled").click(function(){ 	
      
      if (jQuery(this).attr('checked')){ 
          
          jQuery(this).parent().parent().css('background-color', '#C0CFEF'); 
      
      }else{ 
          
          jQuery(this).parent().parent().css('background-color', '#F8F8F8'); 
      
      }
      
});


jQuery(".doapply").click(function(){ 

var dd = jQuery(this).parent();
var id = dd.find('.dolist').val();

		var x = jQuery(this).parents(".trashdiv");
		var galid = x.attr("id");
		var f = x.find('.styled:checked');
		var l = x.find('.sortablegalls li'); 
		
       

if(id==2){

        var names = [];
        f.each(function() {
            
		  if ( jQuery(this).parent().parent().is(':visible')){
		  
            names.push(this.value);			
		
          }
			jQuery(this).parent().parent().hide("slow");	
        
        });

		jQuery.post(ajaxurl, { action:"oQeyImagesFromTrash", imgallfromtrash: encodeURIComponent(names), galid: galid },
        function(data){
		if(names.length>0){
			x.find(".messages").hide().html('<p class="updated fade"><?php _e('All selected images was restored.', 'oqey-gallery'); ?><\/p>').fadeIn("slow");
			var data = eval('(' + data + ')');	
			setTimeout(function () { if(data.statusul=="no"){ jQuery("#row_"+galid).fadeOut(); } }, 1000);
		}else{
		x.find(".messages").hide().html('<p class="updated fade"><?php _e('Please select an image', 'oqey-gallery'); ?><\/p>').fadeIn("slow"); 
		clearUp(); 
		}
        });
}

if(id==3){

        var names = [];
        f.each(function() {
		if ( jQuery(this).parent().parent().is(':visible')){
            names.push(this.name);			
		}
			jQuery(this).parent().parent().hide("slow");	
        });

        jQuery.post(ajaxurl, { action:"oQeyImagePermDelete", imgalldelete: encodeURIComponent(names), galid: galid },
        function(data){
		if(names.length>0){			
			x.find(".messages").hide().html('<p class="updated fade"><?php _e('All selected images was deleted permanently.', 'oqey-gallery'); ?><\/p>').fadeIn("slow");			
			setTimeout(function () { if(data=="no"){ jQuery("#row_"+galid).fadeOut(); } }, 1000);
		}else{
		x.find(".messages").hide().html('<p class="updated fade"><?php _e('Please select an image', 'oqey-gallery'); ?><\/p>').fadeIn("slow"); 
		clearUp(); 
		}
        });
}

});//end doapply
});

/*VIDEO*/

function restoreVideo(id){ 
jQuery.post(ajaxurl, { action:"oQeyFromTrashVideo", id: id },
   function(data){
            var data = eval('(' + data + ')');
            jQuery("#video_tr_" + id).fadeOut('slow');
			if(data.statusul=="no"){ jQuery("#tabs-55").fadeOut(); }
			jQuery("#save").hide().html('<p class="updated fade">' + data.mesaj + '<\/p>').fadeIn("slow"); 
   });
}

function deleteVideo(id, name){ 
    
   jQuery.post(ajaxurl, { action:"oQeyVideoPermDelete", id: id, name: name },
   function(data){
            jQuery("#video_tr_" + id).fadeOut('slow');
			if(data=="no"){ jQuery("#tabs-55").fadeOut(); }
			jQuery("#save").hide().html('<p class="updated fade"><?php _e('Video was deleted.', 'oqey-gallery'); ?><\/p>').fadeIn("slow"); 
   });

}

/*END VIDEO*/

function clearUp(){ setTimeout(function () {  jQuery('#save').fadeOut(function(){ jQuery("#save").html("&nbsp;"); }); }, 2000); }

function deleteSong(id, name){ 
    
   jQuery.post(ajaxurl, { action:"oQeySongPermDelete", deletesong: id, name: name },
   function(data){
            jQuery("#row_" + id).fadeOut('slow');
			if(data=="no"){ jQuery("#tabs-11").fadeOut(); }
			jQuery("#save").hide().html('<p class="updated fade"><?php _e('Song was deleted.', 'oqey-gallery'); ?><\/p>').fadeIn("slow"); 
   });

}

function restoreSong(id){ 
jQuery.post(ajaxurl, { action:"oQeySongFromTrash", restoresong: id },
   function(data){
            jQuery("#row_" + id).fadeOut('slow');
			if(data=="no"){ jQuery("#tabs-11").fadeOut(); }
			jQuery("#save").hide().html('<p class="updated fade"><?php _e('Song was restored.', 'oqey-gallery'); ?><\/p>').fadeIn("slow"); 
   });
}

function restoreSkin(id){ 
jQuery.post(ajaxurl, { action: "oQeySkinFromTrash", undoskin: id },
   function(r){
	        var data = eval('(' + r + ')');
            jQuery("#skin_tr_" + id).fadeOut('slow');
			if(data.statusul=="no"){ jQuery("#tabs-22").fadeOut(); }
			jQuery("#save").hide().html('<p class="updated fade">' + decodeURIComponent(data.raspuns) + '<\/p>').fadeIn("slow"); 
   });
}

function deleteSkinP(id, name){ 
jQuery.post(ajaxurl, { action:"oQeySkinPermDelete", deleteskinper: id, name: name },
   function(data){
            jQuery("#skin_tr_" + id).fadeOut('slow');
			if(data=="no"){ jQuery("#tabs-22").fadeOut(); }
			jQuery("#save").hide().html('<p class="updated fade"><?php _e('Skin was deleted.', 'oqey-gallery'); ?><\/p>').fadeIn("slow"); 
   });
}

function restoreGallery(id){ 
jQuery.post(ajaxurl, { action:"oQeyGalleryFromTrash", undogallid: id },
   function(data){
	        var data = eval('(' + data + ')');		
            jQuery("#row_" + id).fadeOut('slow');
			if(data.statusul=="no"){ jQuery("#tabs-31").fadeOut(); }
			jQuery("#save").hide().html('<p class="updated fade"><?php _e('Gallery was restored.', 'oqey-gallery'); ?><\/p>').fadeIn("slow"); 
			
   });
}

function deleteGallery(id, name){ 
jQuery.post(ajaxurl, { action:"oQeyGalleryPermDelete", deletegall: id, name: name },
   function(data){
	        jQuery("#row_" + id).fadeOut('slow');
			if(data=="no"){ jQuery("#tabs-31").fadeOut(); }
			jQuery("#save").hide().html('<p class="updated fade"><?php _e('Deleted.', 'oqey-gallery'); ?><\/p>').fadeIn("slow"); 
   });
}


function hoverSongs(){
jQuery("#sortable tr").hover(
  function (){
      
      jQuery(this).children(".lasttd").children(".hiddenm").addClass("visiblem"); 
  
  },
  function () {
  
      jQuery(this).children(".lasttd").children(".hiddenm").removeClass("visiblem").addClass("hiddenm");
  
  }
);
}

jQuery(document).ready(function($) {	

jQuery("#empty_trash").click(function(){
	    if(confirm("<?php _e('Are you sure about erasing these data?', 'oqey-gallery'); ?>")){ 
		window.location = "<?php echo admin_url('admin.php?page=oQeyTrash&empty_trash=yes&wpnonce='.wp_create_nonce('oqey_empty_trash')); ?>"		
} 
});

var x = jQuery('ul#taburile li').size();
if(x>0){
jQuery('#tabs').tabs();
}else{
jQuery("#taburile").html("<li><?php _e('There is nothing in the trash.', 'oqey-gallery'); ?><\/li>");	
}


jQuery("#tabs").fadeIn("slow");

jQuery.loadImages([ '<?php echo oQeyPluginUrl().'/images/preview_button.png'; ?>', 
                    '<?php echo oQeyPluginUrl().'/images/remove_button.png'; ?>', 
                    '<?php echo oQeyPluginUrl().'/images/edit_button.png'; ?>' ],
                    function(){}
                 );
                 
hoverSongs();
});
	var oqeyname=1;
	var firstplay = true;
	function checkActivePlayer(newname) {
		if (!firstplay) {
        	getFlashMovie("playbtn" + oqeyname).sendIDToFlash(newname);
			oqeyname = newname;
		} else {
			oqeyname = newname;
			firstplay = false;
		}
	}
	function getFlashMovie(movieName){ var isIE = navigator.appName.indexOf("Microsoft") != -1;   return (isIE) ? window[movieName] : document[movieName]; }
</script>