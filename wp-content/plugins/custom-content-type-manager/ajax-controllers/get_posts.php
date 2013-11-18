<?php
/*------------------------------------------------------------------------------
This controller displays a selection of posts for the user to select, i.e. 
the "Post Selector"

The thickbox appears (for example) when you create or edit a post that uses a relation,
image, or media field.
------------------------------------------------------------------------------*/
if (!defined('CCTM_PATH')) exit('No direct script access allowed');
if (!current_user_can('edit_posts')) die('You do not have permission to do that.');
require_once(CCTM_PATH.'/includes/CCTM_FormElement.php');
require_once(CCTM_PATH.'/includes/SummarizePosts.php');
require_once(CCTM_PATH.'/includes/GetPostsQuery.php');
require_once(CCTM_PATH.'/includes/GetPostsForm.php');


// Template Variables Initialization
$d = array(); 
$d['search_parameters'] = '';
$d['fieldname'] 		= '';
$d['menu']				= '';
$d['search_form']		= '';
$d['content']			= '';
$d['page_number']		= '0'; 
$d['orderby'] 			= 'ID';
$d['order'] 			= 'ASC';

$results_per_page = 10;




// Generate a search form
// we do this AFTER the get_posts() function so the form can access the GetPostsQuery->args/defaults
$Form = new GetPostsForm();


//$d['content'] = '<pre>'.print_r($_POST, true) . '</pre>';

//! Validation
// Some Tests first to see if the request is valid...
$raw_fieldname = CCTM::get_value($_POST, 'fieldname');
$fieldtype = CCTM::get_value($_POST, 'fieldtype');

if (empty($raw_fieldname) && empty($fieldtype)) {
	print '<pre>'.sprintf(__('Invalid fieldname: %s', CCTM_TXTDOMAIN), '<em>'. htmlspecialchars($raw_fieldname).'</em>') .'</pre>';
	return;
}
// More Template Variables
$d['fieldname'] = $raw_fieldname;

$fieldname = preg_replace('/^'. CCTM_FormElement::css_id_prefix . '/', '', $raw_fieldname);

$def = CCTM::get_value(CCTM::$data['custom_field_defs'], $fieldname);

if (!empty($fieldtype)) {
	$def['type'] = $fieldtype;
}
elseif (empty($def)) {
	print '<p>'.sprintf(__('Invalid fieldname: %s', CCTM_TXTDOMAIN), '<em>'. htmlspecialchars($fieldname).'</em>').'</p>';
	return;
}


// This gets subsequent search data that gets passed when the user refines the search.
$args = array();
if (isset($_POST['search_parameters'])) {


	// print '<pre> HERE...'. print_r($_POST['search_parameters'], true).'</pre>';
//	$d['content'] .= '<pre>HERE... '. print_r($_POST['search_parameters'], true).'</pre>';
	parse_str($_POST['search_parameters'], $args);

	// Pass the "view" parameters to the view
	$d['page_number'] = CCTM::get_value($args, 'page_number', 0);
	$d['orderby'] = CCTM::get_value($args, 'orderby', 'ID');
	$d['order'] = CCTM::get_value($args, 'order', 'ASC');
	
	// Unsest these, otherwise the query will try to search them as custom field values.
	unset($args['page_number']);
	unset($args['fieldname']);
	
}



// Set up search boundaries (i.e. the parameters used when nothing else is specified).
// Load up the config...
$possible_configs = array();
$possible_configs[] = '/config/post_selector/'.$fieldname.'.php'; 	// e.g. my_field.php
$possible_configs[] = '/config/post_selector/_'.$def['type'].'.php'; 		// e.g. _image.php

CCTM::$post_selector = array();
if (!CCTM::load_file($possible_configs)) {
	print '<p>'.__('Post Selector configuration file not found.', CCTM_TXTDOMAIN) .'</p>';	
}

// Set search boundaries (i.e. the parameters used when nothing is specified)
// !TODO: put this configuration stuff into the /config/ files

// optionally get pages to exclude
if (isset($_POST['exclude'])) {
	CCTM::$post_selector['exclude'] = $_POST['exclude'];
}

$search_parameters_str = ''; // <-- read custom search parameters, if defined.
if (isset($def['search_parameters'])) {
	$search_parameters_str = $def['search_parameters'];	
}
$additional_defaults = array();
parse_str($search_parameters_str, $additional_defaults);
//print '<pre>'.print_r($additional_defaults,true).'</pre>';
foreach($additional_defaults as $k => $v) {
	if (!empty($v)) {
		CCTM::$post_selector[$k] = $v;
	}
}


//------------------------------------------------------------------------------
// Begin!
//------------------------------------------------------------------------------
$Q = new GetPostsQuery(); 
$Q->set_defaults(CCTM::$post_selector);
	

$args['offset'] = 0; // assume 0, unless we got a page number
// Calculate offset based on page number
if (is_numeric($d['page_number']) && $d['page_number'] > 1) {
	$args['offset'] = ($d['page_number'] - 1) * $results_per_page;
}

// Get the results
$results = $Q->get_posts($args);

$search_form_tpl = CCTM::load_tpl(
	array('post_selector/search_forms/'.$fieldname.'.tpl'
		, 'post_selector/search_forms/_'.$def['type'].'.tpl'
		, 'post_selector/search_forms/_default.tpl'
	)
);

$Form->set_tpl($search_form_tpl);
$Form->set_name_prefix(''); // blank out the prefixes
$Form->set_id_prefix('');
$search_by = array('search_term','yearmonth','post_type'); 
$d['search_form'] = $Form->generate($search_by, $args);


$item_tpl = '';
$wrapper_tpl = '';

// Multi Field (contains an array of values.
if (isset($def['is_repeatable']) && $def['is_repeatable'] == 1) {

	$item_tpl = CCTM::load_tpl(
		array('post_selector/items/'.$fieldname.'.tpl'
			, 'post_selector/items/_'.$def['type'].'_multi.tpl'
			, 'post_selector/items/_default.tpl'
		)
	);
	$wrapper_tpl = CCTM::load_tpl(
		array('post_selector/wrappers/'.$fieldname.'.tpl'
			, 'post_selector/wrappers/_'.$def['type'].'_multi.tpl'
			, 'post_selector/wrappers/_default.tpl'
		)
	);
}
// Simple field (contains single value)
else {	
	$item_tpl = CCTM::load_tpl(
		array('post_selector/items/'.$fieldname.'.tpl'
			, 'post_selector/items/_'.$def['type'].'.tpl'
			, 'post_selector/items/_default.tpl'
		)
	);
	$wrapper_tpl = CCTM::load_tpl(
		array('post_selector/wrappers/'.$fieldname.'.tpl'
			, 'post_selector/wrappers/_'.$def['type'].'.tpl'
			, 'post_selector/wrappers/_default.tpl'
		)
	);
}


// Placeholders for the wrapper tpl
$hash = array();
$hash['post_title'] 	= __('Title', CCTM_TXTDOMAIN);
$hash['post_date'] 		= __('Date', CCTM_TXTDOMAIN);
$hash['post_status'] 	= __('Status', CCTM_TXTDOMAIN);
$hash['post_parent'] 	= __('Parent', CCTM_TXTDOMAIN);
$hash['post_type'] 		= __('Post Type', CCTM_TXTDOMAIN);
//$hash['filter'] 		= __('Filter', CCTM_TXTDOMAIN);
//$hash['show_all']		= __('Show All', CCTM_TXTDOMAIN);

$hash['content'] = '';

// And the items
//$results = array();
foreach ($results as $r){
	
	$r['name'] = $raw_fieldname;
	$r['preview'] = __('Preview', CCTM_TXTDOMAIN);
	$r['select'] = __('Select', CCTM_TXTDOMAIN);	
	$r['field_id'] = $raw_fieldname;
	$r['thumbnail_url'] = CCTM::get_thumbnail($r['ID']);
	// Translate stuff (issue 279)
	$r['post_title'] = __($r['post_title']);
	$r['post_content'] = __($r['post_content']);
	$r['post_excerpt'] = __($r['post_excerpt']);
	
	$hash['content'] .= CCTM::parse($item_tpl, $r);
}

// die(print_r($hash,true));
$d['content'] .= CCTM::parse($wrapper_tpl,$hash);

$d['content'] .= '<div class="cctm_pagination_links">'.$Q->get_pagination_links().'</div>';

if (isset($_POST['wrap_thickbox'])){
	print CCTM::load_view('templates/thickbox.php', $d);
}
else {
	//print CCTM::load_view('templates/thickbox_inner.php', $d);
	print $d['content'];
}

exit;
/*EOF*/