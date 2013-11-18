<?php
include_once TEMPLATEPATH . '/functions/inkthemes-functions.php';
if ( !function_exists( 'optionsframework_init' ) ) {
/*-----------------------------------------------------------------------------------*/
/* Options Framework Theme
/*-----------------------------------------------------------------------------------*/
/* Set the file path based on whether the Options Framework Theme is a parent theme or child theme */
if ( STYLESHEETPATH == TEMPLATEPATH ) {
	define('OPTIONS_FRAMEWORK_URL', TEMPLATEPATH . '/functions/');
	define('OPTIONS_FRAMEWORK_DIRECTORY', get_template_directory_uri() . '/functions/');
} else {
	define('OPTIONS_FRAMEWORK_URL', STYLESHEETPATH . '/functions/');
	define('OPTIONS_FRAMEWORK_DIRECTORY', get_stylesheet_directory_uri() . '/functions/');
}
require_once (OPTIONS_FRAMEWORK_URL . 'options-framework.php');
}
/*-----------------------------------------------------------------------------------*/
/* Styles Enqueue */
/*-----------------------------------------------------------------------------------*/
function inkthemes_add_stylesheet() {
wp_enqueue_style( 'inkthemes_coloroptions', get_template_directory_uri() ."/css/green.css", '', '', 'all' );
wp_enqueue_style( 'inkthemes_zoombox', get_template_directory_uri() ."/css/zoombox.css", '', '', 'all' );
}
add_action('init', 'inkthemes_add_stylesheet');
/*-----------------------------------------------------------------------------------*/
/* jQuery Enqueue */
/*-----------------------------------------------------------------------------------*/
function inkthemes_wp_enqueue_scripts() {
if (!is_admin()) {
wp_deregister_script('jquery');
wp_register_script('jquery', 'http://code.jquery.com/jquery-latest.min.js', false, '1.6.1', true);//load jquery from google api, and place in footer
wp_enqueue_script('jquery');
wp_enqueue_script( 'inkthemes_ddsmoothmenu', get_stylesheet_directory_uri() ."/js/ddsmoothmenu.js", array('jquery'));
wp_enqueue_script( 'inkthemes_ddsmoothmenuinit', get_stylesheet_directory_uri()."/js/ddsmoothmenu-init.js", array('jquery'));
wp_enqueue_script( 'inkthemes_cufonyui', get_stylesheet_directory_uri().'/js/cufon-yui.js', array('jquery'));
wp_enqueue_script( 'inkthemes_cufonfont', get_stylesheet_directory_uri().'/js/Champagne.font.js', array('jquery'));
wp_enqueue_script( 'inkthemes_tipsy', get_stylesheet_directory_uri().'/js/jquery.tipsy.js', array('jquery')); 
wp_enqueue_script( 'inkthemes_imagehover', get_stylesheet_directory_uri()."/js/image.hover.js", array('jquery'));
wp_enqueue_script( 'inkthemes_zoombox', get_stylesheet_directory_uri().'/js/zoombox.js', array('jquery'));
wp_enqueue_script( 'inkthemes_validate', get_stylesheet_directory_uri().'/js/jquery.validate.min.js', array('jquery'));
wp_enqueue_script( 'inkthemes_verif', get_stylesheet_directory_uri().'/js/verif.js', array('jquery'));
}elseif (is_admin()){
}
}
add_action('init', 'inkthemes_wp_enqueue_scripts');


	if (function_exists('register_sidebar')) {

			register_sidebar(array(
			'name' => 'Mail Box',
			'id'   => 'mail_box',
			'description'   => 'Widget area for mail box',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4>',
			'after_title'   => '</h4>'
		));
}


?>