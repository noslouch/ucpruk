<?php
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'oqey-ajax.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!');

/*CROP ADDON ajax*/
//__('Settings', 'oqey-gallery')
//create new thumb
add_action( 'wp_ajax_createoQeyNewThumb', 'createoQeyNewThumb' );
function createoQeyNewThumb() {
    global $wpdb;
    $oqey_images = $wpdb->prefix . "oqey_images";
    $simage = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE id = %d AND status !=2", esc_sql($_POST['imgid']) ));
    
    if( !empty($simage) ){
        
    if($simage->img_type=="nextgen"){
         $iphoneimg = OQEY_ABSPATH.'/'.trim($simage->img_path).'/'.trim($simage->title);
         $iphoneimgthmb = OQEY_ABSPATH.'/'.trim($simage->img_path).'/thumbs/thumbs_'.trim($simage->title);
    }else{
        $iphoneimg = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).oqey_get_gallery_folder($simage->gal_id)."/galimg/".trim($simage->title);
        $iphoneimgthmb = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).oqey_get_gallery_folder($simage->gal_id)."/galthmb/".trim($simage->title);       
    }
    
    $multiplicator = $_POST['multiplicator'];    
    wp_crop_image($iphoneimg, ($_POST['x']*$multiplicator), ($_POST['y']*$multiplicator), ($_POST['w']*$multiplicator), ($_POST['h']*$multiplicator), $_POST['Qttw'], $_POST['Qtth'], "",$iphoneimgthmb );   
    }
}
/*CROP ADDON ajax*/

add_action( 'wp_ajax_oQeyGetWatermarkSettings', 'oQeyGetWatermarkSettings' );
function oQeyGetWatermarkSettings(){
    global $wpdb;
    
    $oqey_galls = $wpdb->prefix . "oqey_gallery";
    $d = $wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_galls WHERE id = %d ",$_POST['id'] ));
    $data = json_decode($d->wtmrk_status);

    if($d->wtmrk_status=="default"){ 
        
        $dchecked = 'checked="checked"';
        
        $data = json_decode(get_option("oqey_addons_watermark_settings"));
        
    }else{
        
        $cchecked = 'checked="checked"';        
        
    }
    
        if($data->oqey_addons_watermark=="TL"){ $checkedTL = 'checked="checked"'; }
        if($data->oqey_addons_watermark=="TM"){ $checkedTM = 'checked="checked"'; }
        if($data->oqey_addons_watermark=="TR"){ $checkedTR = 'checked="checked"'; }
        if($data->oqey_addons_watermark=="ML"){ $checkedML = 'checked="checked"'; }
        if($data->oqey_addons_watermark=="MM"){ $checkedMM = 'checked="checked"'; }
        if($data->oqey_addons_watermark=="MR"){ $checkedMR = 'checked="checked"'; }
        if($data->oqey_addons_watermark=="BL"){ $checkedBL = 'checked="checked"'; }
        if($data->oqey_addons_watermark=="BM"){ $checkedBM = 'checked="checked"'; }
        if($data->oqey_addons_watermark=="BR"){ $checkedBR = 'checked="checked"'; }
    
    
    echo '
<form name="oqey_wtm" id="oqey_wtm" method="POST" accept-charset="utf-8" >
    <table width="846" height="163" border="0" cellpadding="10" cellspacing="0" style="padding:10px;">
  <tr>
    <td colspan="3" align="left">'.__('Use Watermark Settings', 'oqey-gallery').'</td>
    <td width="42" rowspan="2" align="center" valign="middle"><div style="height:130px; background-color:#333333; width:1px;"></div>
    </td>
    <td width="210" align="left">'.__('Choose watermark position', 'oqey-gallery').':</td>
    <td width="114" align="left">'.__('Margins', 'oqey-gallery').':</td>
    <td width="166" align="left">'.__('Watermark image', 'oqey-gallery').':</td>
  </tr>
  <tr>
    <td width="72" align="center" valign="middle">
      <div align="center"><strong>'.__('Default', 'oqey-gallery').'</strong><br/>
        <br/>
        <input type="radio" name="oqey_addons_watermark_default" id="defaultw" value="0d" title="default" '.$dchecked.' />    
    </div></td>
    <td width="30"><strong>OR</strong></td>
    <td width="72" align="center" valign="middle">
      <div align="center"><strong>'.__('Custom', 'oqey-gallery').'</strong><br/>
        <br/>
        <input type="radio" name="oqey_addons_watermark_default" id="customtw" value="1c" title="custom" '.$cchecked.' />
      </div></td>
  <td>
    
<table border="0" cellspacing="0" cellpadding="5" class="oqey_radio_options" style="display:block; float:left; margin-left:-10px;" >
  <tr>
    <td width="30" height="30" align="center" valign="middle"><input type="radio" name="oqey_addons_watermark" id="tl" value="TL" title="top left" '.$checkedTL.' /></td>
    <td width="30" height="30" align="center" valign="middle"><input type="radio" name="oqey_addons_watermark" id="tm" value="TM" title="top middle" '.$checkedTM.'/></td>
    <td width="30" height="30" align="center" valign="middle"><input type="radio" name="oqey_addons_watermark" id="tr" value="TR" title="top right" '.$checkedTR.'/></td>
  </tr>
  <tr>
    <td width="30" height="30" align="center" valign="middle"><input type="radio" name="oqey_addons_watermark" id="ml" value="ML" title="middle left" '.$checkedML.'/></td>
    <td width="30" height="30" align="center" valign="middle"><input type="radio" name="oqey_addons_watermark" id="mm" value="MM" title="middle middle" '.$checkedMM.'/></td>
    <td width="30" height="30" align="center" valign="middle"><input type="radio" name="oqey_addons_watermark" id="mr" value="MR" title="middle right" '.$checkedMR.'/></td>
  </tr>
  <tr>
    <td width="30" height="30" align="center" valign="middle"><input type="radio" name="oqey_addons_watermark" id="bl" value="BL" title="bottom left" '.$checkedBL.'/></td>
    <td width="30" height="30" align="center" valign="middle"><input type="radio" name="oqey_addons_watermark" id="bm" value="BM" title="bottom middle" '.$checkedBM.'/></td>
    <td width="30" height="30" align="center" valign="middle"><input type="radio" name="oqey_addons_watermark" id="br" value="BR" title="bottom right" '.$checkedBR.'/></td>
  </tr>
</table>    
</td>
    <td>
    <table border="0">
			<tbody><tr>
				<td>x</td>
						<td><input name="oqey_W_X_margin" type="text" value="'.$data->oqey_W_X_margin.'" size="2" maxlength="3"> px</td>
					</tr>
					<tr>
						<td>y</td>
  						<td><input name="oqey_W_Y_margin" type="text" value="'.$data->oqey_W_Y_margin.'" size="2" maxlength="3"> px</td>
						</tr>
	</tbody></table>    
    </td>
    <td>    
    <img src="'.get_option('siteurl').'/wp-content/oqey_gallery/watermark/'.oqey_AddonBlogFolder($wpdb->blogid).'watermark.png?'.time().'" id="oqeywatermark" width="100" />    
    </td>
  </tr>
</table>
</form>
    ';
    
    die();
}

add_action( 'wp_ajax_oQeySaveWatermarkSettings', 'oQeySaveWatermarkSettings' );
function oQeySaveWatermarkSettings() {
    global $wpdb;    
    $oqey_galls = $wpdb->prefix . "oqey_gallery";
    $alldata = array();   
    $result =  explode("&", $_POST['data']);
    
    foreach($result as $r){
        
       $elem =  explode("=", $r);
       $alldata[$elem[0]] = $elem[1];
        
    }

     if($alldata['oqey_addons_watermark_default']=="1c"){
        
        $data = json_encode($alldata);
        
     }else{
        
        $data = "default";        
     
     }

    $sql = $wpdb->query( $wpdb->prepare("UPDATE $oqey_galls SET wtmrk_status = %s WHERE id = %d ", $data, $_POST['id']) ); 

    die();
}



/*START galleries ajax*/

/*Check for others galleries*/
add_action( 'wp_ajax_oQeyCheckForOthersGalleries', 'oQeyCheckForOthersGalleries' );
function oQeyCheckForOthersGalleries(){
    global $wpdb, $current_user;
    $nggpic = $wpdb->prefix . 'ngg_pictures';
	$nggal = $wpdb->prefix . 'ngg_gallery';
    $oqey_galls = $wpdb->prefix . "oqey_gallery";
    $oqey_images = $wpdb->prefix . "oqey_images";
    $resp = array();
    
    if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
    
    if (isset($_POST['info'])) {
        
        $do = $_POST['info'];
    
    }
	
    switch ($do) {
		case 'all':
		 
         if( $wpdb->get_var( "SHOW TABLES LIKE '$nggpic'" ) && $wpdb->get_var( "SHOW TABLES LIKE '$nggal'" ) ) { 
            
            echo __('From', 'oqey-gallery').'&nbsp;';
            echo '<select name="othersgalls" id="othersgalls">';
            echo '<option value="0" selected="selected">'.__('Select', 'oqey-gallery').'</option>';
            echo '<option value="nextgen">nextgen</option>';
            echo '</select>';
         
         }else{ 
            
            _e('Importable galleries not found.', 'oqey-gallery');
         
         }
         
        break;
		case 'nextgen':
        $all = $wpdb->get_results("SELECT * FROM $nggal");
        //echo "<pre>". print_r($all, true)."</pre>";
         echo "&nbsp;";
         echo '<select name="otgallstitle" id="otgallstitle">';
         echo '<option value="0" selected="selected">'.__('Select a gallery', 'oqey-gallery').'</option>';
         
         foreach($all as $g){
           echo '<option value="'.$g->gid.'">'.$g->title.'</option>';
         }
         echo '</select>';
         echo "<input id='importnewgall' class='importnewgall' type='button' style='display:none;' value='".__('Create', 'oqey-gallery')."'/>";
		break;
        
        case 'nextgencreate':
        $info = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $nggal WHERE gid = %d ", esc_sql($_POST['gid']) ));
        $folder = sanitize_title($info->title);
        $newtitle = esc_sql(stripslashes_deep(trim(urldecode($info->title))));

        if($sql=$wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_galls WHERE title = %s",$newtitle ))){ $newtitle = $newtitle.time(); }

        if($sql=$wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_galls WHERE folder = %s", $folder ))){ $folder = $folder.time(); }

        $add = $wpdb->query( $wpdb->prepare( "INSERT INTO $oqey_galls (title, post_id, author, folder) 
                                                               VALUES ('%s', '%d', '%d', '%s' )",
                                                               $newtitle,
                                                               $info->pageid,
                                                               $info->author,
                                                               $folder                                                               
                                           )
                                           );
        $lastid = mysql_insert_id();
        
        $resp["galid"] = $lastid;
        
        if($add){ 
            
            $img = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/galimg';
            $thumb = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/galthmb';   
            $iphone = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/iphone';   
            wp_mkdir_p($img);
            wp_mkdir_p($thumb);
            wp_mkdir_p($iphone);            
            
            $imgs = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $nggpic WHERE galleryid = %d ", esc_sql($_POST['gid']) ) );
            
            //echo "<pre>". print_r($imgs, true)."</pre>";
            //pid 	image_slug 	post_id 	galleryid 	filename 	description 	alttext 	imagedate 	exclude 	sortorder 	meta_data
            if(!empty($imgs)){
             
             foreach($imgs as $i){
                
              $wpdb->query("INSERT INTO $oqey_images (title, gal_id, img_order, alt, comments, status, img_link, img_path, img_type) VALUES ('".$i->filename."', '".$lastid."', '".$i->sortorder."', '".stripslashes_deep($i->alttext)."', '".stripslashes_deep($i->description)."', '0', '', '".$info->path."', 'nextgen')");
             
             }
            
            }
            
            $resp["response"]="Created";
                        
            echo json_encode($resp);
        }
        
		break;
			
		default:
        _e('There is no others galleries available at the moment.', 'oqey-gallery');
    }
    die();
}
/*END checking*/

/*Get all galleries*/
add_action( 'wp_ajax_oQeyGetAllGalleries', 'oQeyGetAllGalleries' );
function oQeyGetAllGalleries(){
    global $wpdb, $current_user;
    $oqey_galls = $wpdb->prefix . "oqey_gallery";
    
    if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
    
    $get_list = $wpdb->get_results("SELECT * FROM $oqey_galls WHERE status !=2 ORDER BY gall_order ASC, id DESC");
    $r = "";
    $r .= '<table width="900" border="0" cellspacing="0" cellpadding="0" id="gallerytable" class="tablesorter">
           <tbody id="sortable">';
    if(!empty($get_list)){
	 foreach ($get_list as $list){   
	 $r .=  '<tr id="row_'.$list->id.'">
             <td align="center" width="100" valign="middle" style="height:35px; padding-top:7px;">
			   <a href="#edit" onclick="getGalleryDetails(\''.$list->id.'\'); return false;">
			    <img src="'.oQeyPluginUrl().'/images/edit_button.png" title="'.__('Click to edit this gallery', 'oqey-gallery').'"/></a>
			   <a href="#preview" class="preview-gallery" id="'.$list->id.'">
                <img src="'.oQeyPluginUrl().'/images/preview_button.png" title="'.__('Click to preview this gallery', 'oqey-gallery').'"/></a>
			 </td>
             <td align="left" width="50">ID: '.$list->id.'</td>
             <td width="720" valign="middle"><div class="dblclick" id="gall_id_'.$list->id.'">'.$list->title.'</div></td>
             <td width="30" align="center" valign="middle" class="lasttd">
               <a href="#delete" onclick="deleteGallery(\''.$list->id.'\'); return false;" class="hiddenm">
			    <img src="'.oQeyPluginUrl().'/images/remove_button.png" width="24" height="24" title="'.__('Click to move to trash this gallery', 'oqey-gallery').'"/>
			 </a></td>
             </tr>';
 	  }
     }else{ 

     $r .= '<tr id="row_">
            <td align="center" width="50" style="height:35px;">&nbsp;</td>
            <td align="left" width="700" valign="middle">'.__('No galleries.', 'oqey-gallery').'</td>
            <td width="50" align="center" valign="middle" class="lasttd">&nbsp;</td>
            </tr>';
     }
     $r .= '</tbody></table>';
     echo $r;
     die();
}
/*END all galleries list*/

/*Preview gallery*/
add_action( 'wp_ajax_oQeyGetPreviewGallery', 'oQeyGetPreviewGallery' );
function oQeyGetPreviewGallery(){
    global $wpdb, $current_user;
    
    $oqey_galls = $wpdb->prefix . "oqey_gallery";
    $oqey_images = $wpdb->prefix . "oqey_images";
    $oqey_skins = $wpdb->prefix . "oqey_skins";
    
    if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));

    $galleryID = esc_sql($_POST['previewid']);
    
    $gal = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_galls WHERE id = %d ", $galleryID ) );
    
    $folder = $gal->folder;
    
    if($gal->skin_id!="0"){
        
       $skin = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE id = '".$gal->skin_id."'");
    
    }else{
    
       $skin = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE status = '1'");
    
    }
    
    $oqey_height = get_option('oqey_height');
    $oqey_width = get_option('oqey_width');
    $oqey_bgcolor = get_option('oqey_bgcolor');
    $plugin_url = oQeyPluginUrl();
    $plugin_repo_url = oQeyPluginRepoUrl();
    $show = "yes";

  	$get_bg_img = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_galls WHERE id = %d ", $galleryID ));			  
    $allimgs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE gal_id = %d ORDER BY img_order ASC", $galleryID ));

    $out .= '<div align="center" class="nofimg">';
     
    if(!empty($allimgs)){
         
         foreach($allimgs as $i){ 
            
             if($i->img_type=="nextgen"){
                $thumb = get_option('siteurl').'/'.trim($i->img_path).'/thumbs/thumbs_';
             }else{
                $thumb = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/galthmb/';       
             }  
         
             $out .= '<span class="noflashimg"><img src="'.$thumb.$i->title.'" alt="'.$i->alt.'"/></span>';
         }
         
      }else{
             echo '<span style="margin:10px; height:300px;">'.__('No images. Please upload some pictures.', 'oqey-gallery').'</span>';
             $show = "no";
      }
      $out .= '</div>';		  
      
      if($show=="yes"){
      if($_POST['flash']=="no"){
         echo '<div style="float:left;">'.$out.'</div>';
      }else{
         
         echo '<object height="600" width="896">
               <param value="#ffffff" name="bgcolor">
               <param value="true" name="allowFullScreen">
               <param name="movie" value="'.$plugin_repo_url.'/skins/'.oqey_getBlogFolder($wpdb->blogid).$skin->folder.'/'.$skin->folder.'.swf">
               <param name="FlashVars" value="flashId='.$skin->skinid.'&amp;FKey='.$skin->comkey.'&amp;GalleryPath='.$plugin_url.'&amp;GalleryID='.$galleryID.'&amp;FirstRun='.$skin->firstrun.'"><embed src="'.$plugin_repo_url.'/skins/'.oqey_getBlogFolder($wpdb->blogid).$skin->folder.'/'.$skin->folder.'.swf" bgcolor="#ffffff" FlashVars="flashId='.$skin->skinid.'&amp;FKey='.$skin->comkey.'&amp;GalleryPath='.$plugin_url.'&amp;GalleryID='.$galleryID.'&amp;FirstRun='.$skin->firstrun.'" width="896" height="600" wmode="transparent" allowFullScreen="true">
               </embed>
               </object>';
      }
     }
     die();
}
/*END Preview gallery*/


/*Edit gallery title*/
add_action( 'wp_ajax_oQeyEditGalleryTitle', 'oQeyEditGalleryTitle' );
function oQeyEditGalleryTitle(){
    global $wpdb, $current_user;
    $oqey_galls = $wpdb->prefix . "oqey_gallery";
    if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));

    $gal_id = str_replace("gall_id_", "", $_POST['id'] );
    $gal_id = esc_sql($gal_id);
    
    $newtitle = stripslashes_deep(trim(urldecode($_POST["value"])));

    if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_galls WHERE title = %s AND id != %d ",$newtitle, $gal_id))){ 
        $title = $newtitle;
    }else{
        $sql=$wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_galls WHERE title = %s AND id != %d ",$newtitle, $gal_id));
        $title = trim($sql->title.time());	
    }
 
    $gal = $wpdb->query( $wpdb->prepare("UPDATE $oqey_galls SET title = %s WHERE id= %d ", $title, $gal_id) );
    echo stripslashes_deep($title);
    
    die();
}
/*END Edit gallery title*/

/*CREATE new gallery*/
add_action( 'wp_ajax_oQeyNewGallery', 'oQeyNewGallery' );
function oQeyNewGallery(){
    global $wpdb, $current_user;
    $oqey_galls = $wpdb->prefix . "oqey_gallery";
    
if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));

$resp = array();

if($_POST["newtitle"] != ""){	

$folder = sanitize_title(urldecode($_POST["newtitle"]));
$newtitle = esc_sql(stripslashes_deep(trim(urldecode($_POST["newtitle"]))));

if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_galls WHERE title = %s ",$newtitle ))){

if($sql=$wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_galls WHERE folder = %s ", $folder ))){ $folder = $folder.time(); }

$add = $wpdb->query( $wpdb->prepare( "INSERT INTO $oqey_galls (title, author, folder) 
                                           VALUES ('%s', '%d', '%s' )",
                                           $newtitle,
                                           $current_user->ID,
                                           $folder                                   
                                   )
                                   );
$lastid = mysql_insert_id();

if($add){
   $img = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/galimg';
   $thumb = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/galthmb';   
   $iphone = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/iphone';   
   wp_mkdir_p($img);
   wp_mkdir_p($thumb);
   wp_mkdir_p($iphone);
   $resp["response"] .= 'Created';
   $resp["galid"] .= $lastid;
   $resp["last_gal"] .= '<li class="li_gallery" id="gall_id_'.$lastid.'">'.$newtitle.'<br/>
                         <span><a href="#edit" onclick="getGalleryDetails(\''.$lastid.'\'); return false;">edit</a></span>
						 <span><a href="#delete" onclick="deleteGallery(\''.$lastid.'\'); return false;">delete</a></span>
						 </li>';

}else{
$resp["response"] .= __('Error, try again please', 'oqey-gallery');
}	
}else{
$resp["response"] .= __('Gallery already exist', 'oqey-gallery');
}
}else{
$resp["response"] .= __('Title missing', 'oqey-gallery');	
}
echo json_encode($resp);

die();
}
/*END to CREATE new gallery*/

add_action( 'wp_ajax_oQeyNewGalleryID', 'oQeyNewGalleryID' );
function oQeyNewGalleryID(){
    global $wpdb, $current_user;
    $oqey_galls = $wpdb->prefix . "oqey_gallery";
    
    if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));

    $gal = $wpdb->get_row("SELECT * FROM $oqey_galls WHERE id ='".esc_sql($_POST['newgallid'])."'");

$resp = array();

if ( is_ssl() ){
	$cookies = $_COOKIE[SECURE_AUTH_COOKIE];
    }else{
	$cookies = $_COOKIE[AUTH_COOKIE];
}

$datele = $gal->id.'--'.$cookies.'--'.$_COOKIE[LOGGED_IN_COOKIE].'--'.$_POST['wpnonce'];

$resp["response"] .= '
<table width="900" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="32" align="right" valign="middle">
        <a href="#back_to_all_galleries" id="view_all_galleries"><img src="'.oQeyPluginUrl().'/images/back_to_list.png" title="'.__('back to all galleries', 'oqey-gallery').'" width="28" height="28" class="imgp"/></a>
    </td>
    <td width="32" align="right" valign="middle">
        <a href="#add_manage_music" id="oqeymusic"><img src="'.oQeyPluginUrl().'/images/music_button.png" title="'.__('add / manage music', 'oqey-gallery').'" width="28" height="28" class="imgp"/></a>
    </td>
    <td width="32" align="right" valign="middle">
        <a href="#add_manage_skins" id="oqeyskin"><img src="'.oQeyPluginUrl().'/images/skin_button.png" title="'.__('add / manage skins', 'oqey-gallery').'" width="28" height="28" class="imgp"/></a>
    </td>
    <td width="188" align="right" valign="middle">
    <div id="dodiv" style="margin-left:10px;">
    <select name="dolist" id="dolist">
      <option value="0" selected="selected">'.__('Bulk Actions', 'oqey-gallery').'</option>
      <option value="2">'.__('Move to Trash', 'oqey-gallery').'</option>
	  <option value="3">'.__('Reverse Order', 'oqey-gallery').'</option>
    </select>
	<input type="button" name="doapply" id="doapply" value="'.__('Apply', 'oqey-gallery').'"/>
	</div>
    </td>
    <td width="130" align="left" valign="middle"><input name="selectall" type="checkbox" id="selectall">&nbsp;<span id="seelectmessage">'.__('select all', 'oqey-gallery').'</span></td>    
    <td width="110" align="center" valign="middle">'.__('Gallery ID', 'oqey-gallery').': '.(int)$_POST['newgallid'].' </td>
    <td width="206" align="center" valign="middle">'.__('Shortcode', 'oqey-gallery').': <span style="background-color:#CCC;">[oqeygallery id='.(int)$_POST['newgallid'].'] </span></td>
    <td width="30" align="center" valign="middle">
    
       <a href="#add_video" id="addvideofile"><img src="'.oQeyPluginUrl().'/images/addvideo.png" title="'.__('add video file', 'oqey-gallery').'" class="imgp" width="28" height="28" /></a>
    
    </td>
    <td width="30" align="center" valign="middle">';
    
    if ( current_user_can('oQeyWatermark') ){ 
     if(is_plugin_active('oqey-addons/oqeyaddons.php')){
        
       $resp["response"] .='<a href="#watermark_settings" id="watermark-settings">
                               <img src="'.oQeyPluginUrl().'/images/watermark.png" title="'.__('watermark settings', 'oqey-gallery').'" class="imgp" width="28" height="28" />
                            </a>';
    
     }
    }
    
$resp["response"] .='</td>
      <td width="110" height="50" align="left" valign="middle">
      <div class="uploader" id="flashuploader">
      <div id="upload">'.__('Upload', 'oqey-gallery').'</div>
      <div id="status" ></div>
      <script type="text/javascript">
	  var flashvars = {BatchUploadPath:"'.base64_encode($datele).'",
		 			   Handler:"'.oQeyPluginUrl().'/bcupload.php",
					   FTypes:"*.jpg;*.png",
					   FDescription:"Media Files"};
	  var params = {bgcolor:"#FFFFFF", allowFullScreen:"true", wMode:"transparent"};
      var attributes = {id: "flash"};
	  swfobject.embedSWF("'.oQeyPluginUrl().'/demoupload.swf", "flashuploader", "110", "30", "8.0.0", "", flashvars, params, attributes);
      </script>
    </div>
    </td>
  </tr>
</table>';
//author: '.wp_dropdown_users("selected=".$aid->author."&echo=0").' 
$f = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$gal->folder;
if(!is_dir($f)){
  $ad = '(wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$gal->folder.')';  
  $folderexist = '<p class="error">';  
  $folderexist .= sprintf(__('Error creating the gallery folder (might be a server restrictions issue). Please create this folder manually  %1$s and set permissions to 755. '), $ad);
  $folderexist .= '</p>';
}

$resp["galid"] .= esc_sql($_POST['newgallid']);
$resp["titlul"] .= $gal->title;
$resp["noflashinfo"] = base64_encode($datele);
$resp["folder"] .= $gal->folder;
$resp["folderexist"] .= $folderexist;

echo json_encode($resp);

die();
}

add_action( 'wp_ajax_oQeyAddUserID', 'oQeyAddUserID' );
function oQeyAddUserID(){
    global $wpdb, $current_user;
    $oqey_galls = $wpdb->prefix . "oqey_gallery"; 
    if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
    $gal = $wpdb->query( $wpdb->prepare("UPDATE $oqey_galls SET author = %d WHERE id= %d ", esc_sql($_POST['usrid']), esc_sql($_POST['gid']) ) );
    die();
}

/*Show all gallery images*/
add_action( 'wp_ajax_oQeyGetAllImages', 'oQeyGetAllImages' );
function oQeyGetAllImages(){
   global $wpdb, $current_user;
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_images = $wpdb->prefix . "oqey_images";
    
   $oqeyImagesRoot = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid);
    
   if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
    
   $resp = array();

   $images = $wpdb->get_results("SELECT * FROM $oqey_images WHERE gal_id ='".esc_sql($_POST['id'])."' AND status !=2 ORDER BY img_order ASC, id DESC");
   $folder = oqey_get_gallery_folder($_POST['id'])."/galthmb/";
   $imgFolder = oqey_get_gallery_folder($_POST['id'])."/galimg/";
   $cropFolder = oqey_get_gallery_folder($_POST['id'])."/galimg/";

   $s = $wpdb->get_row( $wpdb->prepare( "SELECT splash_img FROM $oqey_galls WHERE id = %d ", esc_sql($_POST['id']) ));

   if(!empty($images)){ 
    
   $resp["allimages"] .=  '<div class="allimages">
                           <ul id="sortablegalls">';
   $i=0;
   $preload = array();
 
   foreach($images as $img){
    
      if($s->splash_img==$img->id){ $b=' style="border:#7A82DE thin solid;" class="imgsel"'; }

   /*cropper process*/
   $croper="";
   
   
   if($img->img_type=="nextgen"){
    
     $fullimg = get_option('siteurl').'/'.$img->img_path.'/thumbs/thumbs_'.trim($img->title);
     $previewimg = get_option('siteurl').'/'.trim($img->img_path).'/'.trim($img->title);
     
    }elseif($img->img_type=="video"){
        
        $imgroot = OQEY_ABSPATH.trim($img->img_path);
        
        if(is_file($imgroot)){       
           
            $fullimg = get_option('siteurl').'/'.trim($img->img_path);
            $previewimg = get_option('siteurl').'/'.trim($img->img_path);
        
        }else{
            
            $fullimg = oQeyPluginUrl().'/images/no-2-photo.jpg';
            $previewimg = oQeyPluginUrl().'/images/no-2-photo.jpg';
            
        }
        
    }else{
    
     $fullimg = $oqeyImagesRoot.$folder.trim($img->title);
     $previewimg = $oqeyImagesRoot.$imgFolder.trim($img->title);
    
    }
    
   
   if ( current_user_can('oQeyCropperRoles') && ( is_plugin_active('oqey-photo-cropper/oqeycropper.php') || is_plugin_active('oqey-addons/oqeyaddons.php') ) ){
   
   if($img->img_type=="nextgen"){
    
     $iphoneimg = OQEY_ABSPATH.'/'.$img->img_path.'/'.trim($img->title);
     $iphoneimgthumb = OQEY_ABSPATH.'/'.$img->img_path.'/thumbs/thumbs_'.trim($img->title);
     
     $cropimage = get_option('siteurl').'/'.$img->img_path.'/'.trim($img->title);
     $cropimagethmb = get_option('siteurl').'/'.$img->img_path.'/thumbs/thumbs_'.trim($img->title);   
     
   }else{
    
     $iphoneimg = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$cropFolder.trim($img->title);
     $iphoneimgthumb = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.trim($img->title);
     
     $cropimage = $oqeyImagesRoot.$cropFolder.$img->title;
     $cropimagethmb = $oqeyImagesRoot.$folder.$img->title;
   
   }
   
   list($twidth, $theight) = @getimagesize($iphoneimg);
   list($ttwidth, $ttheight) = @getimagesize($iphoneimgthumb);
   
   $orig_twidth = $twidth;
   $orig_theight = $theight;
   
   
    for ($multiplicator=1; $multiplicator<20; $multiplicator++){
    
        if ( $twidth>500 || $theight>330 ){
            
              $twidth = $orig_twidth/$multiplicator; 
              $theight = $orig_theight/$multiplicator;
         
        }else{
            $multiplicator = $multiplicator-1;
            break;
        }
     
     }
    
     if($img->img_type!="video"){
     
       $croper ='<a href="#image" onclick="showCropImage(\''.$img->id.'\', \''.$twidth.'\', \''.$theight.'\',\''.$ttwidth.'\', \''.$ttheight.'\', \''.urlencode(trim($cropimage)).'\', \''.urlencode(trim($cropimagethmb)).'\', \''.$multiplicator.'\'); return false;">
                     <img src="'.oQeyPluginUrl().'/images/cropperbutton.png" width="14" height="14" alt="image" title="'.__('crop image', 'oqey-gallery').'"/>
                 </a>';
       
       $delete = '<a href="#delete" onclick="deleteImage(\''.$img->id.'\'); return false;">
                     <img src="'.oQeyPluginUrl().'/images/remove_small_button.png" width="14" height="14" alt="move to trash" title="'.__('move image to trash', 'oqey-gallery').'"/>
                  </a>';    
    }else{
       
       $delete = '<a href="#delete" onclick="deleteVideo(\''.$img->id.'\'); return false;">
                     <img src="'.oQeyPluginUrl().'/images/remove_small_button.png" width="14" height="14" alt="move to trash" title="'.__('delete video', 'oqey-gallery').'"/>
                  </a>';  
                 
       $croper = "video";
        
    }

   }else{
    
     if($img->img_type!="video"){
       
       $delete = '<a href="#delete" onclick="deleteImage(\''.$img->id.'\'); return false;">
                     <img src="'.oQeyPluginUrl().'/images/remove_small_button.png" width="14" height="14" alt="move to trash" title="'.__('move image to trash', 'oqey-gallery').'"/>
                  </a>';    
    }else{
       
       $delete = '<a href="#delete" onclick="deleteVideo(\''.$img->id.'\'); return false;">
                     <img src="'.oQeyPluginUrl().'/images/remove_small_button.png" width="14" height="14" alt="move to trash" title="'.__('delete video', 'oqey-gallery').'"/>
                  </a>';  
                 
       $croper = "video";
        
    }
    
    
   }/*END with croper icon*/

   //<img src="'.oQeyPluginUrl().'/images/separator_line.png" width="120" height="2" align="middle" class="img_thumbs_top_line"/>

   $resp["allimages"] .= '
                       <li id="img_li_'.$img->id.'"'.$b.'><div class="allbut" align="center">
                       '.$delete.'                       
                       <a href="#image_details" onclick="showSettings(\''.$img->id.'\'); return false;">
                          <img src="'.oQeyPluginUrl().'/images/settings_small_button.png" width="14" height="14" alt="details" title="'.__('details', 'oqey-gallery').'"/>
                       </a>
		               
                       <a href="#image" onclick="showImage(\''.$img->id.'\', \''.urlencode(trim($previewimg)).'\'); return false;">
                          <img src="'.oQeyPluginUrl().'/images/details_small_button.png" width="14" height="14" alt="image" title="'.__('preview image', 'oqey-gallery').'"/>
                       </a>
                       '.$croper.'
		               <input name="selected" type="checkbox" value="'.$img->id.'" class="styled" id="selected_'.$img->id.'">
		               </div>
		               <img src="'.$fullimg.'?'.time().'" alt="image_'.$img->id.'" class="img_thumbs" />
                       </li>';
    $b = "";
    $preload[$i] = $previewimg;
    $i++;
    
    }
    
      $resp["allimages"] .=  '</ul></div>';
    
    }else{
    
      $resp["allimages"] .=  '<div class="allimages">';
      $resp["allimages"] .=  __('No images. Please upload some pictures.', 'oqey-gallery');	
      $resp["allimages"] .=  '</div>';
    
    }

      $resp["allimgpreload"] = $preload;
      
      echo json_encode($resp);
      
      die();
}
/*END to show all gallery images*/

add_action( 'wp_ajax_oQeyOrderAllImages', 'oQeyOrderAllImages' );
function oQeyOrderAllImages(){
   global $wpdb, $current_user;
   $oqey_images = $wpdb->prefix . "oqey_images";
   
   if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
    
   $r = str_replace("img_li[]=", "", $_POST['orderallimgs']);
   $gal_img_id = esc_sql($_POST['galleryid']);
   $r =  explode("&", $r);
   
   if(isset($_REQUEST['imgreverse']) && $_REQUEST['imgreverse']=="yes"){
   
      $r = array_reverse($r);
   
   }
   
   foreach ($r as $position => $item){
    
      $uimgs = $wpdb->query( $wpdb->prepare("UPDATE $oqey_images SET img_order = %d WHERE id = %d AND gal_id = %d ", $position, $item, $gal_img_id) );
   
   }
   
   die();

}

add_action( 'wp_ajax_oQeyOrderAndSelectMusic', 'oQeyOrderAndSelectMusic' );
function oQeyOrderAndSelectMusic(){
   global $wpdb, $current_user;
   $oqey_music = $wpdb->prefix . "oqey_music";
   $oqey_music_rel = $wpdb->prefix . "oqey_music_rel";

$gal_id = esc_sql($_POST['music_gall_id']);

$get_music_list = $wpdb->get_results("SELECT * 
								       FROM $oqey_music AS f 
							     INNER JOIN $oqey_music_rel AS s 
								 	     ON f.id = s.music_id
									  WHERE s.gallery_id = '$gal_id'
								   ORDER BY s.mrel_order ASC
										" ); 
				 
echo '<h4 style="padding-left:10px; margin-left:10px;">Select music</h4>
       <div style="muzica" id="muzica">
         <form action="#" name="musicselect" id="musicselect">
	       <ul id="sortablemuzon" name="sortablemuzon">';	
           
           $interogare = ""; 
           
           foreach ($get_music_list as $music){
          
          	echo '<li><input type="checkbox" checked="checked" value="'.$music->music_id.'" name="check_music_'.$music->music_id.'" id="check_music_'.$music->music_id.'"> '.$music->title.'</li>';	
            $interogare .= "id !='".$music->music_id."' AND ";
           }
           
           $get_music_list_all = $wpdb->get_results("SELECT * FROM $oqey_music WHERE ".$interogare." status !=2");

           foreach ($get_music_list_all as $musicall){
	           
               echo '<li><input type="checkbox" value="'.$musicall->id.'" name="check_music_'.$musicall->id.'" id="check_music_'.$musicall->id.'"> '.$musicall->title.'</li>';
           
           }

     echo '</ul>
           </form>      
	       </div>
	       <input type="button" name="savemusic" id="savemusic" value="'.__('Save changes', 'oqey-gallery').'" class="savemusic" style=" margin-left:20px; margin-bottom:10px;"/>';

     die();
}

add_action( 'wp_ajax_oQeySaveMusicOrder', 'oQeySaveMusicOrder' );
function oQeySaveMusicOrder(){
   global $wpdb, $current_user;
   $oqey_music_rel = $wpdb->prefix . "oqey_music_rel";
    
    if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
    
   $delete_music = $wpdb->query( $wpdb->prepare( "DELETE FROM $oqey_music_rel WHERE gallery_id = %d ", esc_sql($_POST['mgalleryid']) ));
   $i=0;

   if(!empty($_POST['selectedmusic'])){
    
     foreach ($_POST['selectedmusic'] as $l => $d){
   
      $min = $wpdb->query("INSERT INTO $oqey_music_rel (music_id, gallery_id, mrel_order) VALUES ('".$d['value']."', '".esc_sql($_POST['mgalleryid'])."', '".$i."')");
      $i++;
   
    }//end foreach
   }
  
   _e('Changes are saved.', 'oqey-gallery');
   
   die();
}


add_action( 'wp_ajax_oQeyGetAllSkins', 'oQeyGetAllSkins' );
function oQeyGetAllSkins(){
   global $wpdb, $current_user;
   
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_skins = $wpdb->prefix . "oqey_skins";
    
    //if ( !current_user_can('oQeySkins') ) die(__('You do not have sufficient permissions to do this.'));

   $s = $wpdb->get_row( $wpdb->prepare( "SELECT skin_id, status FROM $oqey_galls WHERE id = %d ", esc_sql($_POST['skin_gall_id']) ));

   if($s->skin_id !=0 && $s->status!=2){

      $r = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE id = '".$s->skin_id."'");

   }else{
      
      $r = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE status = '1'");

   }

if(!empty($r)){
 
echo '<table width="900" border="0" cellspacing="0" cellpadding="0" id="currentskintable">
      <thead>
        <tr>
          <th colspan="2" align="left" valign="middle" style="padding:15px;">'.__('Current skin', 'oqey-gallery').'</th>
        </tr>
      </thead>
      <tbody id="sortable">
	    <tr id="skink_tr_'.$r->id.'">
             <td width="180" height="120" align="center" valign="middle">
			   <div align="center">
                  <img src="'.oQeyPluginRepoUrl().'/skins/'.oqey_getBlogFolder($wpdb->blogid).$r->folder.'/'.$r->folder.'.jpg" alt="skin" width="150" height="100" style="border:#999999 solid thin;"/>
	           </div>
               </td>
               <td width="720" align="left" valign="top">
               <p align="left" style="padding:5px;"><b>'.urldecode($r->name).'</b><br/>'.urldecode($r->description).'<br/>
               '.__('Skin files location', 'oqey-gallery').': <code>/skins/'.oqey_getBlogFolder($wpdb->blogid).$r->folder.'</code>.
               </p> 
	          </td>       
        </tr>
      </tbody>
      </table>';
}else{
    
   echo '<table width="900" border="0" cellspacing="0" cellpadding="0" id="currentskintable">
          <thead>
            <tr>
             <th align="left" valign="middle" style="padding:15px;">'.__('Current skin', 'oqey-gallery').'</th>
            </tr>
           </thead>
          <tbody id="sortable">
	       <tr>
             <td width="180" height="120" align="center" valign="middle">
			 <p>'.__('No skins found.', 'oqey-gallery').'</p> 
             </td>       
           </tr>
          </tbody>
         </table>';
}


$get_list = $wpdb->get_results("SELECT * FROM $oqey_skins WHERE id !='".$r->id."' AND status != '2' ORDER BY id DESC");
echo '<table width="900" border="0" cellspacing="0" cellpadding="0" id="skintable" class="tablesorter">
       <thead>
        <tr>
         <th colspan="2" align="left" valign="middle" style="padding:15px;">'.__('Available skins', 'oqey-gallery').'</th>
        </tr>
       </thead>
      <tbody id="sortable">';

if(!empty($get_list)){
	foreach ($get_list as $r){   
       echo '<tr id="skink_tr_'.$r->id.'">
             <td width="180" height="120" align="center" valign="middle">
               <div align="center">
                 <img src="'.oQeyPluginRepoUrl().'/skins/'.oqey_getBlogFolder($wpdb->blogid).$r->folder.'/'.$r->folder.'.jpg" alt="skin" width="150" height="100" style="border:#999999 solid thin;"/>
	           </div>
             </td>
             <td width="720" align="left" valign="top">
	         <p align="left" style="padding:5px;"><b>'.urldecode($r->name).'</b><br/>'.urldecode($r->description).'<br/>
             '.__('Skin files location', 'oqey-gallery').': <code>/skins/'.oqey_getBlogFolder($wpdb->blogid).$r->folder.'</code>.<br/><br/>
			 <a href="#activate_skin" class="activate_skin" id="'.$r->id.'">'.__('Activate this skin', 'oqey-gallery').'</a>
        </p> 
	    </td>       
        </tr>';
	}
  }else{ 
     
     echo '<tr id="skink_tr_'.$r->id.'">
             <td align="center" height="30" valign="middle">'.__('No available skins.', 'oqey-gallery').'</td>
           </tr>';
 
 }

     echo '</tbody>
           </table>';
      
      die();
}

add_action( 'wp_ajax_oQeySetNewSkin', 'oQeySetNewSkin' );
function oQeySetNewSkin(){
    global $wpdb, $current_user;
    $oqey_galls = $wpdb->prefix . "oqey_gallery";   
    
    $s = $wpdb->query( $wpdb->prepare("UPDATE $oqey_galls SET skin_id = %d WHERE id = %d ", esc_sql($_POST['skinid']), esc_sql($_POST['skin_gallery_id']) ) ); 
    
    _e('New skin was set.', 'oqey-gallery');
    
    die();
}

/*Move all selected images to trash*/
add_action( 'wp_ajax_oQeyImagesToTrash', 'oQeyImagesToTrash' );
function oQeyImagesToTrash(){
   global $wpdb, $current_user;
   $oqey_images = $wpdb->prefix . "oqey_images";
   if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
    
   $i=0;
   $all = explode(",", urldecode($_POST['imgalltotrash']));

   foreach ($all as $id){
    
     $img = $wpdb->query( $wpdb->prepare( "UPDATE $oqey_images SET status = %d WHERE id = %d ", 2, $id ) ); 
     $i++;
   
   }
   
   if($i>0){

      _e('All selected images was moved to trash.', 'oqey-gallery');
      
   }else{

      _e('Please select an image.', 'oqey-gallery');
      
   }
   die();
}
/*END to Move all selected images to trash*/

add_action( 'wp_ajax_oQeyImagesFromTrash', 'oQeyImagesFromTrash' );
function oQeyImagesFromTrash(){
   global $wpdb, $current_user;
   $oqey_images = $wpdb->prefix . "oqey_images";
   if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
    
   $resp = array();
   $galid = str_replace("trashdiv", "", $_POST['galid']);
   $all = explode(",", urldecode($_POST['imgallfromtrash']));

   $i=0;
   foreach ($all as $id){
    
      $img = $wpdb->query( $wpdb->prepare("UPDATE $oqey_images SET status = %d WHERE id = %d ", 0, esc_sql($id) ) ); 
      $i++;
   
   }
   
   if($i>0){
   if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT id FROM $oqey_images WHERE gal_id = %d AND status = %d AND img_type != 'video' ", esc_sql($galid), 2 ))){ $e="no"; }
   $resp["statusul"] = $e;
   $resp["imgallfromtrash"] = $all;
   echo json_encode($resp);
   }
   die();
}

/*Gallery to trash*/
add_action( 'wp_ajax_oQeyGalleryToTrash', 'oQeyGalleryToTrash' );
function oQeyGalleryToTrash(){
   global $wpdb, $current_user;
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
   
   $img = $wpdb->query( $wpdb->prepare("UPDATE $oqey_galls SET status = %d WHERE id = %d ", 2, esc_sql($_POST['movetotrashgall']) ) ); 
   
   _e('Gallery was moved to trash.', 'oqey-gallery');
   
   die();
}
/*End Gallery to trash*/

/*Gallery from trash*/
add_action( 'wp_ajax_oQeyGalleryFromTrash', 'oQeyGalleryFromTrash' );
function oQeyGalleryFromTrash(){
   global $wpdb, $current_user;
   $oqey_galls = $wpdb->prefix . "oqey_gallery";    
   if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
    
   $resp = array();
   $gal = $wpdb->query( $wpdb->prepare("UPDATE $oqey_galls SET status = %d WHERE id = %d ", 0, esc_sql($_POST['undogallid']) ) ); 

   if($gal){
     
     if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT id FROM $oqey_galls WHERE status = %d ", 2 ))){ $e="no"; }
       $resp["statusul"] = $e;
       $resp["mesaj"] = __('Gallery was restored.', 'oqey-gallery');
       echo json_encode($resp);	
     }
   
   die();
}
/*END Gallery from trash*/

/*Image to trash*/
add_action( 'wp_ajax_oQeyImageToTrash', 'oQeyImageToTrash' );
function oQeyImageToTrash(){
   global $wpdb, $current_user;
   $oqey_images = $wpdb->prefix . "oqey_images";
   if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
   
   $img = $wpdb->query( $wpdb->prepare("UPDATE $oqey_images SET status = %d WHERE id = %d ", 2, esc_sql($_POST['delimgid']) ) ); 

   _e('Image was moved to trash.', 'oqey-gallery');
   
   die();
}
/*END Image to trash*/

/*Image from trash*/
add_action( 'wp_ajax_oQeyImageFromTrash', 'oQeyImageFromTrash' );
function oQeyImageFromTrash(){
   global $wpdb, $current_user;
   $oqey_images = $wpdb->prefix . "oqey_images";
   
   if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
   
     $img = $wpdb->query( $wpdb->prepare("UPDATE $oqey_images SET status = %d WHERE id= %d ", 0 , esc_sql($_POST['undoimgid']) ) );

     _e('Image was restored.', 'oqey-gallery');
   
   die();
}
/*END Image from trash*/

/*GET image details, alt, comments, link etc*/
add_action( 'wp_ajax_oQeyImageDetails', 'oQeyImageDetails' );
function oQeyImageDetails(){
   global $wpdb, $current_user;
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_images = $wpdb->prefix . "oqey_images";	
    
   if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));    

   $resp = array();
   $splash = 'off';
   $splashexclusive = 'off';
   
   $image = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE id = %d ", esc_sql($_POST['imagedetails']) ) );

   if($sql=$wpdb->get_row($wpdb->prepare("SELECT splash_img FROM $oqey_galls WHERE id = %d AND splash_img = %s ", $image->gal_id, esc_sql($_POST['imagedetails'])  ))){
      
      $splash = 'on';

   $s = $wpdb->get_row("SELECT splash_only FROM $oqey_galls WHERE id ='".$image->gal_id."'");
   
   if($s->splash_only=="1"){ $splashexclusive = 'on'; }
   
   }
   $resp['type'] .= $image->img_type;
   $resp['alt'] .= $image->alt;
   $resp['comments'] .= $image->comments;
   $resp['splash'] .= $splash;
   $resp['splashexclusive'] .= $splashexclusive;
   $resp['link'] .= $image->img_link;

   echo json_encode($resp);
   
   die();
}
/*END image details, alt, comments, link etc*/

/*Update image details alt, etc*/
add_action( 'wp_ajax_oQeyUpdateImageDetails', 'oQeyUpdateImageDetails' );
function oQeyUpdateImageDetails(){
   global $wpdb, $current_user;
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_images = $wpdb->prefix . "oqey_images";	
   if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
   
   $resp = array();

   $alt_up = $wpdb->query( $wpdb->prepare("UPDATE $oqey_images SET alt = %s, comments = %s, img_link = %s WHERE id = %d ", stripslashes_deep(urldecode($_POST['alt'])), stripslashes_deep(urldecode($_POST['comments'])), stripslashes_deep(urldecode($_POST['oqey_image_link'])), esc_sql($_POST['imgid']) ) ); 

   $m = "not";
   $c = $wpdb->get_row( $wpdb->prepare( "SELECT splash_img FROM $oqey_galls WHERE id = %d ", esc_sql($_POST['galid']) ) );

   if($c->splash_img==$_POST['imgid']){
   $splashk = $wpdb->query( $wpdb->prepare("UPDATE $oqey_galls SET splash_img = %d, splash_only = %d WHERE id = %d ", 0, 0, esc_sql($_POST['galid']) ));
   }

   if(!empty($_POST['splash']) && !empty($_POST['splashexclusive']) ){
    
   $splashk = $wpdb->query( $wpdb->prepare("UPDATE $oqey_galls SET splash_img = %d, splash_only = %d WHERE id = %d ", esc_sql($_POST['imgid']), 1, esc_sql($_POST['galid']) ));
   $m = "yes";
   
   }elseif(!empty($_POST['splash'])){	 
   $splashk = $wpdb->query( $wpdb->prepare("UPDATE $oqey_galls SET splash_img = %d, splash_only = %d WHERE id = %d ", esc_sql($_POST['imgid']), 0, esc_sql($_POST['galid']) ));   
   $m = "yes";
   }
   
   $resp['splash'] .= $m;
   $resp['id'] .= $_POST['imgid'];

   echo json_encode($resp);
   
   die();
}
/*END image details update*/

/*END galleries ajax*/

/*START skins ajax*/

/*Get more skins from oqesites.com*/
add_action( 'wp_ajax_oQeyGetNewSkins', 'oQeyGetNewSkins' );
function oQeyGetNewSkins(){
global $wpdb, $current_user;
   $oqey_skins = $wpdb->prefix . "oqey_skins";
   if ( !current_user_can('oQeySkins') ) die(__('You do not have sufficient permissions to do this.'));
	
   $xml = @simplexml_load_file('http://oqeysites.com/skinsxml/skins.xml');    

    if(!empty($xml)){

       echo '<table class="tablesorter" border="0" cellpadding="15" cellspacing="0" width="900">
             <tbody id="sortable">';
	   $e = 0;	 
	   foreach($xml as $x){

       if(!$sql = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $oqey_skins WHERE skinid = %s ", esc_sql($x->skinid) ))){		   
	   
       $install = '<a href="#install_skin" class="install_skin" id="'.$x->archivename.'" >Install</a>';
       
       if($x->price!="" && $x->commercial=="yes"){ 
          
          $price = __('Skin price', 'oqey-gallery').': $'.urldecode($x->price).'<br/>'; 
       
       }else{ 
          
          $discount_code = "<br/>"; 
       
       }
	   
       if($x->commercial=="yes"){ 
          
          $info = ' - '.__('Commercial skin', 'oqey-gallery'); 
          
       }else{ 
          
          $info = ' - '.__('Free skin', 'oqey-gallery'); 
       
       }
       
       $preview = '<a href="http://oqeysites.com/?page_id='.$x->postid.'" id="'.$x->archivename.'" title="'.__('Preview', 'oqey-gallery').' '.urldecode($x->title).'" target="_blank">'.__('Preview', 'oqey-gallery').'</a>';
       
	   echo '<tr>
             <td align="center" valign="middle" width="170" height="120">
			   <img src="http://oqeysites.com/skinsxml/'.$x->archivename.'/'.$x->archivename.'.jpg" alt="skin" style="border: thin solid rgb(153, 153, 153); margin-left: 15px;" width="150" height="100">
			 </td>
             <td style="margin-left: 10px; padding: 5px;" align="left" valign="top" width="730">
             <h4>'.urldecode($x->title).$info.'</h4>
             <p>'.urldecode($x->description).'
             <br/>'.$price.'  </p>           
			 <p>'.$install.' | '.$preview.'</p>
			 </td>
             </tr>';
             
             $price = "";
             $e ++;
	    }         
	  }
      if($e==0){ echo '<tr><td valign="middle" style="height:30px; padding-left: 20px;">'.__('All available skins are already installed...', 'oqey-gallery').'</tr></td>'; }
      
      echo '</tbody></table>';	
            
	}else{
	   
	  echo '<table class="tablesorter" border="0" cellpadding="15" cellspacing="0" width="900">
            <tbody id="sortable">
            <tr>
            <td valign="middle" style="height:30px; padding-left: 20px;">
              <a href="http://oqeysites.com/category/wp-photo-gallery-skins/" target="_blank">'.__('View more skins...', 'oqey-gallery').'</a>
            </tr>
            </td>
            </tbody>
            </table>';	
	
    }
        
    die();
}
/*END Get more skins from oqesites.com*/


/*install new skin*/
add_action( 'wp_ajax_oQeyInstallNewSkins', 'oQeyInstallNewSkins' );
function oQeyInstallNewSkins(){
    global $wpdb, $current_user;
    
    if ( !current_user_can('oQeySkins') ) die(__('You do not have sufficient permissions to do this.'));
	if ( !wp_verify_nonce($_POST['nonce'], 'oqey-install-skin') ) die("Security check failed.");
	
    //include(OQEY_ABSPATH."wp-admin/includes/file.php");

    $url = "http://oqeysites.com/skinsxml/archives/".$_POST['install_new_skin'].".zip";
    $arhiva = download_url($url);
    $arhiva = str_replace("\\", "/", $arhiva);
    
    $skins_dir = OQEY_ABSPATH.'wp-content/oqey_gallery/skins/'.oqey_getBlogFolder($wpdb->blogid); 

      if( class_exists('ZipArchive') ){
        
			$zip = new ZipArchive;
            $zip->open($arhiva);
            $zip->extractTo($skins_dir);
            $zip->close();
            
		}else{
		  
			require_once(OQEY_ABSPATH . 'wp-admin/includes/class-pclzip.php');			
			$archive = new PclZip($arhiva);
            $list = $archive->extract($skins_dir);
            if ($list == 0) {
            die("ERROR : '".$archive->errorInfo(true)."'"); 
            }
            
		}

    if(unlink($arhiva)){ echo "ok"; }
    
    die();	
}
/*END install new skin*/

/*preview skin*/
add_action( 'wp_ajax_oQeyPreviewNewSkin', 'oQeyPreviewNewSkin' );
function oQeyPreviewNewSkin(){
   global $wpdb, $current_user;
   if ( !current_user_can('oQeySkins') ) die(__('You do not have sufficient permissions to do this.'));
   
   echo '
   <object height="598" width="896">
    <param value="#ffffff" name="bgcolor">
    <param value="true" name="allowFullScreen">
    <param name="movie" value="http://oqeysites.com/skinsxml/'.$_POST['get_the_preview'].'/'.$_POST['get_the_preview'].'.swf">
    <param name="FlashVars" value="flashId=fid&amp;FKey='.$_POST['comkey'].'&amp;GalleryPath=http://oqeysites.com/skinsxml/&amp;GalleryID=1&amp;FirstRun=0">
    <embed src="http://oqeysites.com/skinsxml/'.$_POST['get_the_preview'].'/'.$_POST['get_the_preview'].'.swf" bgcolor="#ffffff" FlashVars="flashId=fid&amp;FKey='.$_POST['comkey'].'&amp;GalleryPath=http://oqeysites.com/skinsxml/&amp;GalleryID=1&amp;FirstRun=0" width="896" height="598" wmode="transparent" allowFullScreen="true"></embed>
   </object>';
   die(); 
}
/*END preview skin*/

/*skin options*/
add_action( 'wp_ajax_oQeySkinOptions', 'oQeySkinOptions' );
function oQeySkinOptions(){
   global $wpdb, $current_user;
   if ( !current_user_can('oQeySkins') ) die(__('You do not have sufficient permissions to do this.'));
   
   if ( is_ssl() ){
	$cookies = $_COOKIE[SECURE_AUTH_COOKIE];
    }else{
	$cookies = $_COOKIE[AUTH_COOKIE];
   }

   $datele = '7--'.$cookies.'--'.$_COOKIE[LOGGED_IN_COOKIE].'--'.wp_create_nonce('oqey-options-save');
   if ( is_user_logged_in() ){ $loggedin="true"; }else{ $loggedin="false"; };
   
   if($_POST['flash']=="no"){
    
     echo '<div style="text-align:center;">Adobe Flash Player is required. Please click <a href="http://get.adobe.com/flashplayer/" target="_blank">here</a> to download it.</div>'; 
    
   }else{
    
     echo '
     <object height="660" width="990">
     <param value="#CCCCCC" name="bgcolor">
     <param value="true" name="allowFullScreen">
     <param name="movie" value="'.oQeyPluginRepoUrl().'/skins/'.oqey_getBlogFolder($wpdb->blogid).$_POST['folder'].'/settings.swf">
     <param name="FlashVars" value="spntype='.base64_encode($datele).'&loggedin='.$loggedin.'">
     <embed src="'.oQeyPluginRepoUrl().'/skins/'.oqey_getBlogFolder($wpdb->blogid).$_POST['folder'].'/settings.swf" bgcolor="#CCCCCC" FlashVars="spntype='.base64_encode($datele).'&loggedin='.$loggedin.'" width="990" height="660" wmode="transparent" allowFullScreen="true"></embed>
     </object>';
   
   }
   
   die(); 
}
/*END skin options*/

/*get all skins*/
add_action( 'wp_ajax_oQeyGetAllInstalledSkins', 'oQeyGetAllInstalledSkins' );
function oQeyGetAllInstalledSkins(){
   global $wpdb, $current_user;
   $oqey_skins = $wpdb->prefix . "oqey_skins";
   if ( !current_user_can('oQeySkins') ) die(__('You do not have sufficient permissions to do this.'));

   $get_list = $wpdb->get_results("SELECT * FROM $oqey_skins WHERE status !=2 AND status !=1 ORDER BY id DESC");
   
   echo '<table width="900" border="0" cellspacing="0" cellpadding="15" id="musictable" class="tablesorter">
         <thead>
         <tr>
          <th colspan="3" align="left" valign="middle" style="padding:15px;">'.__('Available skins', 'oqey-gallery').'</th>
         </tr>
         </thead>
         <tbody id="sortable">';

if(!empty($get_list)){	
    
	foreach ($get_list as $r){  
	
	if($r->commercial=="yes"){ $comm = " - Commercial skin"; }else{ $comm = " - Free skin"; }
    
    $skinpath = oQeyPluginRepoPath().'/skins/'.oqey_getBlogFolder($wpdb->blogid).$r->folder.'/';
	$sfpath = $skinpath.'settings.swf';
    
    if(is_file($sfpath)){ $skoptions = '<a href="#set_skin_options" class="set_skin_options" id="skopt'.$r->id.'" rel="'.$r->folder.'">'.__('Skin Options', 'oqey-gallery').'</a> | '; }else{ $skoptions = ""; }
    
       echo '<tr id="skink_tr_'.$r->id.'">
             <td width="170" height="120" align="center" valign="middle">
			 <img src="'.oQeyPluginRepoUrl().'/skins/'.oqey_getBlogFolder($wpdb->blogid).$r->folder.'/'.$r->folder.'.jpg" alt="skin" width="150" height="100" style="border:#999999 solid thin; margin-left:15px;" >
			 </td>
             <td width="460" align="left" valign="top" style="margin-left:10px; padding:10px;">
			 <h4>'.urldecode($r->name).$comm.'</h4>
             '.urldecode($r->description).'<br/>
             '.__('Skin files location', 'oqey-gallery').': <code>/skins/'.oqey_getBlogFolder($wpdb->blogid).$r->folder.'</code>.
			 <p>'.$skoptions.'<a href="#set_as_default" class="set_as_default" id="'.$r->id.'">'.__('Set as default', 'oqey-gallery').'</a> | <a href="#delete_this_skin" class="delete_this_skin" id="'.$r->id.'">'.__('Move to trash', 'oqey-gallery').'</a></p>
             </td>
             <td width="270" align="left" valign="top" style="margin-left:10px; padding:5px;">';
		
		if($r->commercial=="yes"){
		
        if($r->firstrun==0){ 
		}else{

		echo '<div>
              <p>'.__('Commercial key', 'oqey-gallery').':<br/>
              <input name="comkey" class="comkey" type="text" value="'.$r->comkey.'" id="key'.$r->id.'" style="background-color:#CCC;width:190px;"/>
			  <input type="button" name="savekey" class="savekey" id="'.$r->id.'" value="'.__('Save', 'oqey-gallery').'" style="background-color:#CCC;width:63px;">
              </p>
              <form action="http://oqeysites.com/paypal/oqeypaypal.php" name="buyskin" method="post">
              <input type="hidden" name="oqey" value="qwe1qw5e4cw8c7fv8h7" />
              <input type="hidden" name="website" value="'.urlencode(get_option('siteurl')).'" />
              <input type="hidden" name="s" value="'.$r->skinid.'" />
              <input type="hidden" name="skinfolder" value="'.$r->folder.'" />';
        echo '<input type="text" name="d" value="discount code" class="discount_code" style="background-color:#CCC; width:259px;"/>'; 
        echo '<a href="#buy_this_skin" class="buy_this_skin"><img src="'.oQeyPluginUrl().'/images/btn_buynowcc_lg.gif" style="margin-top:8px;" /></a>
              </form>
              </div>';
		}
		}
        echo '</td></tr>';
	    }
        
        }else{ 
            
           echo '<tr id="skink_tr_"><td height="30" align="center" valign="middle">'.__('No available skins.', 'oqey-gallery').'</td></tr>';
        
        }
        
        echo '</tbody>
              </table>';
        die();
}
/*END get all skins*/

/*Save skin key*/
add_action( 'wp_ajax_oQeySaveSkinKey', 'oQeySaveSkinKey' );
function oQeySaveSkinKey(){
   global $wpdb, $current_user;
   $oqey_skins = $wpdb->prefix . "oqey_skins";
   
   if ( !current_user_can('oQeySkins') ) die(__('You do not have sufficient permissions to do this.'));
  
   $upd = $wpdb->query( $wpdb->prepare("UPDATE $oqey_skins SET comkey = %s WHERE id = %d ", esc_sql(trim($_POST['comkey'])), esc_sql($_POST['savekey']) )); 
   
   if($upd){
      _e('Skin key was saved.', 'oqey-gallery');
   }else{ 
    _e('Key already was updated.', 'oqey-gallery'); 
   }
   die();
}
/*END save key*/

/*single skin move to trash*/
add_action( 'wp_ajax_oQeySkinToTrash', 'oQeySkinToTrash' );
function oQeySkinToTrash(){
   global $wpdb, $current_user;
   $oqey_skins = $wpdb->prefix . "oqey_skins";  
   if ( !current_user_can('oQeySkins') ) die(__('You do not have sufficient permissions to do this.'));
    
   $s = $wpdb->query( $wpdb->prepare("UPDATE $oqey_skins SET status = %d WHERE id = %d ", 2, esc_sql($_POST['movetotrashskin']) ));

   _e('Skin was moved to trash.', 'oqey-gallery');
   die();
}
/*End skin to trash*/

/*single skin move to trash*/
add_action( 'wp_ajax_oQeySkinFromTrash', 'oQeySkinFromTrash' );
function oQeySkinFromTrash(){
   global $wpdb, $current_user;
   $oqey_skins = $wpdb->prefix . "oqey_skins";
   if ( !current_user_can('oQeySkins') ) die(__('You do not have sufficient permissions to do this.'));
    
   $resp = array();
   $s = $wpdb->query( $wpdb->prepare("UPDATE $oqey_skins SET status = %d WHERE id = %d ", 0, esc_sql($_POST['undoskin']) ));

   if($s){
   if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT id FROM $oqey_skins WHERE status = %d ", "2" ))){ $e="no"; }
   $resp["statusul"] = $e;
   $resp["raspuns"] = __('Skin was restored.', 'oqey-gallery');
   echo json_encode($resp);
   }
   die();
}
/*End skin to trash*/

/*END skins ajax*/

/*MUSIC*/

/*Song move to trash*/
add_action( 'wp_ajax_oQeySongToTrash', 'oQeySongToTrash' );
function oQeySongToTrash(){
   global $wpdb, $current_user;
   $oqey_music = $wpdb->prefix . "oqey_music";
   if ( !current_user_can('oQeyMusic') ) die(__('You do not have sufficient permissions to do this.'));
   
   $s = $wpdb->query( $wpdb->prepare( "UPDATE $oqey_music SET status = %d WHERE id = %d ", 2, esc_sql($_POST['songtotrash']) ));
   echo trim($_POST['songtotrash']);
   die();
}
/*End song to trash*/

/*Song move from trash*/
add_action( 'wp_ajax_oQeySongFromTrash', 'oQeySongFromTrash' );
function oQeySongFromTrash(){
   global $wpdb, $current_user;
   $oqey_music = $wpdb->prefix . "oqey_music";
   if ( !current_user_can('oQeyMusic') ) die(__('You do not have sufficient permissions to do this.'));
   
   $s = $wpdb->query( $wpdb->prepare("UPDATE $oqey_music SET status = %d WHERE id = %d ", 0, esc_sql($_POST['restoresong']) ));
   if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT id FROM $oqey_music WHERE status = %d ", 2 ))){ $e="no"; }
   echo $e;
   die();
}
/*End song from trash*/


/*Rename songs titles*/
add_action( 'wp_ajax_oQeySongRename', 'oQeySongRename' );
function oQeySongRename(){
   global $wpdb, $current_user;
   $oqey_music = $wpdb->prefix . "oqey_music";
   
   if ( !current_user_can('oQeyMusic') ) die(__('You do not have sufficient permissions to do this.'));

   $id = str_replace("select_", "", $_POST['music_edit_id'] );
   $newtitle = esc_sql(stripslashes_deep(trim(urldecode($_POST["music_edit_title"]))));

   if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_music WHERE title = %s AND id != %d ",$newtitle, esc_sql($id) ))){ 
    
      $title = $newtitle;
   
   }else{
   
      $sql=$wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_music WHERE title = %s AND id != %d ",$newtitle, esc_sql($id) ));
      $title = trim($sql->title.time());	
   
   }
  
   $m = $wpdb->query( $wpdb->prepare("UPDATE $oqey_music SET title = %s WHERE id = %d ", $title, esc_sql($id) ));
   echo stripslashes_deep($title);
   die();
}
/*END music rename*/
/*END MUSIC*/

/*START TRASH*/

/*DELETE gallery images permanently*/
add_action( 'wp_ajax_oQeyImagePermDelete', 'oQeyImagePermDelete' );
function oQeyImagePermDelete(){
   global $wpdb, $current_user;
   $oqey_images = $wpdb->prefix . "oqey_images";
   if ( !current_user_can('oQeyTrash') ) die(__('You do not have sufficient permissions to do this.'));
    
   $e ="";
   $galid = str_replace("trashdiv", "", $_POST['galid']);
   $folder = oqey_get_gallery_folder($galid);

   $all = explode(",", urldecode($_POST['imgalldelete']));

   foreach ($all as $title){
    
   $d = $wpdb->query( $wpdb->prepare( "DELETE FROM $oqey_images WHERE gal_id = %d AND title = %s ", esc_sql($galid), esc_sql($title) ) );

   if(is_file(OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/galimg/'.trim($title)) ){    
      	$i = unlink(OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/galimg/'.trim($title));
	    $t = unlink(OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/galthmb/'.trim($title));
        
        if(is_file(OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/iphone/'.trim($title))){         
	        $t = unlink(OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$folder.'/iphone/'.trim($title));        
        }
    }
   }

   if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT id FROM $oqey_images WHERE gal_id = %d AND status = %d AND img_type != 'video' ", esc_sql($galid), "2" ))){ $e="no"; }
   echo $e;
   die();
}
/*End delete gallery images*/

/*DELETE song*/
add_action( 'wp_ajax_oQeySongPermDelete', 'oQeySongPermDelete' );
function oQeySongPermDelete(){
   global $wpdb, $current_user;
   $oqey_music = $wpdb->prefix . "oqey_music";
   $oqey_music_rel = $wpdb->prefix . "oqey_music_rel";
   
   if ( !current_user_can('oQeyTrash') ) die(__('You do not have sufficient permissions to do this.'));
   
   $song = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_music WHERE id = %d ", esc_sql($_POST['deletesong']) ) );
   
   $d = $wpdb->query( $wpdb->prepare( "DELETE FROM $oqey_music WHERE id = %d ", esc_sql($_POST['deletesong']) ));
   $r = $wpdb->query( $wpdb->prepare( "DELETE FROM $oqey_music_rel WHERE music_id = %d ", esc_sql($_POST['deletesong']) ) );

   if($d){
     
     if($song->type=="oqey"){
    
        if(is_file($song->path)){  
        
	       $i = unlink($song->path);
        
        }
   
   }
   
   if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT id FROM $oqey_music WHERE status = %d ", 2 ))){ $e="no"; }
   echo $e;
   }
   
   die();
}
/*End delete song*/

/*DELETE skin*/
add_action( 'wp_ajax_oQeySkinPermDelete', 'oQeySkinPermDelete' );
function oQeySkinPermDelete(){
   global $wpdb, $current_user;
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_skins = $wpdb->prefix . "oqey_skins";
   
   if ( !current_user_can('oQeyTrash') ) die(__('You do not have sufficient permissions to do this.'));
   
   $skinid = oqey_get_skinid_by_id( esc_sql($_POST['deleteskinper']) );  
   
   $d = $wpdb->query( $wpdb->prepare( "DELETE FROM $oqey_skins WHERE id = %d ", esc_sql($_POST['deleteskinper']) ) );
   $u = $wpdb->query( $wpdb->prepare( "UPDATE $oqey_galls SET skin_id = %d WHERE skin_id = %d ", 0, esc_sql($_POST['deleteskinper']) ) );

   if($d){
      
       
      $skopt = "oqey_request_key_".$skinid;
      delete_option($skopt);
    
    
      if(is_dir(OQEY_ABSPATH."wp-content/oqey_gallery/skins/".oqey_getBlogFolder($wpdb->blogid).esc_sql($_POST['name'])) ){   
         $dir = OQEY_ABSPATH."wp-content/oqey_gallery/skins/".oqey_getBlogFolder($wpdb->blogid).esc_sql($_POST['name']);
         $do = oqey_rm($dir);
      if($do){ 
         if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT id FROM $oqey_skins WHERE status = %d ", "2" ))){ $e="no"; }
         echo $e;
      }
     } 
    }
    die();
}
/*End delete song*/

/*DELETE gallery permanently*/
add_action( 'wp_ajax_oQeyGalleryPermDelete', 'oQeyGalleryPermDelete' );
function oQeyGalleryPermDelete(){
   global $wpdb, $current_user;
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_music_rel = $wpdb->prefix . "oqey_music_rel";
   $oqey_images = $wpdb->prefix . "oqey_images";
   
   if ( !current_user_can('oQeyTrash') ) die(__('You do not have sufficient permissions to do this.'));
    
   $e="";
   $d = $wpdb->query( $wpdb->prepare("DELETE FROM $oqey_galls WHERE id = %d ", esc_sql($_POST['deletegall']) ) );
   $r = $wpdb->query( $wpdb->prepare("DELETE FROM $oqey_music_rel WHERE gallery_id = %d ", esc_sql($_POST['deletegall']) ) );
   $d = $wpdb->query( $wpdb->prepare("DELETE FROM $oqey_images WHERE gal_id = %d ", esc_sql($_POST['deletegall']) ) );   

   if($d){
      if(is_dir(OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).esc_sql($_POST['name'])) ){   
         $dir = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).esc_sql($_POST['name']);
         $do = oqey_rm($dir);
      if($do){ 
      if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT id FROM $oqey_galls WHERE status = %d ", "2" ))){ $e="no"; }
          echo $e;
      }
     }  
    }
   die();
}
/*End delete gallery*/

/*END TRASH*/

/*VIDEO*/

add_action( 'wp_ajax_oQeyTrashVideoFile', 'oQeyTrashVideoFile' );
function oQeyTrashVideoFile(){
   global $wpdb, $current_user;
   $oqey_video = $wpdb->prefix . "oqey_video";
   $oqey_images = $wpdb->prefix . "oqey_images";

   $resp = array();
   
   $img = $wpdb->query( $wpdb->prepare( "UPDATE $oqey_video SET status = %d WHERE id = %d ", 2, esc_sql($_POST['id']) ) );
   $vdo = $wpdb->query( $wpdb->prepare( "UPDATE $oqey_images SET status = %d WHERE video_id = %d ", 2, esc_sql($_POST['id']) ) );
   
   if(get_option("oqey_default_video")==$_POST['id']){ 
    
    $sql = $wpdb->get_row("SELECT id FROM $oqey_video WHERE status != '2' ORDER BY id ASC LIMIT 0,1");
    
    if(!empty($sql)){
        
        update_option("oqey_default_video", $sql->id ); 
        $defvid = $sql->id;
        
    }else{
        
        update_option("oqey_default_video", "0" ); 
        $defvid = 0;
        
    }
   }
   
   $resp["defvid"] = $defvid;
   $resp["info"] = __('Video was moved to trash.', 'oqey-gallery');
   echo json_encode($resp);

   die();
}

//get file back from trash
add_action( 'wp_ajax_oQeyFromTrashVideoFile', 'oQeyFromTrashVideoFile' );
function oQeyFromTrashVideoFile(){
   global $wpdb, $current_user;
   $oqey_video = $wpdb->prefix . "oqey_video";
   $oqey_images = $wpdb->prefix . "oqey_images";
   
   if(!$sql=$wpdb->get_results($wpdb->prepare("SELECT id FROM $oqey_video WHERE status != %d ", 2 ))){ 
    
       update_option("oqey_default_video", $_POST['id'] );  
   
   }
    
   $resp = array();
   $v = $wpdb->query( $wpdb->prepare("UPDATE $oqey_video SET status = %d WHERE id = %d ", 0, esc_sql($_POST['id']) ));
   $vdo = $wpdb->query( $wpdb->prepare( "UPDATE $oqey_images SET status = %d WHERE video_id = %d ", 0, esc_sql($_POST['id']) ) );

   if($v){
      
      if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT id FROM $oqey_video WHERE status = %d ", "2" ))){ $e="no"; }
      $resp["statusul"] = $e;
      $resp["mesaj"] = __('Video was restored.', 'oqey-gallery');
      echo json_encode($resp);	
      
   } 

   die();
}


/*SCAN server for image*/
add_action( 'wp_ajax_oQeyScanForImagesForVideo', 'oQeyScanForImagesForVideo' );
function oQeyScanForImagesForVideo(){
   global $wpdb, $current_user;
   $oqey_video = $wpdb->prefix . "oqey_video";
   
   $r = $wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_video WHERE id = %d ", esc_sql($_POST['id']) ));
   
   $img_ext = array('jpg', 'png', 'gif');
   $img_root = rtrim(OQEY_ABSPATH, '/');
   $imgs = oqey_get_all_files($img_root, $img_ext );
   $url = get_option("siteurl")."/";
   $f_link = $url.$r->video_image;
   $bg = "";
   
   foreach($imgs as $img){
    
       $f_link_u = str_replace( OQEY_ABSPATH, $url, $img);
       if($f_link == $f_link_u){ $bg = "background:#AECFF0;"; }  
       echo "<div style='width:190px; height:110px; padding:4px; margin:1px; display: table-cell; text-align: center; vertical-align: bottom; border: 1px #999 solid; float:left; ".$bg."'>
             <img src='".$f_link_u."' style='max-width:150px; max-height:100px; margin-top: 5px; margin-right: auto; margin-bottom: auto; margin-left: auto;' class='videoimage' title='".$f_link_u."' />
             </div>";
       $bg = "";
       
   }
   
   die();
}
/*end scan*/

/*Save Video IMG*/

add_action( 'wp_ajax_oQeySaveVideoImg', 'oQeySaveVideoImg' );
function oQeySaveVideoImg(){
   global $wpdb, $current_user;
   $oqey_video = $wpdb->prefix . "oqey_video";
   $url = get_option("siteurl")."/";
   $url = str_replace($url, "", $_POST['imgurl']);
   
   $s = $wpdb->query( $wpdb->prepare("UPDATE $oqey_video SET video_image = %s WHERE id = %d ", $url, esc_sql($_POST['id']) ));
   
   echo trim($_POST['id']);
   die();
}

/*end video img*/

/*Edit video title*/
add_action( 'wp_ajax_oQeyEditVideoTitle', 'oQeyEditVideoTitle' );
function oQeyEditVideoTitle(){
    global $wpdb, $current_user;
    $oqey_video = $wpdb->prefix . "oqey_video";

    $id = esc_sql( str_replace("video_title_", "", $_POST['id'] ) );
    $newtitle = stripslashes_deep(trim(urldecode($_POST["value"])));

    if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_video WHERE title = %s AND id != %d ",$newtitle, $id))){ 
        
        $title = $newtitle;
      
      }else{
      
        $sql=$wpdb->get_row($wpdb->prepare("SELECT * FROM $oqey_video WHERE title = %s AND id != %d ",$newtitle, $id));
        $title = trim($sql->title.time());	
    
    }
 
    $gal = $wpdb->query( $wpdb->prepare("UPDATE $oqey_video SET title = %s WHERE id = %d ", $title, $id) );
    echo stripslashes_deep($title);

    die();
}
/*END Edit video title*/

/*Edit description title*/
add_action( 'wp_ajax_oQeyEditVideoDescription', 'oQeyEditVideoDescription' );
function oQeyEditVideoDescription(){
    global $wpdb, $current_user;
    $oqey_video = $wpdb->prefix . "oqey_video";

    $id = esc_sql( str_replace("video_desc_", "", $_POST['id'] ) );
    $desc = stripslashes_deep(trim(urldecode($_POST["value"])));

    $wpdb->query( $wpdb->prepare("UPDATE $oqey_video SET description = %s WHERE id = %d ", $desc, $id) );
    echo stripslashes_deep($desc);

    die();
}
/*END Edit description title*/

/*undo video*/
add_action( 'wp_ajax_oQeyFromTrashVideo', 'oQeyFromTrashVideo' );
function oQeyFromTrashVideo(){
   global $wpdb, $current_user;
   $oqey_video = $wpdb->prefix . "oqey_video";
   $oqey_images = $wpdb->prefix . "oqey_images";
   
    if(!$sql=$wpdb->get_results($wpdb->prepare("SELECT id FROM $oqey_video WHERE status != %d ", 2 ))){ 
    
      update_option("oqey_default_video", $_POST['id'] );  
   
    }
    
   $resp = array();
   $v = $wpdb->query( $wpdb->prepare("UPDATE $oqey_video SET status = %d WHERE id = %d ", 0, esc_sql($_POST['id']) ));
   $vdo = $wpdb->query( $wpdb->prepare( "UPDATE $oqey_images SET status = %d WHERE video_id = %d ", 0, esc_sql($_POST['id']) ) );

   if($v){
    
       if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT id FROM $oqey_video WHERE status = %d ", "2" ))){ $e="no"; }
   
       $resp["statusul"] = $e;
       $resp["mesaj"] = __('Video was restored.', 'oqey-gallery');
       echo json_encode($resp);	
   } 

   die();
}

/*DELETE video permanently*/
add_action( 'wp_ajax_oQeyVideoPermDelete', 'oQeyVideoPermDelete' );
function oQeyVideoPermDelete(){
   global $wpdb, $current_user;
   $oqey_video = $wpdb->prefix . "oqey_video";
   $oqey_images = $wpdb->prefix . "oqey_images";
    
   $e="";

   $d = $wpdb->query( $wpdb->prepare("DELETE FROM $oqey_video WHERE id = %d ", esc_sql($_POST['id']) ) );
        $wpdb->query( $wpdb->prepare("DELETE FROM $oqey_images WHERE video_id = %d ", esc_sql($_POST['id']) ) );

   if($d){

      if(!$sql=$wpdb->get_row($wpdb->prepare("SELECT id FROM $oqey_video WHERE status = %d ", "2" ))){ $e="no"; }
    
    echo $e;
   }
   die();
}
/*End delete video*/

/*Video delete from gallery*/
add_action( 'wp_ajax_oQeyVideoFromGalleryDelete', 'oQeyVideoFromGalleryDelete' );
function oQeyVideoFromGalleryDelete(){
   global $wpdb, $current_user;
   $oqey_images = $wpdb->prefix . "oqey_images";
   if ( !current_user_can('oQeyGalleries') ) die(__('You do not have sufficient permissions to do this.'));
   
   $wpdb->query( $wpdb->prepare("DELETE FROM $oqey_images WHERE id = %d ", esc_sql($_POST['id']) ) );
   
   _e('Image was moved to trash.', 'oqey-gallery');
   
   die();
}
/*END Video delete from gallery*/

/*Get ALL VIDEOS FOR GALLERY INSERT*/
add_action( 'wp_ajax_oQeyGetAllVideoFiles', 'oQeyGetAllVideoFiles' );
function oQeyGetAllVideoFiles(){
   global $wpdb, $current_user;
   $oqey_video = $wpdb->prefix . "oqey_video";
   $oqey_images = $wpdb->prefix . "oqey_images";
   
   $imgsvideo=$wpdb->get_results($wpdb->prepare("SELECT video_id FROM $oqey_images WHERE gal_id = %d AND img_type = %s  ", esc_sql($_POST['galid']), "video" ));
   
   $vlist = array();

   foreach($imgsvideo as $v){
    
   $vlist[] = $v->video_id;
    
   }   
   
   $videos = $wpdb->get_results("SELECT * FROM $oqey_video WHERE status !=2 ");

    $r .='<form name="videoslist" id="videoslist">';
    $r .='<table width="100%" border="0" cellspacing="0" cellpadding="15" class="tablesorter">
          <tbody>';
    
    $j=0;
    
  if(!empty($videos)){ 
    
    foreach ($videos as $video){ 
        
        if(!in_array($video->id, $vlist)){
            
            $imglink = oQeyPluginUrl().'/images/no-2-photo.jpg';
            
            if( !empty($video->video_image) ){ 
                
                $imgroot = OQEY_ABSPATH.trim($video->video_image);    
                $imglink = get_option('siteurl').'/'.trim($video->video_image); 
                
                if(!is_file($imgroot)){
                    
                    $imglink = oQeyPluginUrl().'/images/no-2-photo.jpg';
                    
                }
            
            }
    
      $r .='<tr id="video_tr_'.$video->id.'">
             <td width="170" height="120" align="center" valign="middle">
			 <img src="'.$imglink.'" alt="video" style="border:#999999 solid thin; margin-left:15px; height:100px; max-width:150px;"/>
			 </td>
             <td align="left" valign="top" style="margin-left:10px; padding:5px;">
			 <h4>'.urldecode($video->title).'</h4>
             <p>'.urldecode($video->description).'<br/></p>
			 </td>
             <td width="50" height="120" align="center" valign="middle">
                
                <input type="checkbox" value="'.$video->id.'" name="check_video" id="check_video_'.$video->id.'"> 
                
             </td>
           </tr>';
           
           $j++;
           
    }    
    
   }
   
     if($j==0){
        
       $r .='<tr id="video_tr">
               <td width="100%" height="120" align="center" valign="middle">
			     '.__('All available video files have been added already to this gallery.', 'oqey-gallery').'
               </td>
             </tr>';
        
       }
  
     }else{
         
     $r .='<tr id="video_tr">
             <td width="100%" height="120" align="center" valign="middle">
			 '.__('There is no video yet. Please upload some video content first.', 'oqey-gallery').'
             </td>
           </tr>';
    
    } 

    $r .='</tbody>
          </table>';
          
    $r .='</form>';
    
    
    echo $r;
       
  die();
}

/*END insert*/
add_action( 'wp_ajax_oQeyAddVideoToGallery', 'oQeyAddVideoToGallery' );
function oQeyAddVideoToGallery(){
   global $wpdb, $current_user;
   $oqey_video = $wpdb->prefix . "oqey_video";
   $oqey_images = $wpdb->prefix . "oqey_images";
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   
   if(!empty($_POST['data'])){
        
   $data = str_replace("check_video=", "", $_POST['data']);
   $data = explode("&", $data);
   
      foreach($data as $vid){ 
    
         $video = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $oqey_video WHERE id = %d ", esc_sql($vid) ) );
   
                    $wpdb->query( $wpdb->prepare( "INSERT INTO $oqey_images (title, gal_id, alt, comments, status, img_path, img_type, video_id) 
                                                                     VALUES (%s, %d, %s, %s, %d, %s, %s, %d )",
                                                                     $video->video_link,
                                                                     esc_sql($_POST['galid']),
                                                                     $video->title,
                                                                     $video->description,
                                                                     '0',
                                                                     $video->video_image,
                                                                     'video',
                                                                     esc_sql($vid)                                                           
                                                                     ) );

      }
      
      echo "1";
      
   }else{
      
      echo "0";
    
   }
   
   die();

}
?>