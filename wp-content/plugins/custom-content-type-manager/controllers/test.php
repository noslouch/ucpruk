<?php
/*
This is a wholy f'ing mess how WP does this.

*/


//media_upload_form();
/**
 * Manage media uploaded file.
 *
 * There are many filters in here for media. Plugins can extend functionality
 * by hooking into the filters.
 *
 * @package WordPress
 * @subpackage Administration
 */

if ( ! isset( $_GET['inline'] ) )
	define( 'IFRAME_REQUEST' , true );

/** Load WordPress Administration Bootstrap */
require_once('./admin.php');

if (!current_user_can('upload_files'))
	wp_die(__('You do not have permission to upload files.'));

wp_enqueue_script('swfupload-all');
wp_enqueue_script('swfupload-handlers');
wp_enqueue_script('image-edit');
wp_enqueue_script('set-post-thumbnail' );
wp_enqueue_style('imgareaselect');

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

// IDs should be integers
$ID = isset($ID) ? (int) $ID : 0;
$post_id = isset($post_id)? (int) $post_id : 0;

// Require an ID for the edit screen
if ( isset($action) && $action == 'edit' && !$ID ) {
	wp_die(__("You are not allowed to be here"));
}

// Prime the pump: the Flash uploader reads out of this
$_REQUEST['post'] = 577; 
print '<script>var post_id=577;</script>';

// upload type: image, video, file, ..?
if ( isset($_GET['type']) ) {
	$type = strval($_GET['type']);
}
else {
	$type = apply_filters('media_upload_default_type', 'file');
}
//	die($type); // file
// tab: gallery, library, or type-specific
if ( isset($_GET['tab']) )
	$tab = strval($_GET['tab']);
else
	$tab = apply_filters('media_upload_default_tab', 'type');

$body_id = 'media-upload';

// let the action code decide how to handle the request
//if ( $tab == 'type' || $tab == 'type_url' ) {
//		die('xushg');

// wp-admin/includes/media.php : media_upload_file()
//do_action("media_upload_$type"); //
//------------------------------------------------------------------------------
// copied from
// wp-admin/includes/media.php : media_upload_file()
$errors = array();
$id = 0;

if ( isset($_POST['html-upload']) && !empty($_FILES) ) {
	check_admin_referer('media-form');
	// Upload File button was clicked
	$id = media_handle_upload('async-upload', $_REQUEST['post_id']);
	unset($_FILES);
	if ( is_wp_error($id) ) {
		$errors['upload_error'] = $id;
		$id = false;
	}
}

if ( !empty($_POST['insertonlybutton']) ) {
	$href = $_POST['insertonly']['href'];
	if ( !empty($href) && !strpos($href, '://') )
		$href = "http://$href";

	$title = esc_attr($_POST['insertonly']['title']);
	if ( empty($title) )
		$title = basename($href);
	if ( !empty($title) && !empty($href) )
		$html = "<a href='" . esc_url($href) . "' >$title</a>";
	$html = apply_filters('file_send_to_editor_url', $html, esc_url_raw($href), $title);
	return media_send_to_editor($html);
}

if ( !empty($_POST) ) {
	$return = media_upload_form_handler();

	if ( is_string($return) )
		return $return;
	if ( is_array($return) )
		$errors = $return;
}

if ( isset($_POST['save']) ) {
	$errors['upload_notice'] = __('Saved.');
	return media_upload_gallery();
}

//		die('xyxxz');
//return wp_iframe('media_upload_type_form', 'file', $errors, $id );
// Copied from the wp_iframe function
$content_func = 'media_upload_type_form';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php bloginfo('name') ?> &rsaquo; <?php _e('Uploads'); ?> &#8212; <?php _e('WordPress'); ?></title>
<?php
wp_enqueue_style( 'global' );
wp_enqueue_style( 'wp-admin' );
wp_enqueue_style( 'colors' );
// Check callback name for 'media'
if ( ( is_array( $content_func ) && ! empty( $content_func[1] ) && 0 === strpos( (string) $content_func[1], 'media' ) )
	|| ( ! is_array( $content_func ) && 0 === strpos( $content_func, 'media' ) ) )
	wp_enqueue_style( 'media' );
wp_enqueue_style( 'ie' );
?>
<script type="text/javascript">
//<![CDATA[
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
var userSettings = {'url':'<?php echo SITECOOKIEPATH; ?>','uid':'<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>','time':'<?php echo time(); ?>'};
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>', pagenow = 'media-upload-popup', adminpage = 'media-upload-popup',
isRtl = <?php echo (int) is_rtl(); ?>;
//]]>
</script>
<?php
do_action('admin_enqueue_scripts', 'media-upload-popup');
do_action('admin_print_styles-media-upload-popup');
do_action('admin_print_styles');
do_action('admin_print_scripts-media-upload-popup');
do_action('admin_print_scripts');
do_action('admin_head-media-upload-popup');
do_action('admin_head');

if ( is_string($content_func) )
	do_action( "admin_head_{$content_func}" );
?>
</head>
<body<?php if ( isset($GLOBALS['body_id']) ) echo ' id="' . $GLOBALS['body_id'] . '"'; ?> class="no-js">
<script type="text/javascript">
//<![CDATA[
(function(){
var c = document.body.className;
c = c.replace(/no-js/, 'js');
document.body.className = c;
})();
//]]>
</script>
<?php
	//$args = func_get_args();
	//$args = array_slice($args, 1);
	$args = array('file', $errors, $id);
//	call_user_func_array($content_func, $args);
//function media_upload_type_form($type = 'file', $errors = null, $id = null) {
	media_upload_header();

	$post_id = isset( $_REQUEST['post_id'] )? intval( $_REQUEST['post_id'] ) : 0;

	$form_action_url = admin_url("media-upload.php?type=$type&tab=type&post_id=$post_id");
	$form_action_url = apply_filters('media_upload_form_url', $form_action_url, $type);
?>

<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr($form_action_url); ?>" class="media-upload-form type-form validate" id="<?php echo $type; ?>-form">
<?php submit_button( '', 'hidden', 'save', false ); ?>
<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id; ?>" />
<?php wp_nonce_field('media-form'); ?>

<h3 class="media-title"><?php _e('Add media files from your computer'); ?></h3>

<?php media_upload_form( $errors ); ?>

<script type="text/javascript">
//<![CDATA[
jQuery(function($){
	var preloaded = $(".media-item.preloaded");
	if ( preloaded.length > 0 ) {
		preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
	}
	updateMediaForm();
});
//]]>
</script>
<div id="media-items">
<?php
if ( $id ) {
	if ( !is_wp_error($id) ) {
		add_filter('attachment_fields_to_edit', 'media_post_single_attachment_fields_to_edit', 10, 2);
		echo get_media_items( $id, $errors );
	} else {
		echo '<div id="media-upload-error">'.esc_html($id->get_error_message()).'</div>';
		exit;
	}
}
?>
</div>
<p class="savebutton ml-submit">
<?php submit_button( __( 'Save all changes' ), 'button', 'save', false ); ?>
</p>
</form>
<?php
//}







	do_action('admin_print_footer_scripts');
?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>
<?php
/*EOF*/