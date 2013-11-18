<?php
/*------------------------------------------------------------------------------
This controller grabs one or many post by the $_POST['post_id'], formats them
and returns them to the browser as the "preview".

NOTE: the default tpls used here are the _relation*.tpl's:
	_relation.tpl for single posts
	_relation_multi.tpl for fields where "is repeatable" has been selected
	
TODO: Should there be limits on what gets posted to this form because it does 
cough up post contents?  Is the Ajax nonce enough?
------------------------------------------------------------------------------*/
if (!defined('CCTM_PATH')) exit('No direct script access allowed');
if (!current_user_can('edit_posts')) exit('You do not have permission to do that.');

require_once(CCTM_PATH.'/includes/CCTM_FormElement.php');
require_once(CCTM_PATH.'/includes/SummarizePosts.php');
require_once(CCTM_PATH.'/includes/GetPostsQuery.php');

$d = array(); // <-- Template Variables

// Some Tests first to see if the request is valid...
$raw_fieldname = CCTM::get_value($_POST, 'fieldname');

// This could be empty if you're setting the default value of a new field definition
if (empty($raw_fieldname)) {
	print '<p>'.sprintf(__('Invalid fieldname: %s', CCTM_TXTDOMAIN), '<em>'. htmlspecialchars($raw_fieldname).'</em>') .'</p>';
	return;
}
$fieldname = preg_replace('/^'. CCTM_FormElement::css_id_prefix . '/', '', $raw_fieldname);

$def = CCTM::get_value(CCTM::$data['custom_field_defs'], $fieldname);
if (empty($def)) {
	print '<p>'.sprintf(__('Invalid fieldname: %s', CCTM_TXTDOMAIN), '<em>'. htmlspecialchars($fieldname).'</em>') .'</p>';
	return;
}

// Will be either the single or the multi, depending.
$tpl = '';

// Might be an array
$post_ids = CCTM::get_value($_POST,'post_id');
$guid = CCTM::get_value($_POST,'guid');

if (CCTM::get_value($def,'is_repeatable') && !is_array($post_ids)) {
	if (empty($post_ids)) {
		$post_ids = array();
	}	
	else {
		$post_ids = array($post_ids);
	}
}

if (empty($post_ids) && empty($guid)) {
	print '<p>'.__('Post ID or guid required.', CCTM_TXTDOMAIN).'</p>';
	return;	
}

// Multi
if (is_array($post_ids)) {
	// name should go to name[]
	$tpl = CCTM::load_tpl(
		array('fields/elements/'.$def['name'].'.tpl'
			, 'fields/elements/_'.$def['type'].'_multi.tpl'
			, 'fields/elements/_relation_multi.tpl'
		)
	);
}
// Single Post
else {
	$tpl = CCTM::load_tpl(
		array('fields/elements/'.$def['name'].'.tpl'
			, 'fields/elements/_'.$def['type'].'.tpl'
			, 'fields/elements/_relation.tpl'
		)
	);
}

// Just in case...
if (empty($tpl)) {
	print '<p>'.__('Formatting template not found!', CCTM_TXTDOMAIN).'</p>';
	return;	

}

//------------------------------------------------------------------------------
// Begin!
//------------------------------------------------------------------------------

$Q = new GetPostsQuery(); 
$Q->defaults = array(); // blank it out... everything is fair game.

// Handle WP 3.3 
if (!empty($guid)) {
	$args['guid'] = $guid;
}
// Normal behavior
else {
	$args['include'] = $post_ids;
}

$results = $Q->get_posts($args);

// Mostly just stuff from the full object record (the post and *all* custom fields),
// but we add a couple things in here for formatting purposes.
foreach($results as $r) {

	$r['thumbnail_url'] = CCTM::get_thumbnail($r['ID']);
	$r['id'] = $fieldname;
	$r['name'] = $fieldname;	
	$r['id_prefix'] = CCTM_FormElement::css_id_prefix;
	$r['name_prefix'] = CCTM_FormElement::post_name_prefix;

	// Translate stuff (issue 279)
	$r['post_title'] = __($r['post_title']);
	$r['post_content'] = __($r['post_content']);
	$r['post_excerpt'] = __($r['post_excerpt']);

	print CCTM::parse($tpl, $r);
}

/*EOF*/