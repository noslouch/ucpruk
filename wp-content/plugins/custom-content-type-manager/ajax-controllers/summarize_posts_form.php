<?php
/*------------------------------------------------------------------------------
Remember: the output here MUST be wrapped in HTML tags, otherwise jQuery's .html()
method will kack.
------------------------------------------------------------------------------*/
require_once(CCTM_PATH.'/includes/SummarizePosts.php');
require_once(CCTM_PATH.'/includes/GetPostsQuery.php');
require_once(CCTM_PATH.'/includes/GetPostsForm.php');

$Form = new GetPostsForm();

// What options should be displayed on the form that defines the search?  
// Load up the config...
$possible_configs = array();
$possible_configs[] = '/config/search_parameters/_summarize_posts.php';

if (!CCTM::load_file($possible_configs)) {
	print '<p>'.__('Search parameter configuration file not found.', CCTM_TXTDOMAIN) .'</p>';	
}

$form_tpl = CCTM::load_tpl('summarize_posts/search.tpl');

$Form->set_name_prefix('');
$Form->set_id_prefix('');

$Form->set_tpl($form_tpl);

$custom_fields = CCTM::get_custom_field_defs();
$custom_field_options = '';
foreach($custom_fields as $cf) {
	$custom_field_options .= sprintf('<option value="%s:%s">%s</option>', $cf['name'], $cf['label'], $cf['label']);
}
$Form->set_placeholder('custom_fields', $custom_field_options);
print $Form->generate(CCTM::$search_by);



//print '<pre>hey...</pre>';
/*EOF*/