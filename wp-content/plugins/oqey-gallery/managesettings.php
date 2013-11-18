<?php
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'managesettings.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!');

if(isset($_POST['page_options'])){
    
$options = explode(',', $_POST['page_options']);

if ($options){
    			foreach ($options as $option) {
    				$option = trim($option);
    				$value = isset($_POST[$option]) ? trim($_POST[$option]) : false;
    				update_option($option, $value);
    			}
}

echo '<div id="message" style="width:903px; margin-bottom:10px;"><p class="updated fade">'.__('Settings saved.', 'oqey-gallery').'</p></div>';

}
?>
<div class="wrap">
    <h2 style="width: 930px;"><?php _e('oQey Gallery settings', 'oqey-gallery'); ?>
    <div style="margin-left:250px; float:right; width: 200px; height: 20px;">
     <div id="fb-root"></div>
     <div class="fb-like" data-href="http://www.facebook.com/oqeysites" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="tahoma"></div>
     <div class="fb-send" data-href="http://oqeysites.com"></div>
    </div>
    </h2>
</div>

<a href="<?php echo admin_url('admin.php?page=oQeySkins&showskins=yes'); ?>"><img src="<?php echo oQeyPluginUrl().'/images/'; ?>settings_banner.png"/></a><br /><br />

<div class="wrap">
<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function($) {

var numberRegex = /^[+-]?\d+(\.\d+)?([eE][+-]?\d+)?$/;

if(jQuery("#oqey_limitmax").attr('checked')){ 
jQuery(".maxon").show(); 
}else{ 
jQuery(".maxon").hide(); 
}	
 						
jQuery("#oqey_limitmax").click(function(){ 
if(jQuery("#oqey_limitmax").attr('checked')){ 
jQuery(".maxon").show(); 
}else{ 
jQuery(".maxon").hide(); 
}	
});

if(jQuery("#oqey_CaptionsOption").attr('checked')){ 
jQuery(".oqey_radio_options").show(); 
}else{ 
jQuery(".oqey_radio_options").hide(); 
}

if(jQuery("#oqey_BorderOption").attr('checked')){ 
jQuery(".oqey_border_bgcolor_tr").show(); 
}else{ 
jQuery(".oqey_border_bgcolor_tr").hide(); 
}

jQuery("#oqey_BorderOption").click(function(){ 
if(jQuery("#oqey_BorderOption").attr('checked')){ 
jQuery(".oqey_border_bgcolor_tr").show(); 
}else{ 
jQuery(".oqey_border_bgcolor_tr").hide(); 
}	
});

jQuery("#oqey_CaptionsOption").click(function(){ 
if(jQuery("#oqey_CaptionsOption").attr('checked')){ 
jQuery(".oqey_radio_options").show(); 
}else{ 
jQuery(".oqey_radio_options").hide(); 
}	
});

jQuery("#oqey_width").change(function(){
var x = jQuery("#oqey_width").val();
if(numberRegex.test(x)) {
if(parseInt(x)<200){ 
jQuery("#oqey_width").val("200"); 
var x = 200;
}

var y = jQuery("#oqey_max_width").val();
if(y==""){ jQuery("#oqey_max_width").val(x);  }

if(parseInt(y)<parseInt(x)){ 
jQuery("#oqey_max_width").val(x); 
}

}else{ alert('<?php _e('Only numbers allowed!!!', 'oqey-gallery'); ?>'); } 
});

jQuery("#oqey_height").change(function(){
var x = jQuery("#oqey_height").val();

if(numberRegex.test(x)) {
if(parseInt(x)<100){ 
jQuery("#oqey_height").val("100");
var x = 100;
}

var y = jQuery("#oqey_max_height").val();
if(y==""){ jQuery("#oqey_max_height").val(x);  }

if(parseInt(y)<parseInt(x)){ 
jQuery("#oqey_max_height").val(x); 
}

}else{ alert('<?php _e('Only numbers allowed!!!', 'oqey-gallery'); ?>'); } 
});

jQuery("#oqey_max_width").change(function(){
				  
var x = jQuery("#oqey_width").val();
var y = jQuery("#oqey_max_width").val();

if(numberRegex.test(y)) {
if(parseInt(y)<parseInt(x)){ jQuery("#oqey_max_width").val(x); }
}else{ alert('<?php _e('Only numbers allowed!!!', 'oqey-gallery'); ?>'); } 
});

jQuery("#oqey_max_height").change(function(){
var h = jQuery("#oqey_height").val();
var x = jQuery("#oqey_max_height").val();
if(numberRegex.test(x)) {
if(parseInt(x)<parseInt(h)){ jQuery("#oqey_max_height").val(h); }
}else{ alert('<?php _e('Only numbers allowed!!!', 'oqey-gallery'); ?>'); } 
});


jQuery("#oqey_thumb_width").change(function(){
var x = jQuery("#oqey_thumb_width").val();
if(numberRegex.test(x)) {
if(parseInt(x)<60){ jQuery("#oqey_thumb_width").val("60"); }
if(parseInt(x)>180){ jQuery("#oqey_thumb_width").val("180"); }
}else{ alert('<?php _e('Only numbers allowed!!!', 'oqey-gallery'); ?>'); } 
});

jQuery("#oqey_thumb_height").change(function(){
var x = jQuery("#oqey_thumb_height").val();
if(numberRegex.test(x)) {
if(parseInt(x)<40){ jQuery("#oqey_thumb_height").val("40"); }
if(parseInt(x)>120){ jQuery("#oqey_thumb_height").val("120"); }
}else{ alert('<?php _e('Only numbers allowed!!!', 'oqey-gallery'); ?>'); } 
});

jQuery("#oqey_effects_trans_time").change(function(){
var x = jQuery("#oqey_effects_trans_time").val();
if(numberRegex.test(x)) {
if(parseFloat(x)<0.1){ jQuery("#oqey_effects_trans_time").val("0.1"); }
if(parseFloat(x)>1.5){ jQuery("#oqey_effects_trans_time").val("1.5"); }
}else{ alert('<?php _e('Only numbers allowed!!!', 'oqey-gallery'); ?>'); }  
});

jQuery("#oqey_pause_between_tran").change(function(){
var x = jQuery("#oqey_pause_between_tran").val();
if(numberRegex.test(x)) {
if(parseFloat(x)<2){ jQuery("#oqey_pause_between_tran").val("2"); }
if(parseFloat(x)>26){ jQuery("#oqey_pause_between_tran").val("26"); }
}else{ alert('<?php _e('Only numbers allowed!!!', 'oqey-gallery'); ?>'); }  
});			

    jQuery('#demo').hide();
    if(jQuery('#oqey_border_bgcolor').val()==""){ jQuery("#oqey_border_bgcolor").val("#FFFFFF"); }
	if(jQuery('#oqey_bgcolor').val()==""){ jQuery("#oqey_bgcolor").val("#FFFFFF"); }
	
	var f = jQuery.farbtastic('#picker');
    var p = jQuery('#picker').css('opacity', 0.25);
    var selected;
    jQuery('.colorwell')
      .each(function () { f.linkTo(this); jQuery(this).css('opacity', 0.75); })
      .focus(function() {
        if (selected) {
          jQuery(selected).css('opacity', 0.75).removeClass('colorwell-selected');
        }
        f.linkTo(this);
        p.css('opacity', 1);
        jQuery(selected = this).css('opacity', 1).addClass('colorwell-selected');
      });
  });

</script>
<div id="demo" style="color: red; font-size: 1em"><?php _e('Please wait, loading files...', 'oqey-gallery'); ?></div>

<form method="POST" action="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=oQeysettings" name="oqey_settings">
<?php wp_nonce_field('update-options'); ?>
<table width="900" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top" align="left">
<table width="450" border="0" cellpadding="1" cellspacing="0">
<tr valign="top">
  <th width="230" height="25" align="left" valign="middle" scope="row"><div align="left"><?php _e('Gallery Width', 'oqey-gallery'); ?></div></th>
  <td width="216" height="25" align="left" valign="middle"><input name="oqey_width" id="oqey_width" type="text" value="<?php echo get_option('oqey_width'); ?>" size="5"/> pix (min. 200)</td>
</tr>
<tr valign="top">
<th height="25" align="left" valign="middle" scope="row"><div align="left"><?php _e('Gallery Height', 'oqey-gallery'); ?></div></th>
<td height="25" align="left" valign="middle"><input name="oqey_height" id="oqey_height" type="text" value="<?php echo get_option('oqey_height'); ?>" size="5"/> pix (min. 100)</td>
</tr>
<tr valign="top">
<td height="25" align="left" valign="middle" scope="row"><?php _e('Limit the image size on fullscreen', 'oqey-gallery'); ?></td>
<td height="25" align="left" valign="middle"><input type="checkbox" name="oqey_limitmax" id="oqey_limitmax" <?php if(get_option('oqey_limitmax')=="on"){ echo 'checked="checked"'; } ?>/></td>
</tr>
<tr valign="top" style="display:none;" class="maxon">
<th width="230" height="25" align="left" valign="middle" scope="row"><?php _e('Width:', 'oqey-gallery'); ?></th>
<td height="25" align="left" valign="middle"><input name="oqey_max_width" id="oqey_max_width" type="text" value="<?php echo get_option('oqey_max_width'); ?>" size="5"/></td>
</tr>
<tr valign="top" style="display:none;" class="maxon">
<th width="230" height="25" align="left" valign="middle" scope="row"><?php _e('Height:', 'oqey-gallery'); ?></th>
<td height="25" align="left" valign="middle"><input name="oqey_max_height" id="oqey_max_height" type="text" value="<?php echo get_option('oqey_max_height'); ?>" size="5"/></td>
</tr>
<tr valign="top">
<td height="25" align="left" valign="middle" scope="row"><?php _e('Crop images to fit the gallery size', 'oqey-gallery'); ?></td>
<td height="25" align="left" valign="middle"><input type="checkbox" name="oqey_crop_images" id="oqey_crop_images" <?php if(get_option('oqey_crop_images')=="on"){ echo 'checked="checked"'; } ?>/></td>
</tr>
<tr valign="top">
<th height="25" align="left" valign="middle" scope="row"><div align="left"><?php _e('Background Color', 'oqey-gallery'); ?></div></th>
<td height="25" align="left" valign="middle"><input type="text" name="oqey_bgcolor" id="oqey_bgcolor" class="colorwell" value="<?php echo get_option('oqey_bgcolor'); ?>" /></td>
</tr>
<tr valign="top">
<th height="25" colspan="2" align="left" valign="middle" scope="row">&nbsp;</th>
</tr>
</table>
    </td>
    <td>
    <table width="450" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td height="200"><div id="picker" style="float:left; margin-left:20px;"></div></td>
  </tr>
</table>    
    </td>
  </tr>
</table>
<table width="900" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="230" height="25"><strong><?php _e('Thumbs width', 'oqey-gallery'); ?></strong></td> 
      <td width="670" height="25"><input name="oqey_thumb_width" id="oqey_thumb_width" type="text" value="<?php echo get_option('oqey_thumb_width'); ?>" size="5"/> pix (min. 60, max. 180)</td>
    </tr>
    <tr>
    <td width="230" height="25"><strong><?php _e('Thumbs height', 'oqey-gallery'); ?></strong></td>
    <td width="670" height="25"><input name="oqey_thumb_height" id="oqey_thumb_height" type="text" value="<?php echo get_option('oqey_thumb_height'); ?>" size="5"/> pix (min. 40, max. 120)</td>
  </tr>
      <tr>
    <td width="230" height="25">&nbsp;</td>
    <td width="670" height="25">&nbsp;</td>
  </tr>
      <tr>
    <td width="230" height="25"><?php _e('Effect transition time:', 'oqey-gallery'); ?></td>
    <td width="670" height="25"><input name="oqey_effects_trans_time" id="oqey_effects_trans_time" type="text" value="<?php echo get_option('oqey_effects_trans_time'); ?>" size="5"/> seconds (min. 0.1, max. 1.5)</td>
  </tr>
  <tr>
    <td width="230"><?php _e('Pause between transitions:', 'oqey-gallery'); ?></td>
    <td width="670"><input name="oqey_pause_between_tran" id="oqey_pause_between_tran" type="text" value="<?php echo get_option('oqey_pause_between_tran'); ?>" size="5"/> seconds</td>
</tr>
  <tr>
    <td width="230" height="35"><?php _e('Effect transition type:', 'oqey-gallery'); ?></td>
    <td width="670">
       <input type="radio" name="oqey_effect_transition_type" id="oqey_effect_transition_type" value="fade" <?php if(get_option('oqey_effect_transition_type')=="fade"){ echo 'checked="checked"'; } ?> style="margin-top:0px;"/>
    <?php _e('Fade', 'oqey-gallery'); ?>&nbsp;&nbsp;
       <input type="radio" name="oqey_effect_transition_type" id="oqey_effect_transition_type1" value="slide" <?php if(get_option('oqey_effect_transition_type')=="slide"){ echo 'checked="checked"'; } ?> style="margin-top:0px;"/>
    <?php _e('Slide', 'oqey-gallery'); ?>   
    </td>
</tr>
<tr valign="top">
<th height="25" align="left" valign="middle" scope="row"><?php _e('Auto hide thumbnails', 'oqey-gallery'); ?></th>
<td height="25" align="left" valign="middle"><input type="checkbox" name="oqey_HideThumbs" id="oqey_HideThumbs" <?php if(get_option('oqey_HideThumbs')=="on"){ echo 'checked="checked"'; } ?>/></td>
</tr>
<tr valign="top">
<th height="25" align="left" valign="middle" scope="row"><?php _e('Continuous play option', 'oqey-gallery'); ?></th>
<td height="25" align="left" valign="middle"><input type="checkbox" name="oqey_LoopOption" id="oqey_LoopOption" <?php if(get_option('oqey_LoopOption')=="on"){ echo 'checked="checked"'; } ?>/></td>
</tr>
<tr valign="top">
<th height="25" align="left" valign="middle" scope="row"><?php _e('Show border', 'oqey-gallery'); ?> *</th>
<td height="25" align="left" valign="middle"><input type="checkbox" name="oqey_BorderOption" id="oqey_BorderOption" <?php if(get_option('oqey_BorderOption')=="on"){ echo 'checked="checked"'; } ?>/></td>
</tr>
<tr valign="top" class="oqey_border_bgcolor_tr">
<th width="230" height="25" align="left" valign="middle" scope="row"><?php _e('Border color', 'oqey-gallery'); ?> *</th>
<td height="25" align="left" valign="middle"><input type="text" name="oqey_border_bgcolor" id="oqey_border_bgcolor" class="colorwell" value="<?php echo get_option('oqey_border_bgcolor'); ?>" /></td>
</tr>
<tr valign="top">
<th height="25" align="left" valign="middle" scope="row"><?php _e('Autostart slideshow', 'oqey-gallery'); ?> *</th>
<td height="25" align="left" valign="middle"><input type="checkbox" name="oqey_AutostartOption" id="oqey_AutostartOption" <?php if(get_option('oqey_AutostartOption')=="on"){ echo 'checked="checked"'; } ?>/></td>
</tr>
<tr valign="top">
<th align="left" valign="top" scope="row"><?php _e('Show captions', 'oqey-gallery'); ?> *</th>
<td align="left" valign="top"><input type="checkbox" name="oqey_CaptionsOption" id="oqey_CaptionsOption" <?php if(get_option('oqey_CaptionsOption')=="on"){ echo 'checked="checked"'; } ?> style="float:left;"/>
  <table border="0" cellspacing="0" cellpadding="5" class="oqey_radio_options" style="display:block; float:left; margin-left:10px;" >
  <tr>
    <td width="60" rowspan="3" align="left" valign="top"><?php _e('Position', 'oqey-gallery'); ?>:</td>
    <td width="20" height="20" align="center" valign="middle"><input type="radio" name="oqey_options" id="tl" value="TL" title="top left" <?php if(get_option('oqey_options')=="TL"){ echo 'checked="checked"'; } ?>/></td>
    <td width="20" height="20" align="center" valign="middle"><input type="radio" name="oqey_options" id="tm" value="TM" title="top middle" <?php if(get_option('oqey_options')=="TM"){ echo 'checked="checked"'; } ?>/></td>
    <td width="20" height="20" align="center" valign="middle"><input type="radio" name="oqey_options" id="tr" value="TR" title="top right" <?php if(get_option('oqey_options')=="TR"){ echo 'checked="checked"'; } ?>/></td>
  </tr>
  <tr>
    <td width="20" height="20" align="center" valign="middle"><input type="radio" name="oqey_options" id="ml" value="ML" title="middle left" <?php if(get_option('oqey_options')=="ML"){ echo 'checked="checked"'; } ?>/></td>
    <td width="20" height="20" align="center" valign="middle"><input type="radio" name="oqey_options" id="mm" value="MM" title="middle middle" <?php if(get_option('oqey_options')=="MM"){ echo 'checked="checked"'; } ?>/></td>
    <td width="20" height="20" align="center" valign="middle"><input type="radio" name="oqey_options" id="mr" value="MR" title="middle right" <?php if(get_option('oqey_options')=="MR"){ echo 'checked="checked"'; } ?>/></td>
  </tr>
  <tr>
    <td width="20" height="20" align="center" valign="middle"><input type="radio" name="oqey_options" id="bl" value="BL" title="bottom left" <?php if(get_option('oqey_options')=="BL"){ echo 'checked="checked"'; } ?>/></td>
    <td width="20" height="20" align="center" valign="middle"><input type="radio" name="oqey_options" id="bm" value="BM" title="bottom middle" <?php if(get_option('oqey_options')=="BM"){ echo 'checked="checked"'; } ?>/></td>
    <td width="20" height="20" align="center" valign="middle"><input type="radio" name="oqey_options" id="br" value="BR" title="bottom right" <?php if(get_option('oqey_options')=="BR"){ echo 'checked="checked"'; } ?>/></td>
  </tr>
</table></td>
</tr>
<tr valign="top" >
  <td height="30" colspan="2" align="left" valign="middle" scope="row">* <?php _e('Options applied to commercial skins only (working with SOME commercial skins)', 'oqey-gallery'); ?></td>
</tr>
<tr valign="top" >
  <td height="30" colspan="2" align="left" valign="middle" scope="row">&nbsp;</td>
</tr>
<tr valign="top" >
  <th height="30" align="left" valign="middle" scope="row"><?php _e('Do not use  Flash skins', 'oqey-gallery'); ?>:</th>
  <td height="30" align="left" valign="middle" scope="row"><input type="checkbox" name="oqey_flash_gallery_true" id="oqey_flash_gallery_true" <?php if(get_option('oqey_flash_gallery_true')=="on"){ echo 'checked="checked"'; } ?>/>
    (<?php _e('if checked, most functions will be disabled', 'oqey-gallery'); ?>)</td>
</tr>
<tr valign="top">
<th height="39" colspan="2" align="left" valign="middle" scope="row"><?php _e('Non-flash browsers options', 'oqey-gallery'); ?>:</th>
</tr>
<tr valign="top">
<th align="left" valign="top" scope="row"><?php _e('Display photos using', 'oqey-gallery'); ?></th>
<td align="left" valign="top">
<input type="radio" name="oqey_noflash_options" id="oqey_noflash_options1" value="incolums" <?php if(get_option('oqey_noflash_options')=="incolums"){ echo 'checked="checked"'; } ?> style="margin-top:0px;"/> 
&nbsp;<?php _e('Column center aligned.', 'oqey-gallery'); ?><br />
<input type="radio" name="oqey_noflash_options" id="oqey_noflash_options2" value="injsarr" <?php if(get_option('oqey_noflash_options')=="injsarr"){ echo 'checked="checked"'; } ?> style="margin-top:0px;"/> 
&nbsp;<?php _e('Javascript slideshow with navigation arrows.', 'oqey-gallery'); ?><br />
<input type="radio" name="oqey_noflash_options" id="oqey_noflash_options3" value="injsarrtouch" <?php if(get_option('oqey_noflash_options')=="injsarrtouch"){ echo 'checked="checked"'; } ?> style="margin-top:0px;"/> 
&nbsp;<?php _e('Javascript slideshow with &quot;touch and slide&quot; option (for touchscreen devices).', 'oqey-gallery'); ?></td>
</tr>
<tr valign="top">
<th height="25" align="left" valign="middle" scope="row"><?php _e('Display gallery title', 'oqey-gallery'); ?></th>
<td height="25" align="left" valign="middle" scope="row">
  <input type="checkbox" name="oqey_gall_title_no" id="oqey_gall_title_no" <?php if(get_option('oqey_gall_title_no')=="on"){ echo 'checked="checked"'; } ?> style="float:left;"/></td>
</tr>
<tr valign="top">
<th height="25" align="left" valign="middle" scope="row"><?php _e('Show captions under each photo', 'oqey-gallery'); ?></th>
<td height="25" align="left" valign="middle" scope="row">
  <input type="checkbox" name="oqey_show_captions_under_photos" id="oqey_show_captions_under_photos" <?php if(get_option('oqey_show_captions_under_photos')=="on"){ echo 'checked="checked"'; } ?> style="float:left;"/></td>
</tr>
<tr valign="top">
<th height="25" colspan="2" align="left" valign="middle" scope="row">&nbsp;</th>
</tr>
</table>
<table width="900" border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
<td width="25" height="25" align="left" valign="middle" scope="row">
<input type="checkbox" name="oqey_backlinks" id="oqey_backlinks" <?php if(get_option('oqey_backlinks')=="on"){ echo 'checked="checked"';  } ?> style="vertical-align: middle;" /></td>
<td width="875" height="25" align="left" valign="middle" scope="row"> <?php _e('I would like to credt oQey Sites with a copyright note and a back link', 'oqey-gallery'); ?> </td>
</tr>
</table>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="oqey_height,oqey_width,oqey_bgcolor,oqey_thumb_width,oqey_thumb_height,oqey_max_width,oqey_max_height,oqey_limitmax,oqey_crop_images,oqey_effects_trans_time,oqey_pause_between_tran,oqey_HideThumbs,oqey_LoopOption,oqey_backlinks,oqey_BorderOption,oqey_AutostartOption,oqey_CaptionsOption,oqey_options,oqey_noflash_options,oqey_gall_title_no,oqey_border_bgcolor,oqey_effect_transition_type,oqey_flash_gallery_true,oqey_show_captions_under_photos" />
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'oqey-gallery') ?>" />
</p>
</form>
</div>