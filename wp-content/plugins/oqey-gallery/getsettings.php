<?php
include('../../../wp-load.php');

  if(get_option('oqey_crop_images')=="on"){ $crop = "true"; }else{ $crop = "false"; }
  if(get_option('oqey_HideThumbs')=="on"){ $HideThumbs = "true"; }else{ $HideThumbs = "false"; }
  if(get_option('oqey_LoopOption')=="on"){ $LoopOption = "true"; }else{ $LoopOption = "false"; }

  $maxw = get_option('oqey_width');
  $maxh = get_option('oqey_height');

if(get_option('oqey_limitmax')=="on"){
    
   if(get_option('oqey_max_width')!=""){ $maxw = get_option('oqey_max_width'); }
   if(get_option('oqey_max_height')!=""){ $maxh = get_option('oqey_max_height'); }
   $maximum .= 'MaximumWidth='.$maxw.'&MaximumHeight='.$maxh.'&';

}else{ 
    
   $maximum .= ''; 

}

  if(get_option('oqey_BorderOption')=="on"){ $BorderOption = "true"; }else{ $BorderOption = "false"; }
  if(get_option('oqey_AutostartOption')=="on"){ $AutostartOption = "true"; }else{ $AutostartOption = "false"; }

if(get_option('oqey_CaptionsOption')=="on"){
  
  $CaptionsOption = "true";
  $CaptionPosition = get_option('oqey_options');
  $maximum .= 'CaptionsOption='.$CaptionsOption.'&CaptionPosition='.$CaptionPosition.'&';

}else{ 

  $CaptionsOption = "false";
  $maximum .= 'CaptionsOption='.$CaptionsOption.'&'; 

}

//coloreaza pentru flash :))
function oqey_makeFlashColor($csscolor){	
  
  $color = preg_replace('/#/i', '0x', $csscolor);
  return $color;

}

function get_oqey_domain($url){
    
  $pieces = parse_url($url);
  
  $domain = isset($pieces['host']) ? $pieces['host'] : '';
  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,7})$/i', $domain, $regs)) {
    return $regs['domain'];
  }
  
  return false;

}

echo $maximum.'GalleryWidth='.get_option('oqey_width').'&GalleryHeight='.get_option('oqey_height').'&CropOption='.$crop.'&ThumbWidth='.get_option('oqey_thumb_width').'&ThumbHeight='.get_option('oqey_thumb_height').'&TransitionTime='.get_option('oqey_effects_trans_time').'&TransitionInterval='.get_option('oqey_pause_between_tran').'&HideThumbs='.$HideThumbs.'&LoopOption='.$LoopOption.'&BorderOption='.$BorderOption.'&BorderColor='.oqey_makeFlashColor(get_option('oqey_border_bgcolor')).'&BackgroundColor='.oqey_makeFlashColor(get_option('oqey_bgcolor')).'&AutostartOption='.$AutostartOption.'&domain='.get_oqey_domain(get_option('siteurl')).'&TransitionType='.get_option('oqey_effect_transition_type');
?>