<?php
include ("../../../wp-load.php");
global $wpdb;

if(isset($_REQUEST['galleryid'])){
$id = mysql_real_escape_string($_REQUEST['galleryid']);

$oqey_music = $wpdb->prefix . "oqey_music";
$oqey_music_rel = $wpdb->prefix . "oqey_music_rel";

header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
$r = get_option('siteurl').'/wp-content/oqey_gallery/music/'.oqey_getBlogFolder($wpdb->blogid);

$dax .= '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
$dax .= '<songs>'; 

          $mus = $wpdb->get_results("SELECT * 
								       FROM $oqey_music AS f 
							     INNER JOIN $oqey_music_rel AS s 
								 	     ON f.id = s.music_id
									  WHERE s.gallery_id = '$id'
								   ORDER BY s.mrel_order ASC
										" ); 

   foreach($mus as $m){ 
   
    $dax .= '<song path="'.urlencode(trim($m->link)).'" artist="" title="'.urlencode(trim($m->title)).'"></song>';
   
   }
   
   $dax .= '</songs>';
   echo $dax;
}
?>