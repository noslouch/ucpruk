<?php
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'gallcore.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!');

global $oqeycounter;
$d=0;
$oqeycounter = 1;
 
 if(is_admin()){
    
    require ("oqey-ajax.php");
 
 }
 
add_action( 'widgets_init', 'oqey_load_widgets' );

function oqey_load_widgets() {
	register_widget( 'oQey_Gallery_Widget' );
}

class oQey_Gallery_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function oQey_Gallery_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'oqeygallery', 'description' => __('Show oQey Gallery Slideshow', 'oqeygallery') );

		/* Create the widget. */
		$this->WP_Widget( 'oqey-gallery-widget', __('Oqey Widget', 'oqey-gallery'), $widget_ops ); 
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );
        
        global $wpdb,$oqeycounter;
        
        $oqey_galls = $wpdb->prefix . "oqey_gallery";
        $oqey_skins = $wpdb->prefix . "oqey_skins";
        $oqey_images = $wpdb->prefix . "oqey_images";
        
        $gal = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_galls WHERE id = %d ", esc_sql($instance['oqeygalleryid']) ));
        
        $folder = $gal->folder;
    
        if($gal->skin_id!="0"){
            
           $skin = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE id = '".$gal->skin_id."'");
        
        }else{
        
           $skin = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE status = '1'");
        
        }
        
        if($gal->splash_only==1){ $s = "AND id!=".$gal->splash_img; }else{ $s=""; }        
        $all = $wpdb->get_results("SELECT * FROM $oqey_images WHERE gal_id = '".esc_sql($instance['oqeygalleryid'])."' AND status!=2 ".$s." ORDER BY img_order ASC");
        $oqey_bgcolor = get_option('oqey_bgcolor');
        $plugin_url = oQeyPluginUrl();
        $plugin_repo_url = oQeyPluginRepoUrl();
        $autoplay = "false";
        if($instance['autostart']==1){ $autoplay = "true"; }else{ $autoplay="false"; }
        
        $title = apply_filters('widget_title', $instance['title'] );
                
        echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;
   
   /*images array*/     
    $oqey_height = $instance['height'];
    $oqey_width = $instance['width'];
       
   foreach($all as $i){ 
	 if($i->img_type!="video" ){      
	   
      if($i->img_type=="nextgen"){
        
        $ipath = OQEY_ABSPATH.'/'.trim($i->img_path).'/';
        $img_type = "nextgen";
        $img_f_path = urlencode(trim($i->img_path));
       
      }else{
      
        $ipath = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$gal->folder.'/galimg/';
        $img_type = "oqey";
        $img_f_path = "";
      
      }

	$img_path = $ipath.trim($i->title);   
    $size = @getimagesize( $img_path );
	if ( $size ){
	   
	   list($iwidth, $iheight, $itype, $iattr)= $size;
    
    }else{
        
	   $iwidth = 210;
       $iheight = 140;
	
    }
    
      $img_holder_h = $oqey_width/1.5;
      $d = wp_constrain_dimensions($iwidth, $iheight, $oqey_width, $img_holder_h);

	  $img_full_root = get_option('siteurl').'/wp-content/plugins/oqey-gallery/oqeyimgresize.php?width='.$d[0].'&amp;new_height='.$d[1].'&amp;folder='.$gal->folder.'&amp;img='.trim($i->title).'&amp;img_type='.$img_type.'&amp;img_f_path='.$img_f_path;
	  $imgs .= '[div class="oqeyimgdiv" style="background: url('.$img_full_root.') center center no-repeat; width:'.$oqey_width.'px; height:'.$img_holder_h.'px;"][/div]';
	
    }
    
    }	
            
         echo  "\n".'<div id="oqey_image_div'.$oqeycounter.'" style="position:relative; width:'.$instance['width'].'px; height:'.$instance['height'].'; display:none; margin: 0 auto;">';
         echo  "\n".'<div id="image'.$oqeycounter.'" style="height:auto; display:none;" class="oqey_images"></div>';
         echo  "\n".'</div>';
         
         echo "\n".'<script type="text/javascript">';
         echo "\n".'jQuery(function($){';
         echo "\n".'var pv = swfobject.getFlashPlayerVersion();';
         echo "\n".'oqey_e_w(pv, \''.$oqeycounter.'\', \''.$imgs.'\', \''.(get_option('oqey_pause_between_tran')*1000).'\');';
         echo "\n".'});';
         echo "\n".'</script>';
          //oqey_e(pv, {$oqeycounter}, '{$imgs}', '{$optouch}', '{$incolums}');
         
         echo '<div id="flash_gal_'.$oqeycounter.'" style="width:'.$instance['width'].'px; height:'.$instance['height'].'px; margin: 0 auto;">
               <script type="text/javascript">
                  var flashvars'.$oqeycounter.' = {
                          autoplay:"'.$autoplay.'",
                           flashId:"'.$oqeycounter.'",
		                      FKey:"'.$skin->comkey.'",
	                   GalleryPath:"'.$plugin_url.'",	
                         GalleryID:"'.$instance['oqeygalleryid'].'-0",
					      FirstRun:"'.$skin->firstrun.'"
					 };
              	var params'.$oqeycounter.' = {bgcolor:"'.get_option('oqey_bgcolor').'", allowFullScreen:"true", wMode:"transparent"};
	            var attributes'.$oqeycounter.' = {id: "oqeygallery'.$oqeycounter.'"};
               	swfobject.embedSWF("'.$plugin_repo_url.'/skins/'.$skin->folder.'/'.$skin->folder.'.swf", "flash_gal_'.$oqeycounter.'", "'.$instance['width'].'", "'.$instance['height'].'", "8.0.0", "", flashvars'.$oqeycounter.', params'.$oqeycounter.', attributes'.$oqeycounter.');
               </script>                
               </div>';
         $oqeycounter ++;         

          echo '<div class="textwidget">'.$instance['oqeywidgettext'].'</div>';
         
          if(isset($after_widget)){
            
				echo $after_widget;
		  
          }
}
	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['oqeygalleryid'] = (int) $new_instance['oqeygalleryid'];
		$instance['height'] = (int) $new_instance['height'];
		$instance['width'] = (int) $new_instance['width'];
        $instance['autostart'] = (bool) $new_instance['autostart'];
        $instance['oqeywidgettext'] = $new_instance['oqeywidgettext'];        
		return $instance;        
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {
        global $wpdb;
        $oqey_galls = $wpdb->prefix . "oqey_gallery";
		/* Set up some default widget settings. */
        $instance = wp_parse_args( (array) $instance, array( 'title' => 'Oqey Gallery', 'oqeygalleryid' => '0', 'height' => '140', 'width' => '210') );
        $title  = esc_attr( $instance['title'] );
		$height = esc_attr( $instance['height'] );
		$width  = esc_attr( $instance['width'] );
        $oqeywidgettext  =  esc_textarea($instance['oqeywidgettext']);                
		$gals = $wpdb->get_results("SELECT * FROM $oqey_galls WHERE status!=2 ORDER BY id ASC ");
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('galleryid'); ?>"><?php _e('Select Gallery:', 'oqey-gallery'); ?></label>
				<select size="1" name="<?php echo $this->get_field_name('oqeygalleryid'); ?>" id="<?php echo $this->get_field_id('oqeygalleryid'); ?>" class="widefat">
					<option value="0" <?php if (0 == $instance['oqeygalleryid']) echo "selected='selected' "; ?> ><?php _e('All galleries', 'oqey-gallery'); ?></option>
<?php
				if($gals) {
					foreach($gals as $gal) {
					echo '<option value="'.$gal->id.'" ';
					if ($gal->id == $instance['oqeygalleryid']) echo "selected='selected' ";
					echo '>'.trim($gal->title).'</option>'."\n\t"; 
					}
				}
?>
				</select>
		</p>
		<p><label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height:', 'oqey-gallery'); ?></label> 
        <input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" style="padding: 3px; width: 45px;" value="<?php echo $height; ?>" /></p>
		
        <p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:', 'oqey-gallery'); ?></label> 
        
        <input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" style="padding: 3px; width: 45px;" value="<?php echo $width; ?>" /></p>
	    <p>
           <label for="<?php echo $this->get_field_id('autostart'); ?>">
		   <input id="<?php echo $this->get_field_id('autostart'); ?>" name="<?php echo $this->get_field_name('autostart'); ?>" type="checkbox" value="1" <?php checked(true , $instance['autostart']); ?> /> <?php _e('Autostart','oqeygallery'); ?>
		</p>
 	    <p>
         <label for="<?php echo $this->get_field_id('oqeywidgettext'); ?>"><?php _e('Few words:', 'oqey-gallery'); ?></label> 
        <textarea id="<?php echo $this->get_field_id('oqeywidgettext'); ?>" name="<?php echo $this->get_field_name('oqeywidgettext'); ?>" style="padding: 3px; width: 100%;"><?php echo $oqeywidgettext; ?></textarea></p>
		
    <?php
	}
}
/*End Widget*/
 
function addoQeyMediaIcon($context){
    global $post_ID, $temp_ID, $wpdb;    
	$qgall_upload_iframe_src = "media-upload.php?type=oqeygallery&amp;post_id=$post_ID";
	$qgall_iframe_src = apply_filters('qgall_iframe_src', "$qgall_upload_iframe_src&amp;tab=oqeygallery");
	$title = __('Add oQey Gallery');
	$qgall_button_src = oQeyPluginUrl().'/images/oqeyinsert.png';
    return $context.'<a href="'.$qgall_upload_iframe_src.'&amp;TB_iframe=1&amp;height=500&amp;width=640" class="thickbox" id="add_oqeygallery" title="'.$title.'"><img src="'.$qgall_button_src.'" alt="'.$title.'" /></a>';
}

add_filter('media_buttons_context', 'addoQeyMediaIcon');

function oQeyPluginUrl() {
	$url = get_option('siteurl') . '/wp-content/plugins/oqey-gallery';   
	return $url;
}

function oQeyPluginRepoUrl() {
	$url = get_option('siteurl') . '/wp-content/oqey_gallery';   
	return $url;
}

function oQeyPluginRepoPath() {
	$path = OQEY_ABSPATH . 'wp-content/oqey_gallery';   
	return $path;
}

oqeygall_tab_content(); // start tab content

function oqeygall_tab_content(){
    global $post_ID, $temp_ID, $wpdb;
    //media_upload_header();
	if($_GET['type'] == "oqeygallery"){ include ("insert_in_post.php"); }
}

add_action('admin_menu', 'oqey_add_pages');

function oqey_add_pages() {
	$icon = oQeyPluginUrl().'/images/oqeygallery.png';
    $oqeym = plugin_basename( dirname(__FILE__));
    
    add_menu_page('oQey Gallery plugin', 'oQey Gallery', 8, $oqeym, 'oqey_top_page', $icon);	  
    add_submenu_page($oqeym,'oQey Gallery plugin', __('Settings', 'oqey-gallery'), 'oQeySettings', 'oQeysettings',  'oqey_settings_page');
	//add_submenu_page($oqeym,'oQey-Gallery plugin', 'Categories', 8, 'oQeyCategories',  'oqey_categories_page');
    add_submenu_page($oqeym, 'Galleries', __('Galleries', 'oqey-gallery'), 'oQeyGalleries', 'oQeyGalleries', 'oqey_galleries_page');    
    add_submenu_page($oqeym, 'Video', __('Video', 'oqey-gallery'), 'oQeyVideo', 'oQeyVideo', 'oqey_video_page');    
	add_submenu_page($oqeym, 'Skins', __('Skins', 'oqey-gallery'), 'oQeySkins', 'oQeySkins', 'oqey_galleries_skin_page');
	add_submenu_page($oqeym, 'Music', __('Music', 'oqey-gallery'), 'oQeyMusic', 'oQeyMusic', 'oqey_music_page');
    add_submenu_page($oqeym, 'Roles', __('Roles', 'oqey-gallery'), 'oQeyRoles', 'oQeyRoles', 'oqey_roles_page');
	add_submenu_page($oqeym, 'Trash', __('Trash', 'oqey-gallery'), 'oQeyTrash', 'oQeyTrash', 'oqey_trash_page');	
}

function oqey_top_page(){
?>
<div class="wrap">
    <h2 style="width: 900px;"><?php _e('oQey Gallery plugin', 'oqey-gallery'); ?>
    <div style="margin-left:250px; float:right; width: 200px; height: 20px;">
     <div id="fb-root"></div>
     <div class="fb-like" data-href="http://www.facebook.com/oqeysites" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="tahoma"></div>
     <div class="fb-send" data-href="http://oqeysites.com"></div>
    </div>
    </h2>
</div>

<div class="metabox-holder has-right-sidebar">
<!--
<div class="inner-sidebar" style="margin-right:30px;">
 <div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position: relative;">
  <div id="sm_pnres" class="postbox" >
				<h3 class="hndle"><span>Donate</span></h3>
				<div class="inside">
				  <p>If you really like this plugin and find it useful, help to keep this plugin free and constantly updated by clicking the donate button below.</p>
                   <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="3ZV8CCFYAUYKJ"><input alt="PayPal - The safer, easier way to donate online!" name="submit" src="<?php echo oQeyPluginUrl(); ?>/images/btn_donate.gif" type="image"/><img src="https://www.paypal.com/en_US/i/scr/pixel.gif" alt="" width="1" border="0" height="1"/></form>
				</div><br />
			</div>
</div>
</div>
-->
										
<div class="has-sidebar sm-padded">
	<div id="post-body-content" class="has-sidebar-content">
	  <div class="meta-box-sortabless">
        
       <div class="postbox" style="width:870px;">
        <h3 class="hndle"><span><?php _e('News', 'oqey-gallery'); ?></span></h3>
	    <div class="inside" style="font-size:13px; padding:10px auto; text-align:justify;">        
        <p>
        <?php               
        $url = "http://oqeysites.com/updater/get-oqey-gallery-news.php";         	
        $response = wp_remote_post( $url, array(
	    'method' => 'POST',
	    'timeout' => 45,
	    'redirection' => 5,
	    'httpversion' => '1.0',
	    'blocking' => true,
	    'headers' => array('User-Agent' => 'oQeySitesNewsBot'),
	    'body' => array( 'domainurl' => get_option('siteurl') )
        )
        );        
        $code = (int) wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        if( $code == 200 ){
            if($body!=""){
                echo urldecode($body);
            }
           }
        ?>
        </p>
       </div>
      </div>
      
      <div class="postbox" style="width:870px;">
				<h3 class="hndle"><span>WP Themes and Site templates</span></h3>                
                  <div class="inside" style="font-size:13px;text-align:justify;"> 
	               <p>If you are interested to buy an original WP theme, oQey Sites recommends the following themes. Check it out!</p>
	               <a href="http://themeforest.net/?ref=oqeysites" target="_blank"><img style="border:none;" src="<?php echo oQeyPluginUrl(); ?>/images/tf_728x90_v5.gif" width="728" height="90" /></a>
                  </div>
      </div>

      <div class="postbox" style="width:870px;">
				<h3 class="hndle"><span><?php _e('Donate', 'oqey-gallery'); ?></span></h3>                
                <div class="inside" style="font-size:13px;padding:10px auto;text-align:justify;"> 
                <div align="left"><p>If you really like this plugin and find it useful, help to keep this plugin free and constantly updated by clicking the donate button below.</p></div>
                
                <div align="right">
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank"><input type="hidden" name="cmd" value="_s-xclick"/><input type="hidden" name="hosted_button_id" value="3ZV8CCFYAUYKJ"/><input alt="PayPal - The safer, easier way to donate online!" name="submit" src="<?php echo oQeyPluginUrl(); ?>/images/btn_donate.gif" type="image"/><img src="https://www.paypal.com/en_US/i/scr/pixel.gif" alt="" width="1" border="0" height="1"/></form>
                </div>
                </div>
      </div>
        
      <div class="postbox" style="width:870px;">
				<h3 class="hndle"><span><?php _e('About', 'oqey-gallery'); ?></span></h3>
				<div class="inside" style="font-size:13px; padding:10px auto; text-align:justify;">
                <p>oQey Gallery is a premium grade plugin for managing images and video, creating photo slideshows with music, photo &amp; video galleries that works fine under iPhone / iPad and other mobile devices. Flash version of the slideshow is automatically replaced by the HTML5 | Java slideshow on a non-flash device. Flash gallery supports customizable skins, so you can change the way it looks with a few clicks using the skin options tool. Commercial skins are also available as well as custom built photo / video galleries and slideshows for professionals. This plugin uses built-in WP functions and a simple batch upload system. 
                   Check this out on <a href="http://oqeysites.com/" target="_blank">oqeysites.com</a></p>
                <p><a href="http://oqeysites.com"><img style="border: none;" src="<?php echo WP_PLUGIN_URL; ?>/oqey-gallery/images/oqeybanner.jpg" /></a></p>
                </div>

      </div>

	 </div>
	</div>
 </div>
</div>
<?php
}

function oqey_galleries_page(){ include("managegal.php"); }
function oqey_galleries_skin_page(){ include("manageskins.php"); }
function oqey_music_page(){ include("managemusic.php"); }
function oqey_trash_page(){ include("managetrash.php"); }
function oqey_roles_page(){ include("manageroles.php"); }
function oqey_settings_page(){ include("managesettings.php"); }
function oqey_video_page(){ include("managevideo.php"); }

/*oqeygallery shortcode*/
add_shortcode( 'oqeygallery', 'AddoQeyGallery' );
add_shortcode( 'qgallery', 'AddoQeyGallery' );

function AddoQeyGalleryToFeed($atts){
    global $oqeycounter, $post_ID, $wpdb, $post;
    
    $id = esc_sql( $atts['id'] );
    
    $oqey_galls = $wpdb->prefix . "oqey_gallery";
    $oqey_images = $wpdb->prefix . "oqey_images";
    $oqey_skins = $wpdb->prefix . "oqey_skins";

    $gal = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_galls WHERE id = %d AND status !='2'", $id ));
    
    if($gal){
       $folder = $gal->folder;
       $gal_title = urlencode($gal->title);

       if($gal->skin_id!="0"){ 
          $skin = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE id = '".$gal->skin_id."'");
       }else{ 
          $skin = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE status = '1'"); 
       }
       
       if($gal->splash_only==1){ $s = "AND id!=".$gal->splash_img; }else{ $s=""; }
       
       $all = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE gal_id = %d AND status!=2 ".$s." ORDER BY img_order ASC", $id  ));
       
       $imgs .= '<span class="all_images">';	
    
        foreach($all as $i){
	 
	 if($i->img_type!="video"){
            
          if($i->img_type=="nextgen"){
            
            $gimg = get_option('siteurl').'/'.trim($i->img_path).'/';
        
           }else{
        
            $gimg = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$gal->folder.'/galimg/';
        
           } 
	 
          $imgs .= '<p><img src="'.$gimg.trim($i->title).'" alt="'.urlencode(trim($i->alt)).'" style="margin-top:3px;"/></p>'; 
         }
        } 
        $imgs .= '</span>';
      }
       return $imgs;
}

function oqey_gallery_front_head(){
   
   $applejs = WP_PLUGIN_URL . '/oqey-gallery/js/oqey-js-drag-iphone.js';
   echo "\n".'<script type="text/javascript" src="' . $applejs . '"></script>';
   
   $oqeyjs = WP_PLUGIN_URL . '/oqey-gallery/js/oqey.js';
   echo "\n".'<script type="text/javascript" src="' . $oqeyjs . '"></script>';
   
   echo "\n".'<script type="text/javascript">';
   echo "\n".'jQuery(document).ready(function($){';
   
   echo "\n".'jQuery(".larrowjs").hover(
         function () {
           jQuery(this).attr("src" , "'.WP_PLUGIN_URL . '/oqey-gallery/images/arrow-left-hover.png");
         },
         function () {
           jQuery(this).attr("src" , "'.WP_PLUGIN_URL . '/oqey-gallery/images/arrow-left.png");
         });

        jQuery(".rarrowjs").hover(
        function () {
          jQuery(this).attr("src" , "'.WP_PLUGIN_URL . '/oqey-gallery/images/arrow-right-hover.png");
        },
        function () {
          jQuery(this).attr("src" , "'.WP_PLUGIN_URL . '/oqey-gallery/images/arrow-right.png");
        });';        
   echo "\n".'});';
   echo "\n".'</script>';
   //__('Settings', 'oqey-gallery')
}

add_action('wp_head', 'oqey_gallery_front_head');

function getUserNow($userAgent) {
    $crawlers = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|' .
    'AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|' .
    'GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby|yandex|facebook';
    $isCrawler = (preg_match("/$crawlers/i", $userAgent) > 0);
    return $isCrawler;
}

function AddoQeyGallery($atts){
   global $oqeycounter, $post_ID, $wpdb, $post, $wp_query;
   
   if (is_feed()) {

     return AddoQeyGalleryToFeed($atts);

   }else{

   if($atts['width']!=""){ $oqey_width = $atts['width']; }else{ $oqey_width = get_option('oqey_width'); $oqey_width_n = get_option('oqey_width'); }
   if($atts['height']!=""){ $oqey_height = $atts['height']; }else{ $oqey_height = get_option('oqey_height'); }
   if($atts['autoplay']!=""){ $oqey_autoplay = $atts['autoplay']; }else{ $oqey_autoplay = "false"; }
 
   $id = str_replace(":", "", $atts['id']); 
   
   if(empty($id)){        
   
       $id = str_replace(":", "", $atts[0] );     
      
   }
   
   $id = esc_sql( $id );
   
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_images = $wpdb->prefix . "oqey_images";
   $oqey_skins = $wpdb->prefix . "oqey_skins";
   
   $oqey_BorderSize = get_option('oqey_BorderSize');
   $oqey_bgcolor = get_option('oqey_bgcolor');
   $plugin_url_qu = oQeyPluginUrl();
   $plugin_repo_url = oQeyPluginRepoUrl();
   
   $skinoptionsrecorded = "false";

   $gal = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $oqey_galls WHERE id = %d AND status !='2'", $id ) );
   
   if($gal){
            
      $folder = $gal->folder;
      $gal_title = urlencode($gal->title);

      if($gal->skin_id!="0"){ 
        
         $skin = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE id = '".$gal->skin_id."'");
         $options = "oqey_skin_options_".$skin->folder; 
         $all = json_decode(get_option($options));
         
         if(!empty($all)){
            
            $skinoptionsrecorded = "true";
         
         }
         
      }else{ 
         
         $skin = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE status = '1'"); 
         $options = "oqey_skin_options_".$skin->folder; 
         $all = json_decode(get_option($options));
         
         if(!empty($all)){
            
            $skinoptionsrecorded = "true";
         
         }      
      }
      
      $link =  OQEY_ABSPATH . 'wp-content/oqey_gallery/skins/'.oqey_getBlogFolder($wpdb->blogid).$skin->folder.'/'.$skin->folder.'.swf';
      
      if(!is_file($link)){
        
         $skin = $wpdb->get_row("SELECT * FROM $oqey_skins WHERE status != '2' LIMIT 0,1"); 
         $options = "oqey_skin_options_".$skin->folder; 
         $all = json_decode(get_option($options));
         
         if(!empty($all)){
            
            $skinoptionsrecorded = "true";
         
         }         
      }
       
      if($gal->splash_only==1){ 
        
          $s = "AND id!=".$gal->splash_img; 
      
      }else{ 
        
          $s=""; 
      
      }
      
      $all = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $oqey_images WHERE gal_id = %d AND status!=2 ".$s." ORDER BY img_order ASC", $id  ));

      define('IBROWSER', preg_match('~(iPad|iPod|iPhone)~si', $_SERVER['HTTP_USER_AGENT']));
      define('OQEYBROWSER', preg_match('~(WebKit)~si', $_SERVER['HTTP_USER_AGENT']));      
      $gimg = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$gal->folder.'/galimg/';	

      $isCrawler = getUserNow($_SERVER['HTTP_USER_AGENT']); // check if is a crawler

      if ($isCrawler || (is_plugin_active('wptouch/wptouch.php') && IBROWSER)){
    
        if ($isCrawler){
            
           $imgs = "<p align='center'>".urldecode($gal_title)."</p>";
        
        }else{ 
            
            if(is_plugin_active('wptouch/wptouch.php') && IBROWSER){
                
               if(get_option('oqey_gall_title_no')=="on"){
   	           
                  $imgs = '<div style="margin-left:auto; margin-right:auto; width:100%; text-align:center;">'.urldecode($gal_title).'</div>';
	           
               } 
            }
        }
    
        foreach($all as $i){ 
            
          if($i->img_type=="nextgen"){
        
           $gimg = get_option('siteurl').'/'.trim($i->img_path).'/';
        
          }
         
           if($i->img_type!="video"){
          
             $imgs .= '<p style="margin-left:auto; margin-right:auto;display:block;text-align:center;">
	                  <img src="'.$gimg.trim($i->title).'" alt="Photo '.urldecode(trim($i->alt)).'" style="margin-top:1px;height:auto;max-width:100%;"/></p>'; 
             
             if(get_option('oqey_show_captions_under_photos')=="on"){
                
		$comments = '';
		
		if(!empty($i->comments)){
		   $comments = ' | '.trim(urldecode($i->comments));
	        }
			       
                $imgs .= '<p class="oqey_p_comments">'.trim(urldecode($i->alt)).$comments."</p>";
                
             }
          
           }
           
        } 
        
        if ($isCrawler){ 
            
            $imgs .= '<div style="font-size:11px;margin-left:auto;margin-right:auto;width:100%;text-align:right;"><a href="http://oqeysites.com" target="_blank"><img src="'.$plugin_url_qu.'/images/oqey-logo.png" alt="WordPress Photo Gallery Plugin by oQeySites"/></a></div>';
            return $imgs; 
        
        }else{ 
            
            return $imgs; 
        
        }
        
    }else{	
	
	if(get_option('oqey_gall_title_no')=="on"){
	   
   	   $galtitle = '<div style="margin-left:auto; margin-right:auto; width:100%; text-align:center;">'.urldecode($gal_title).'</div>';
	
    }else{ 
        
        $galtitle =""; 
    
    }
	
    $allimgs = array();
    
    if( get_option('oqey_noflash_options')=="incolums" ){
        
        $top_margin ='margin-top:3px;';
        
    }else{
        
        $top_margin = '';
        
    }
    
    $bgimages = array();
	
	foreach($all as $i){ 
	  
       $vpath = pathinfo(urldecode($i->title));
      
       if( OQEYBROWSER && $i->img_type=="video" && ($vpath["extension"]=="mp4" || $vpath["extension"]=="m4p") ){
             
            $vdurl = parse_url(urldecode($i->title));
            
            if( !empty($vdurl['host']) ){
        
                $vurl = trim($i->title);
         
            }else{
        
                $vurl = get_option('siteurl').'/'.trim($i->title);
            }
        
         $imgs .= '[div class="oqeyimgdiv" style="width:'.$oqey_width.'px; height:'.($oqey_width/1.5).'px;'.$top_margin.'"][video class="videocnt" width="'.$oqey_width.'" height="'.($oqey_width/1.5).'" controls="controls"][source src="'.$vurl.'"  type="video/mp4"][/video][/div]';
        

       }elseif($i->img_type!="video" ){
        
        if($i->img_type=="nextgen"){
      
          $ipath = OQEY_ABSPATH.'/'.trim($i->img_path).'/';
          $img_type = "nextgen";
          $img_f_path = urlencode(trim($i->img_path));
      
          }else{
        
          $ipath = OQEY_ABSPATH.'wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid).$gal->folder.'/galimg/';
          $img_type = "oqey";
          $img_f_path = "";
      
        }
       
	$img_path = $ipath.trim($i->title);   
    $size = @getimagesize( $img_path );
    
	if ( $size ){
	
       list($iwidth, $iheight, $itype, $iattr)= $size;
    
    }else{
	
       $iwidth = 900;
       $iheight = 600;
	
    }
    
       $img_holder_h = $oqey_width/1.5; ///??????????????????
       
       if(!empty($atts['height'])){ $img_holder_h = $atts['height']; }else{ $img_holder_h = $oqey_width/1.5; }
       $customlink = "";
       $div_custom_margin = "";
       $custom_bg_img = "background:transparent;";       
       
       if( !empty($atts['customheight']) && !empty($atts['customwidth']) ){ 
        
        $img_holder_h = (int)$atts['customheight']+14; 
        $custom_height_n = (int)$atts['customheight']; 
        $oqey_width_n = (int)$atts['customwidth'];
        $customlink = '[div style="background: url('.$plugin_url_qu.'/images/seemorebtn.png) center center no-repeat;width:715px;height:79px;position:absolute;bottom:27px;margin-left:12px;"][div class="fpage_first_line"]'.trim($i->alt).'[/div][div class="fpage_second_line"]'.trim($i->comments).'[/div][a href="'.trim($i->img_link).'" style="position:absolute;left:590px;top:35px;height:15px;width:70px;font-size:0px;" target="_blank"]SEE MORE[/a][/div]';
        //$div_custom_margin = "margin-top:100px;";
        $custom_bg_img = "background:url(".$plugin_url_qu."/images/imagebg.png) center center no-repeat;";
        $top_margin ='margin-top:15px;';
        $c_width = (int)$atts['customwidth'];
        //$custom_margin_left ='margin-left:100px;';
        
        }else{
            
            $c_width = $oqey_width;
            $oqey_width_n = $oqey_width;
        }
       
       $d = wp_expand_dimensions($iwidth, $iheight, $oqey_width, $img_holder_h);
       if(!empty($atts['process'])){ $process = '&amp;process='.$atts['process']; }else{ $process = '&amp;process=on'; }
 	   $img_full_root = get_option('siteurl').'/wp-content/plugins/oqey-gallery/oqeyimgresize.php?width='.$d[0].'&amp;new_height='.$d[1].'&amp;folder='.$gal->folder.'&amp;img='.trim($i->title).'&amp;img_type='.$img_type.'&amp;img_f_path='.$img_f_path.$process;
       $imgs .= '[div class="oqeyimgdiv" style="background: url('.$img_full_root.') center top no-repeat;width:'.$oqey_width_n.'px;height:'.$img_holder_h.'px;'.$top_margin.'"]'.$customlink.'[/div]';
       
       //$bgimages[] = $img_full_root;
       //$imgs .= '[div class="oqeyimgdiv" id="oqeyimgdiv0" style="background: center top no-repeat;width:'.$oqey_width_n.'px;height:'.$img_holder_h.'px;'.$top_margin.'"]'.$customlink.'[/div]';
       //$imgs .= '[img src="'.$img_full_root.'"/]';
       
       if(get_option('oqey_show_captions_under_photos')=="on" && get_option('oqey_noflash_options')=="incolums" ){
                
                $imgs .= '[p class="oqey_p_comments"]'.trim($i->comments)."[/p]";
                
        }
       }	
    }	
    
	if(get_option("oqey_backlinks")=="on"){ 
	
       $oqeybacklink = '<div style="font-size:11px;margin-left:auto;margin-right:auto;width:100%;text-align:center;font-family:Arial,Helvetica,sans-serif">powered by <a href="http://oqeysites.com" target="_blank">oQeySites</a></div>'; 
	
    }
    
    $custom_height = "";    
    $margin_top = $img_holder_h/2-50;	
	
	if( get_option('oqey_noflash_options')=="incolums" ){  
	   
	   $incolums = "on";
	   $optouch = "off"; 
       $custom_height = "auto";
       $custom_height_n = "auto";
       $img_holder_h = "auto";
	
    }
	
    if( get_option('oqey_noflash_options')=="injsarr" ){ 
	
       $incolums = "off"; 
	   $optouch = "off"; 	
       
       if(get_option("oqey_backlinks")=="on"){       
           $custom_height = $custom_height + 25; 
       }
       
       if(get_option('oqey_gall_title_no')=="on"){
           $custom_height = $custom_height + 25;  
           $margin_top = $margin_top + 25;         
       }
       
       $custom_height = $custom_height + $img_holder_h."px";
       $custom_height_n = $custom_height;
	
    }
	
    if( get_option('oqey_noflash_options')=="injsarrtouch" ){ 
	
       $incolums = "off"; 
	   $optouch = "on";             
       if(get_option("oqey_backlinks")=="on"){       
           $custom_height = $custom_height + 25;    
       }
       
       if(get_option('oqey_gall_title_no')=="on"){
           $custom_height = $custom_height + 25;
           $margin_top = $margin_top + 25;        
       }       
	   $custom_height = $custom_height + $img_holder_h."px";
       $custom_height_n = $custom_height;
    
    }
    
	$margleft = $oqey_width - 44;
        
    if(get_option('oqey_flash_gallery_true')){ $pfv = "on"; }else{ $pfv = "off"; }
    
    $custom_margin_top = "";
    
    if( !empty($atts['custombgwidth']) && !empty($atts['custombgheight']) ){
        
        $oqey_width_n = (int)$atts['custombgwidth']; 
        $custom_height_n = (int)$atts['custombgheight'];  
        $margleft = $oqey_width_n-44;      
        $custom_margin_top = "margin-top:35px;";
        
    }
    
   /*Custom words - set arrows*/ 
   $arrows=="on";
   $arrows = $atts['arrows'];
   
   if($arrows=="off"){
    
    $arrowleft = "";
    $arrowtight="";    
    
   }else{
    
    $arrowleft = '<div style="position:absolute;left:0px;top:'.$margin_top.'px;z-index:99999;" class="gall_links">
                  <a id="prev'.$oqeycounter.'" href="#back" style="text-decoration:none;" onclick="pausePlayer();">
                    <img class="larrowjs" src="'.$plugin_url_qu.'/images/arrow-left.png" style="width:44px;height:94px;border:none;cursor:pointer;cursor:hand"/>
                  </a>
                </div>';
    
    $arrowtight = '<div style="position:absolute;left:'.$margleft.'px;top:'.$margin_top.'px;z-index:99999;" class="gall_links">
                     <a id="next'.$oqeycounter.'" href="#next" style="text-decoration:none;" onclick="pausePlayer();">
                        <img class="rarrowjs" src="'.$plugin_url_qu.'/images/arrow-right.png" style="width:44px; height:94px; border:none;cursor:pointer;cursor:hand"/>
                     </a>
                   </div>';
    
   }
   
   if($custom_height_n!="auto"){
   //$custom_height_n = $custom_height_n."px";
   $img_holder_h = $img_holder_h."px";
   }   
   
   $oqeyblogid = oqey_getBlogFolder($wpdb->blogid);
 
ob_start();	
print <<< SWF
<div class="responsive_oqey" id="oqey_image_div{$oqeycounter}" style="position:relative;width:{$oqey_width_n}px;height:{$custom_height_n};display:none;margin: 0 auto;{$div_custom_margin}{$custom_bg_img}">
{$arrowleft}{$arrowtight}{$galtitle}
<div id="image{$oqeycounter}" style="width:{$c_width}px;height:{$img_holder_h};display:none;background:transparent;margin: 0 auto;{$custom_margin_top}" class="oqey_images"></div>
{$oqeybacklink}
</div>
<script type="text/javascript">
      var flashvars{$oqeycounter} = {
                          autoplay:"{$oqey_autoplay}",
                           flashId:"{$oqeycounter}",
		                      FKey:"{$skin->comkey}",
	                   GalleryPath:"{$plugin_url_qu}",	
                         GalleryID:"{$id}-{$post->ID}",
					      FirstRun:"{$skin->firstrun}"
					 };
	var params{$oqeycounter} = {bgcolor:"{$oqey_bgcolor}", allowFullScreen:"true", wMode:"transparent"};
	var attributes{$oqeycounter} = {id: "oqeygallery{$oqeycounter}"};
	swfobject.embedSWF("{$plugin_repo_url}/skins/{$oqeyblogid}{$skin->folder}/{$skin->folder}.swf", "flash_gal_{$oqeycounter}", "{$oqey_width}", "{$oqey_height}", "8.0.0", "", flashvars{$oqeycounter}, params{$oqeycounter}, attributes{$oqeycounter});
</script> 
<div id="flash_gal_{$oqeycounter}" style="width:{$oqey_width}px; min-width:{$oqey_width}px; min-height:{$oqey_height}px; height:{$oqey_height}px; margin: 0 auto;">
<script type="text/javascript">
  jQuery(document).ready(function($){ 
    var pv = swfobject.getFlashPlayerVersion();
    oqey_e(pv, {$oqeycounter}, '{$imgs}', '{$optouch}', '{$incolums}', '{$pfv}', '{$allimages}');
    });
    var htmlPlayer = document.getElementsByTagName('video');
    function pausePlayer(){ for(var i = 0; i < htmlPlayer.length; i++){htmlPlayer[i].pause();} }</script></div>
SWF;
$output = ob_get_contents();
ob_end_clean();
$oqeycounter ++;
return $output;
}
}//end crawler check
}
}
?>