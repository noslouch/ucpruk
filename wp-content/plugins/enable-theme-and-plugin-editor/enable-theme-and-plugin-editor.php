<?php
/*
Plugin Name: Enable Theme and Plugin Editor
Plugin URI: http://uplift.ru/projects/
Description: Allows to enable theme and plugin editor for site administrator in WordPress MU.
Version: 0.1
Author: Sergey Biryukov
Author URI: http://sergeybiryukov.ru/
*/

function etpe_add_menu() {
	global $submenu;
	$submenu['plugins.php'][] = array( __('Editor'), 'edit_plugins', 'plugin-editor.php' );
	$submenu['themes.php'][] = array( __('Editor'), 'edit_themes', 'theme-editor.php' );
}
add_action('_admin_menu', 'etpe_add_menu', 11);

function etpe_init() {
	if ( is_site_admin() ) {
		remove_action('admin_init', 'disable_some_pages');
	}
}
add_action('admin_init', 'etpe_init', 9);
?>