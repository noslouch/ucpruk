<?php
//print '<pre>';
//print_r(get_declared_classes());
//print '</pre>';
//exit;
if (!defined('CCTM_PATH')) exit('No direct script access allowed');
if (!current_user_can('manage_options')) exit('Admins only.');
/*------------------------------------------------------------------------------
Independent controller that displays the contents of a CCTM definition file

Output is the HTML required to display and format the def file.
This needed to live in a separate file because I needed to completely control
the entire request: if it were handled by WP, headers() would be sent.
------------------------------------------------------------------------------*/
//@require_once( realpath('../../../../').'/wp-load.php' );
//include_once('../includes/constants.php');
//include_once(CCTM_PATH.'/includes/CCTM.php');
include_once(CCTM_PATH.'/includes/CCTM_ImportExport.php');

// Make sure a file was specified
$filename = CCTM::get_value($_REQUEST,'file');
if (empty($filename)) {
	print CCTM::format_error_msg( __('Definition file not specified.', CCTM_TXTDOMAIN));
	exit;
}

// Make sure the filename is legit
if (!CCTM_ImportExport::is_valid_basename($filename)) {
	CCTM::format_error_msg( __('Invalid filename: the definition filename should not contain spaces and should use an extension of <code>.cctm.json</code>.', CCTM_TXTDOMAIN));
	exit;
}

// Load up this thing... errors will be thrown
$upload_dir = wp_upload_dir();
$dir = $upload_dir['basedir'] .'/'.CCTM::base_storage_dir . '/' . CCTM::def_dir .'/';

$data = CCTM_ImportExport::load_def_file($dir.$filename);

// Bail if there were errors
if (!empty(CCTM::$errors)) {
	print CCTM::format_errors();
	exit;
}

$data['filename'] = $filename;

print CCTM::load_view('preview_def.php', $data);

/*EOF*/