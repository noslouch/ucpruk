<?php
if ( ! defined('CCTM_PATH')) exit('No direct script access allowed');
if (!current_user_can('administrator')) exit('Admins only.');
/*------------------------------------------------------------------------------
clear the cache (images, etc) inside of wp-uploads/cctm/cache
------------------------------------------------------------------------------*/

$data 				= array();
$data['page_title']	= __('Clear Cache', CCTM_TXTDOMAIN);
$data['help']		= 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Cache';
$data['menu'] 		= ''; 
$data['msg']		= CCTM::get_flash();
$data['action_name'] = 'custom_content_type_mgr_clear_cache';
$data['nonce_name'] = 'custom_content_type_mgr_clear_cache_nonce';
$data['submit']   = __('Clear Cache', CCTM_TXTDOMAIN);
$data['fields']   = '';
$data['cancel_target_url'] = '?page=cctm_tools';


$upload_dir = wp_upload_dir();			
$cache_dir = $upload_dir['basedir'].'/'.CCTM::base_storage_dir .'/cache';


// If properly submitted, Proceed with deleting the post type
if ( !empty($_POST) && check_admin_referer($data['action_name'], $data['nonce_name']) ) {
	$error_flag = false;

	CCTM::delete_dir($cache_dir);
	
	// Delete the cache dir
	if (!$error_flag) {

		$msg = '<div class="updated"><p>'
			. __('Cache has been cleared', CCTM_TXTDOMAIN)
			. '</p></div>';
		$data['msg'] = $msg;
		
		@mkdir($cache_dir);
	
	}
	else {
		$msg = '<div class="error"><p>'
			. __('Unable to clear the cache!  Please adjust the permissions on that directory or manually delete its contents.', CCTM_TXTDOMAIN)
			. '</p></div>';
		$data['msg'] = $msg;
	
	
	}
}

$data['content'] = '
	<div>
		<p>'.
		 sprintf( __('Clearing the cache will delete all files and directories from the CCTM cache directory: %s.', CCTM_TXTDOMAIN), "<code>$cache_dir</code>")
		.'</p>
	</div>';

$data['content'] = CCTM::load_view('basic_form.php', $data);
print CCTM::load_view('templates/default.php', $data);
/*EOF*/