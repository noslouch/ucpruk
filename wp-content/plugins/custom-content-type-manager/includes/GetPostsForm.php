<?php
/**
 * This class generates search forms that act as a graphical interface to the 
 * GetPostsQuery->get_posts() function.
 * In other words, the data submitted from one of the generated forms describes
 * a search that can be performed by GetPostsQuery->get_posts().  This is not to
 * say that you NEED to use this class to front-end any queries performed by GetPostsQuery,
 * this just lets you generate such a form quickly and via configuration. This class
 * also dynamically creates form elements based on what's in the database (e.g. it 
 * lists Year/Months for filtering only if posts were created in those months).
 *
 * Requires: GetPostsQuery
 *
 * $args is an array of valid keys from the GetPostsQuery $defaults: each string
 * in the array defines a filter used by the GetPostsQuery::get_posts() function.
 * Including an item in the $args will cause the generate() function to generate
 * the HTML form elements to allow the user to control that filter on the search
 * form. I.e. the more $args supplied, the longer and more complex the search
 * form will be.
 *
 * Form element names will correspond exactly to the arguments accepted by the
 * get_posts() function so that this will work: GetPostsQuery::get_posts($_POST);
 *
 * @package SummarizePosts
 */


class GetPostsForm {

	// GetPostsQuery
	public $Q;
	
	/**
	 * The super simple default search form includes only a search term.
	 */
	public static $defaults = array(
		'search_term'
	);

	public static $small = array('search_term', 'match_rule', 'post_type', 'yearmonth');
	public static $medium = array();
	public static $large = array();
	
	// Used for text inputs
	public $text_tpl = '
		<div id="[+id+]_wrapper" class="[+wrapper_class+]">
			<label for="[+id_prefix+][+id+]" class="[+label_class+]" id="[+id+]_label">[+label+]</label>
			<span class="[+description_class+]" id="[+id+]_description">[+description+]</span>
			<input class="[+input_class+] input_field" type="text" name="[+name_prefix+][+id+]" id="[+id_prefix+][+id+]" value="[+value+]" />
			[+javascript_options+]
		</div>
		';

	// Used for checkbox inputs: wraps one or more $checkbox_tpl's
	public $checkbox_wrapper_tpl = '
		<div id="[+id+]_wrapper" class="[+wrapper_class+]">
			<span class="[+label_class+]" id="[+id+]_label">[+label+]</span>
			<span class="[+description_class+]" id="[+id+]_description">[+description+]</span>
			[+checkboxes+]
		</div>
		';

	public $checkbox_tpl = '
		<input type="checkbox" class="[+input_class+]" name="[+name_prefix+][+name+]" id="[+id_prefix+][+id+]" value="[+value+]" [+is_checked+]/> <label for="[+id_prefix+][+id+]" class="[+label_class+]" id="[+id+]_label">[+label+]</label>';

	// Used for radio input
	public $radio_tpl = '
		<input class="[+input_class+]" type="radio" name="[+name_prefix+][+name+]" id="[+id_prefix+][+id+]" value="[+value+]" [+is_checked+]/> <label class="[+label_class+]" id="[+id+]_label" for="[+id_prefix+][+id+]">[+label+]</label>';

	// dropdowns and multiselects
	public $select_wrapper_tpl = '
		<div id="[+id+]_wrapper" class="[+wrapper_class+]">
			<label for="[+id_prefix+][+id+]" class="[+label_class+]" id="[+id+]_label">[+label+]</label>
			<span class="[+description_class+]" id="[+id+]_description">[+description+]</span>
			<select size="[+size+]" name="[+name_prefix+][+name+]" class="[+input_class+]" id="[+id_prefix+][+id+]">
				[+options+]
			</select>
		</div>
		';

	// Options
	public $option_tpl = '<option value="[+value+]" [+is_selected+]>[+label+]</option>
	';

	/**
	 * Full form: contains all search elements. Some attributes only are useful
	 * when used programmatically.
	 */
	public static $full = array('limit', 'offset', 'orderby', 'order', 'include',
		'exclude', 'append', 'meta_key', 'meta_value', 'post_type', 'omit_post_type',
		'post_mime_type', 'post_parent', 'post_status', 'post_title', 'author', 'post_date',
		'post_modified', 'yearmonth', 'date_min', 'date_max', 'date_format', 'taxonomy',
		'taxonomy_term', 'taxonomy_slug', 'taxonomy_depth', 'search_term', 'search_columns',
		'join_rule', 'match_rule', 'date_column', 'paginate');


	// Set @ __construct so we can localize the "Search" button.
	public $form_tpl = '
		<style>
		[+css+]
		</style>
		<form method="[+method+]" action="[+action+]" class="[+form_name+]" id="[+form_name+][+form_number+]">
			[+nonce+]
			[+content+]
			<input type="submit" value="[+search+]" />
		</form>';


	/**
	 * Stores any errors encountered for debugging purposes.
	 */
	public $errors = array();



	public $nonce_field; // set @ __construct. Contains the whole field to be used.
	public $nonce_action = 'sp_search';
	public $nonce_name = 'sp_search';



	/**
	 * Contains the localized message displayed if no results are found. Set @ instantiation.
	 */
	public $no_results_msg;



	/**
	 * Ultimately passed to the parse function, this contains an associative
	 * array. The key is the name of the placeholder, the value is what it will
	 * get replaced with.
	 */
	public $placeholders = array(
		'name_prefix'    => 'gpf_',
		'id_prefix'     => 'gpf_',
		'wrapper_class'   => 'input_wrapper',
		'input_class'    => 'input_field',
		'label_class'  => 'input_title',
		'description_class' => 'input_description',
		'form_name'     => 'getpostsform',
		'form_number'    => '', // iterated on each instance of generate, even across objects
		'action'      => '',
		'method'      => 'post',
	);

	// Contains css stuff, populated at instantiation
	public $css;

	// Describes how we're going to search
	public $search_by = array();

	/**
	 * Values to populate the fields with
	 */
	public $values = array();

	/**
	 * Any valid key from GetPostsQuery (populated @ instantiation)
	 */
	private $valid_props = array();

	//------------------------------------------------------------------------------
	//! Magic Functions
	//------------------------------------------------------------------------------
	/**
	 * This function handles generation of generic textfields.  This occurs if the
	 * user wants to search a specific field for an exact value, e.g. 'post_excerpt'.
	 *
	 * @param string  $name the name of the field you want to search.
	 * @param unknown $args
	 * @return string html for this field element.
	 */
	public function __call($name, $args) {
		$ph = $this->placeholders;
		$ph['value'] = '';
		$ph['name'] = $name;
		$ph['id']  = $name;
		$ph['label'] = __($name, CCTM_TXTDOMAIN);
		$ph['description'] = sprintf(__('Retrieve posts with this exact %s.', CCTM_TXTDOMAIN), "<em>$name</em>");

		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * The inputs describe how you want to search: each element provided will trigger
	 * the generation of various form elements.
	 *
	 * @param array   $search_by (optional)
	 */
	public function __construct($search_by=array()) {
		$this->Q = new GetPostsQuery();
		
		// Default CSS stuff
		$dir = dirname(dirname(__FILE__));
		$this->set_css( $dir.'/css/searchform.css');

		$this->no_results_msg = '<p>'. __('Sorry, no results matched your search criteria.', CCTM_TXTDOMAIN) . '</p>';

		// some localization
		$this->placeholders['search'] 				= __('Search', CCTM_TXTDOMAIN);
		$this->placeholders['filter'] 				= __('Filter', CCTM_TXTDOMAIN);
		$this->placeholders['show_all'] 			= __('Show All', CCTM_TXTDOMAIN);
		$this->placeholders['show_all_dates'] 		= __('Show all dates', CCTM_TXTDOMAIN);
		$this->placeholders['show_all_post_types'] 	= __('Show all post-types', CCTM_TXTDOMAIN);

		$this->placeholders['label_class'] 			= 'input_title';
		$this->placeholders['wrapper_class'] 		= 'input_wrapper';
		$this->placeholders['description_class'] 	= 'input_description';
		$this->placeholders['input_class'] 			= 'input_field';

		$this->valid_props = array_keys($this->Q->defaults);
				
		if (empty($search_by)) {
			// push this through validation.
			//foreach(self::$defaults as $k => $v) {
			// $this->__set($k, $v);
			//}
			$this->search_by = self::$defaults;
		}
		else {
			$this->search_by = $search_by;
		}

		$this->nonce_field = wp_nonce_field($this->nonce_action, $this->nonce_name, true, false);

	}


	//------------------------------------------------------------------------------
	/**
	 * Interface with $this->search_by
	 *
	 * @param unknown $k
	 * @return unknown
	 */
	public function __get($k) {
		if ( in_array($k, $this->search_by) ) {
			return $this->search_by[$k];
		}
		else {
			return __('Invalid parameter:') . $k;
		}
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param string  $k for key
	 * @return boolean
	 */
	public function __isset($k) {
		return isset($this->search_by[$k]);
	}


	//------------------------------------------------------------------------------
	/**
	 * Interface with $this->search_by
	 *
	 * @param unknown $k
	 */
	public function __unset($k) {
		unset($this->search_by[$k]);
	}


	//------------------------------------------------------------------------------
	/**
	 * Validate and set parameters
	 * Interface with $this->search_by
	 *
	 * @param string  $k for key
	 * @param mixed   $v for value
	 */
	public function __set($k, $v) {
		if (in_array($k, $this->valid_props)) {
			$this->search_by[$k] = $v;
		}
		else {

		}
	}


	//------------------------------------------------------------------------------
	//! Private Functions (named after GetPostsQuery args)
	//------------------------------------------------------------------------------
	//------------------------------------------------------------------------------
	/**
	 * List which posts to append to search results.
	 *
	 * @return string
	 */
	private function _append() {
		$ph = $this->placeholders;
		$val = 
		$ph['value'] = $this->get_value('append');
		$ph['name'] = 'append';
		$ph['id']  = 'append';
		$ph['label'] = __('Append', CCTM_TXTDOMAIN);
		$ph['description'] = __('List posts by their ID that you wish to include on every search. Comma-separate multiple values.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'append');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Post author (display name)
	 *
	 * @return string
	 */
	private function _author() {
		$ph = $this->placeholders;
		$current_value = $this->get_value('author');
		global $wpdb;
		$authors = $wpdb->get_results("SELECT ID, display_name from {$wpdb->users} ORDER BY display_name");

		$ph['options'] = '';
		foreach ($authors as $a) {
			$ph['value'] = $a->display_name;
			$ph['label'] = $a->display_name .'('.$a->ID.')';
			if ($current_value == $a->display_name) {
				$ph['is_selected'] = ' selected="selected"';
			}		
			$ph['options'] .=  self::parse($this->option_tpl, $ph);
		}

		$ph['value'] = '';
		$ph['name'] = 'author';
		$ph['id']  = 'author';
		$ph['label'] = __('Author', CCTM_TXTDOMAIN);
		$ph['description'] = __('Select an author whose posts you want to see.', CCTM_TXTDOMAIN);
		$ph['size'] = 5;
		$this->register_global_placeholders($ph, 'author');
		return self::parse($this->select_wrapper_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * date_column: some js help, but the user can write in their own value for dates stored in custom fields (i.e. custom columns)
	 * post_date, post_date_gmt, post_modified, post_modified_gmt
	 *
	 * @return string
	 */
	private function _date_column() {
		$ph = $this->placeholders;

		$ph['value'] = $this->get_value('date_column','post_modified');
		$ph['name'] = 'date_column';
		$ph['id']  = 'date_column';
		$ph['label'] = __('Date Columns', CCTM_TXTDOMAIN);
		$ph['description'] = __('Which column should be used for date comparisons? Select one, or write in a custom field.', CCTM_TXTDOMAIN);
		$ph['javascript_options'] = '
			<div class="js_button_wrapper">
				<span class="js_button" onclick="jQuery(\'#'.$this->placeholders['id_prefix'].'date_column\').val(\'post_date\');">post_date</span><br/>
				<span class="js_button" onclick="jQuery(\'#'.$this->placeholders['id_prefix'].'date_column\').val(\'post_date_gmt\');">post_date_gmt</span><br/>
				<span class="js_button" onclick="jQuery(\'#'.$this->placeholders['id_prefix'].'date_column\').val(\'post_modified\');">post_modified</span><br/>
				<span class="js_button" onclick="jQuery(\'#'.$this->placeholders['id_prefix'].'date_column\').val(\'post_modified_gmt\');">post_modified_gmt</span><br/>
			</div>';
		$this->register_global_placeholders($ph, 'date_column');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Date format: some js help, but the user can write in their own value.
	 *
	 * @return string
	 */
	private function _date_format() {
		$ph = $this->placeholders;

		$ph['value'] = $this->get_value('date_format', 'yyyy-mm-dd');
		$ph['name'] = 'date_format';
		$ph['id']  = 'date_format';
		$ph['label'] = __('Date Format', CCTM_TXTDOMAIN);
		$ph['description'] = __('How do you want the dates in the results formatted? Use one of the shortcuts, or supply a use any value valid for the <a href="http://php.net/manual/en/function.date-format.php">date_format()</a>', CCTM_TXTDOMAIN);

		$ph['javascript_options'] = '
			<span class="button" onclick="jQuery(\'#'.$ph['id_prefix'].'date_format\').val(\'mm/dd/yy\');">mm/dd/yy</span>
			<span class="button" onclick="jQuery(\'#'.$ph['id_prefix'].'date_format\').val(\'yyyy-mm-dd\');">yyyy-mm-dd</span>
			<span class="button" onclick="jQuery(\'#'.$ph['id_prefix'].'date_format\').val(\'yy-mm-dd\');">yy-mm-dd</span>
			<span class="button" onclick="jQuery(\'#'.$ph['id_prefix'].'date_format\').val(\'d M, y\');">d M, y</span>
			<span class="button" onclick="jQuery(\'#'.$ph['id_prefix'].'date_format\').val(\'d MM, y\');">d MM, y</span>
			<span class="button" onclick="jQuery(\'#'.$ph['id_prefix'].'date_format\').val(\'DD, d MM, yy\');">DD, d MM, yy</span>';
		$this->register_global_placeholders($ph, 'date_format');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * date_max
	 *
	 * @return string
	 */
	private function _date_max() {
		$ph = $this->placeholders;

		$ph['value'] = $this->get_value('date_max');
		$ph['name'] = 'date_max';
		$ph['id']  = 'date_max';
		$ph['label'] = __('Date Maximum', CCTM_TXTDOMAIN);
		$ph['description'] = __('Only results from this date or before will be returned', CCTM_TXTDOMAIN);

		$ph['javascript_options'] = '
	    	<script>
				jQuery(function() {
					jQuery("#'.$ph['id_prefix'].'date_max").datepicker({
						dateFormat : "yy-mm-dd"
					});
				});
			</script>';
		$this->register_global_placeholders($ph, 'date_max');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * date_min
	 *
	 * @return string
	 */
	private function _date_min() {
		$ph = $this->placeholders;

		$ph['value'] = $this->get_value('date_min');
		$ph['name'] = 'date_min';
		$ph['id']  = 'date_min';
		$ph['label'] = __('Date Minimum', CCTM_TXTDOMAIN);
		$ph['description'] = __('Only results from this date or after will be returned.', CCTM_TXTDOMAIN);

		$ph['javascript_options'] = '
	    	<script>
				jQuery(function() {
					jQuery("#'.$ph['id_prefix'].'date_min").datepicker({
						dateFormat : "yy-mm-dd"
					});
				});
			</script>';
		$this->register_global_placeholders($ph, 'date_min');
		return self::parse($this->text_tpl, $ph);

	}


	//------------------------------------------------------------------------------
	/**
	 * Lists which posts to exclude
	 *
	 * @return string
	 */
	private function _exclude() {
		$ph = $this->placeholders;
		$ph['value'] = $this->get_value('exclude');
		$ph['name'] = 'exclude';
		$ph['id']  = 'exclude';
		$ph['label'] = __('Exclude', CCTM_TXTDOMAIN);
		$ph['description'] = __('List posts by their ID that you wish to exclude from search results. Comma-separate multiple values.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'exclude');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * List which posts to include
	 *
	 * @return string
	 */
	private function _include() {
		$ph = $this->placeholders;
		$ph['value'] = $this->get_value('include');
		$ph['name'] = 'include';
		$ph['id']  = 'include';
		$ph['label'] = __('Include', CCTM_TXTDOMAIN);
		$ph['description'] = __('List posts by their ID that you wish to return.  Usually this option is not used with any other search options. Comma-separate multiple values.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'include');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Limits the number of posts returned OR sets the number of posts per page
	 * if pagination is on.
	 *
	 * @return string
	 */
	private function _limit() {
		$ph = $this->placeholders;

		$ph['value'] = (int) $this->get_value('limit');
		$ph['name'] = 'limit';
		$ph['id']  = 'limit';
		$ph['label'] = __('Limit', CCTM_TXTDOMAIN);
		$ph['description'] = __('Limit the number of results returned. If pagination is enabled, this number will be the number of results shown per page.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'limit');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * match_rule
	 *
	 * @return string
	 */
	private function _match_rule() {
		$ph = $this->placeholders;

		$ph['value'] = $this->get_value('match_rule');
		$ph['name'] = 'match_rule';
		$ph['id']  = 'match_rule';
		$ph['label'] = __('Match Rule', CCTM_TXTDOMAIN);
		$ph['description'] = __('Define how your search term should match.', CCTM_TXTDOMAIN);
		$ph['size'] = 1;

		$match_rules = array(
			'contains'   => __('Contains', CCTM_TXTDOMAIN),
			'starts_with'  => __('Starts with', CCTM_TXTDOMAIN),
			'ends_with'  => __('Ends with', CCTM_TXTDOMAIN),
		);
		$ph['options'] = '';
		foreach ($match_rules as $value => $label) {
			$ph2['value'] = $value;
			$ph2['label'] = $label;
			$ph['options'] .=  self::parse($this->option_tpl, $ph2);
		}
		$this->register_global_placeholders($ph, 'match_rule');
		return self::parse($this->select_wrapper_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Meta key is the name of a custom field from wp_postmeta: should be used with meta_value
	 *
	 * @return string
	 */
	private function _meta_key() {
		$ph = $this->placeholders;
		$ph['value'] = htmlspecialchars($this->get_value('meta_key'));
		$ph['name'] = 'meta_key';
		$ph['id']  = 'meta_key';
		$ph['label'] = __('Meta Key', CCTM_TXTDOMAIN);
		$ph['description'] = __('Name of a custom field, to be used in conjuncture with <em>meta_value</em>.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'meta_key');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Meta key is the name of a custom field from wp_postmeta: should be used with meta_value
	 *
	 * @return string
	 */
	private function _meta_value() {
		$ph = $this->placeholders;
		$ph['value'] = htmlspecialchars($this->get_value('meta_value'));
		$ph['name'] = 'meta_value';
		$ph['id']  = 'meta_value';
		$ph['label'] = __('Meta Value', CCTM_TXTDOMAIN);
		$ph['description'] = __('Value of a custom field, to be used in conjuncture with <em>meta_key</em>.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'meta_value');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Offset
	 *
	 * @return string
	 */
	private function _offset() {
		$ph = $this->placeholders;
		$ph['value'] = (int) $this->get_value('offset');
		$ph['name'] = 'offset';
		$ph['id']  = 'offset';
		$ph['label'] = __('Offset', CCTM_TXTDOMAIN);
		$ph['description'] = __('Number of results to skip.  Usually this is used only programmatically when pagination is enabled.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'offset');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Lets the user select a valid post_type
	 *
	 * @return string
	 */
	private function _omit_post_type() {
		$ph = $this->placeholders;

		$ph['label'] = __('Omit Post Types', CCTM_TXTDOMAIN);
		$ph['id']  = 'omit_post_type';
		$ph['value'] = (array) $this->get_value('omit_post_type');
		$ph['name'] = 'omit_post_type[]';
		$ph['description'] = __('Check which post-types you wish to omit from search results.', CCTM_TXTDOMAIN);

		$i = 0;
		$ph['checkboxes'] = '';
		$post_types = get_post_types();
		foreach ($post_types as $k => $pt) {
			$ph2 = $this->placeholders;
			$ph2['value'] = $k;
			$ph2['name'] = 'omit_post_type[]';
			$ph2['label'] = $pt;
			$ph2['input_class'] = 'input_checkbox';
			$ph2['label_class'] = 'label_checkbox';
			$ph2['id'] = 'omit_post_type' . $i;
			$ph['checkboxes'] .= self::parse($this->checkbox_tpl, $ph2);
			$i++;
		}
		$this->register_global_placeholders($ph, 'omit_post_type');
		return self::parse($this->checkbox_wrapper_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Order of results: ascending, descending
	 *
	 * @return string
	 */
	private function _order() {
		$ph = $this->placeholders;

		$current_value = $this->get_value('order');

		$ph['name'] = 'order';
		$ph['id']  = 'order';
		$ph['label'] = __('Order', CCTM_TXTDOMAIN);
		$ph['description'] = __('What order should search results be returned in? See also the <em>orderby</em> parameter.', CCTM_TXTDOMAIN);
		$ph['checkboxes'] = '';

		$ph2 = $this->placeholders;
		if ($current_value == 'ASC') {
			$ph2['is_checked'] = ' checked="checked"';
			$ph2['is_selected'] = ' selected="selected"';
			$this->placeholders['order.ASC.is_checked'] = ' checked="checked"';
			$this->placeholders['order.ASC.is_selected'] = ' selected="selected"';
		}
		$ph2['value'] = 'ASC';
		$ph2['label'] = __('Ascending', CCTM_TXTDOMAIN);
		$ph2['id'] = 'order_asc';
		$ph2['name'] = 'order';
		$ph2['input_class'] = 'input_radio';
		$ph2['label_class'] = 'label_radio';
		$ph['checkboxes'] .= self::parse($this->radio_tpl, $ph2);

		$ph3 = $this->placeholders;
		if ($current_value == 'DESC') {
			$ph3['is_checked'] = ' checked="checked"';
			$ph3['is_selected'] = ' selected="selected"';
			$this->placeholders['order.DESC.is_checked'] = ' checked="checked"';
			$this->placeholders['order.DESC.is_selected'] = ' selected="selected"';
		}
		$ph3['value'] = 'DESC';
		$ph3['label'] = __('Descending', CCTM_TXTDOMAIN);
		$ph3['id'] = 'order_desc';
		$ph3['name'] = 'order';
		$ph['checkboxes'] .= self::parse($this->radio_tpl, $ph3);

		$this->register_global_placeholders($ph, 'order');
		return self::parse($this->checkbox_wrapper_tpl, $ph);

	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @return string
	 */
	private function _orderby() {
		$ph = $this->placeholders;
		$ph['value'] = $this->get_value('orderby');
		$ph['name'] = 'orderby';
		$ph['id']  = 'orderby';
		$ph['label'] = __('Order By', CCTM_TXTDOMAIN);
		$ph['description'] = __('Which column should results be sorted by. Default: ID', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'orderby');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Enable pagination?
	 *
	 * @return string
	 */
	private function _paginate() {
		$ph = $this->placeholders;

		$ph['value'] = $this->get_value('paginate');
		$ph['name'] = 'paginate';
		$ph['id']  = 'paginate';
		$ph['label'] = __('Paginate Results', CCTM_TXTDOMAIN);
		$ph['description'] = 'Check this to paginate long result sets.'; // __('.', CCTM_TXTDOMAIN);
		$ph['label_class'] = 'label_checkbox';
		$ph['checkboxes'] = self::parse($this->checkbox_tpl, $ph);
		$this->register_global_placeholders($ph, 'paginate');
		$ph['label'] = __('Pagination', CCTM_TXTDOMAIN);
		$ph['label_class'] = 'input_title';
		return self::parse($this->checkbox_wrapper_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * post_date
	 *
	 * @return string
	 */
	private function _post_date() {
		$ph = $this->placeholders;
		$ph['value'] = $this->get_value('post_date');
		$ph['name'] = 'post_date';
		$ph['id']  = 'post_date';
		$ph['label'] = __('Post Date', CCTM_TXTDOMAIN);
		$ph['description'] = __('Find posts from this date.  Use the <em>date_column</em> parameter to determine which column should be considered.', CCTM_TXTDOMAIN);

		$ph['javascript_options'] = '
	    	<script>
				jQuery(function() {
					jQuery("#'.$ph['id_prefix'].'post_date").datepicker({
						dateFormat : "yy-mm-dd"
					});
				});
			</script>';
		$this->register_global_placeholders($ph, 'post_date');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * post_mime_type
	 *
	 * @return string
	 */
	private function _post_mime_type() {
		$ph = $this->placeholders;
		$ph['value'] = $this->get_value('post_mime_type');
		$ph['name'] = 'post_mime_type';
		$ph['id']  = 'post_mime_type';
		$ph['label'] = __('Post MIME Type', CCTM_TXTDOMAIN);
		$ph['description'] = __('Specify either the full MIME type (e.g. image/jpeg) or just the beginning (e.g. application, image, audio, video).', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'post_mime_type');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * post_modified
	 *
	 * @return string
	 */
	private function _post_modified() {
		$ph = $this->placeholders;
		$ph['value'] = $this->get_value('post_modified');
		$ph['name'] = 'post_modified';
		$ph['id']  = 'post_modified';
		$ph['label'] = __('Post Modified', CCTM_TXTDOMAIN);
		$ph['description'] = __('Find posts modified on this date.', CCTM_TXTDOMAIN);

		$ph['javascript_options'] = '
	    	<script>
				jQuery(function() {
					jQuery("#'.$ph['id_prefix'].'post_modified").datepicker({
						dateFormat : "yy-mm-dd"
					});
				});
			</script>';
		$this->register_global_placeholders($ph, 'post_modified');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * post_parent
	 *
	 * @return string
	 */
	private function _post_parent() {
		$ph = $this->placeholders;
		$val = $this->get_value('post_parent');
		if (!empty($val) && is_array($val)) {
			$ph['value'] = implode(',',$val);
		}		
		$ph['name'] = 'post_parent';
		$ph['id']  = 'post_parent';
		$ph['label'] = __('Post Parent', CCTM_TXTDOMAIN);
		$ph['description'] = __('Retrieve all posts that are children of the post ID(s) specified. Comma-separate multiple values.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'post_parent');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * post_status
	 *
	 * @return string
	 */
	private function _post_status() {
		$ph = $this->placeholders;
		$ph['value'] = (array) $this->get_value('post_status', array());

		$ph['name'] = 'post_status';
		$ph['id']  = 'post_status';
		$ph['label'] = __('Post Status', CCTM_TXTDOMAIN);
		$ph['description'] = __('Most searches will be for published posts.', CCTM_TXTDOMAIN);

		$i = 0;
		$ph['checkboxes'] = '';
		$post_statuses = array('draft', 'inherit', 'publish', 'auto-draft');

		foreach ($post_statuses as $ps) {
			$ph2 = $this->placeholders;
			$ph2['name'] = 'post_status[]';
			$ph2['is_checked'] = '';
			if (in_array($ps, $ph['value'])) {
				$ph2['is_checked'] = ' checked="checked"';
			}	
			$ph2['value'] = $ps;
			$ph2['label'] = $ps;
			$ph2['input_class'] = 'input_checkbox';
			$ph2['label_class'] = 'label_checkbox';
			$ph2['id'] = 'post_status' . $i;
			$ph['checkboxes'] .= self::parse($this->checkbox_tpl, $ph2);
			$i++;
		}
		$this->register_global_placeholders($ph, 'post_status');
		return self::parse($this->checkbox_wrapper_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * post_title
	 *
	 * @return string
	 */
	private function _post_title() {
		$ph = $this->placeholders;
		$ph['value'] = htmlspecialchars($this->get_value('post_title'));
		$ph['name'] = 'post_title';
		$ph['id']  = 'post_title';
		$ph['label'] = __('Post Title', CCTM_TXTDOMAIN);
		$ph['description'] = __('Retrieve posts with this exact title.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'post_title');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Lets the user select a valid post_type
	 *
	 * @return string
	 */
	private function _post_type() {
		$ph = $this->placeholders;
		$current_value = (array) $this->get_value('post_type');
		
		$ph['label'] = __('Post Types', CCTM_TXTDOMAIN);
		$ph['id']  = 'post_type';
		$ph['value'] = $this->get_value('post_type');
		$ph['name'] = 'post_type[]';
		$ph['description'] = __('Check which post-types you wish to search.', CCTM_TXTDOMAIN);

		$i = 0;
		$ph['checkboxes'] = '';
		$ph['options'] = '';
		// put a blank option before all the rest
		$ph2['name'] = 'post_type[]';
		$ph2['input_class'] = 'input_checkbox';
		$ph2['label_class'] = 'label_checkbox';
		$ph2['label'] = __('Select post-type', CCTM_TXTDOMAIN);
//		$ph['checkboxes'] .= self::parse($this->checkbox_tpl, $ph2);
		$ph['options'] .= self::parse($this->option_tpl, $ph2);
		

		$post_types = '';
		if (isset($this->Q->defaults['post_type'])) {
			$post_types = $this->Q->defaults['post_type'];
		}
		if(empty($post_types)) {
			$post_types = get_post_types(array('public'=>true));
		}
		
		sort($post_types);
		foreach ($post_types as $pt) {
			$ph2 = $this->placeholders;
			$ph2['name'] = 'post_type[]';
			if (in_array($pt, $current_value)) {
				$ph2['is_selected'] = ' selected="selected"';
				$ph2['is_checked'] = ' checked="checked"';
				$this->placeholders['post_type.'.$pt.'.is_selected'] = ' selected="selected"';
				$this->placeholders['post_type.'.$pt.'.is_checked'] = ' checked="checked"';
			}
			$ph2['value'] = $pt;
			$ph2['label'] = $pt;
			$ph2['input_class'] = 'input_checkbox';
			$ph2['label_class'] = 'label_checkbox';
			$ph2['id'] = 'post_type' . $i;
			$ph['checkboxes'] .= self::parse($this->checkbox_tpl, $ph2);
			$ph['options'] .= self::parse($this->option_tpl, $ph2);
			$i++;
		}
		$this->register_global_placeholders($ph, 'post_type');
		return self::parse($this->checkbox_wrapper_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Which columns to search
	 *
	 * @return string
	 */
	private function _search_columns() {
		$ph = $this->placeholders;
		$ph['value'] = (array) $this->get_value('search_columns');
		$ph['name'] = 'search_columns';
		$ph['id']  = 'search_columns';
		$ph['label'] = __('Search Columns', CCTM_TXTDOMAIN);
		$ph['description'] = __('When searching by a <em>search_term</em>, which define columns should be searched. Comma-separate multiple values. You can specify custom-fields as column names.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'search_columns');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * Generates simple search term box.
	 *
	 * @return string
	 */
	private function _search_term() {
		$ph = $this->placeholders;
		$ph['value'] = htmlspecialchars($this->get_value('search_term'));
		$ph['name'] = 'search_term';
		$ph['id']  = 'search_term';
		$ph['label'] = __('Search Term', CCTM_TXTDOMAIN);
		$ph['description'] = __('Search posts for this term. Use the <em>search_columns</em> parameter to specify which columns are searched for the term.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'search_term');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * taxonomy
	 *
	 * @return string
	 */
	private function _taxonomy() {
		$ph = $this->placeholders;
		$current_value = $this->get_value('taxonomy');
		$ph['options'] = '';
		// put a blank option before all the rest
		$ph2['name'] = 'taxonomy';
		$ph2['label'] = __('Select taxonomy', CCTM_TXTDOMAIN);
		$ph['options'] .= self::parse($this->option_tpl, $ph2);		
		
		$taxonomies = get_taxonomies();
		foreach ($taxonomies as $t) {
			$ph2 = $this->placeholders;
			$ph2['value'] = $t;
			$ph2['label'] = $t;
			if ($current_value == $t) {
				$ph2['is_selected'] = ' selected="selected"';
			}		
			$ph['options'] .=  self::parse($this->option_tpl, $ph2);
		}

		$ph['value'] = $current_value;
		$ph['name'] = 'taxonomy';
		$ph['id']  = 'taxonomy';
		$ph['label'] = __('Taxonomy', CCTM_TXTDOMAIN);
		$ph['description'] = __('Choose which taxonomy to search in. Used in conjunction with <em>taxonomy_term</em>.', CCTM_TXTDOMAIN);
		$ph['size'] = 1;
		$this->register_global_placeholders($ph, 'search_taxonomy');
		return self::parse($this->select_wrapper_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * How deep to search the taxonomy
	 *
	 * @return string
	 */
	private function _taxonomy_depth() {
		$ph = $this->placeholders;

		$ph['value'] = $this->get_value('taxonomy_depth');
		$ph['name'] = 'taxonomy_depth';
		$ph['id']  = 'taxonomy_depth';
		$ph['label'] = __('Taxonomy Depth', CCTM_TXTDOMAIN);
		$ph['description'] = __('When doing a hierarchical taxonomical search (e.g. by sub-categories), increase this number to reflect how many levels down the hierarchical tree should be searched. For example, 1 = return posts classified with the given taxonomical term (e.g. mammals), 2 = return posts classified with the given term or with the sub-taxonomies (e.g. mammals or dogs). (default: 1).', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'taxonomy_depth');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * taxonomy_slug
	 *
	 * @return string
	 */
	private function _taxonomy_slug() {
		$ph = $this->placeholders;

		$ph['value'] = $this->get_value('taxonomy_slug');
		$ph['name'] = 'taxonomy_slug';
		$ph['id']  = 'taxonomy_slug';
		$ph['label'] = __('Taxonomy Slug', CCTM_TXTDOMAIN);
		$ph['description'] = __('The taxonomy slug is the URL-friendly taxonomy term.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'taxonomy_slug');
		return self::parse($this->text_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	/**
	 * taxonomy_term
	 *
	 * @return string
	 */
	private function _taxonomy_term() {
		$ph = $this->placeholders;
		// print '<pre>'.print_r($this->get_value('taxonomy_term'), true).'</pre>';
		$val = $this->get_value('taxonomy_term');
		if (!empty($val) && is_array($val)) {
			$ph['value'] = implode(',',$val);
		}
		$ph['name'] = 'taxonomy_term';
		$ph['id']  = 'taxonomy_term';
		$ph['label'] = __('Taxonomy Term', CCTM_TXTDOMAIN);
		$ph['description'] = __('Set a specific category(ies) or tag(s) to include in search results. Comma-separate multiple values.', CCTM_TXTDOMAIN);
		$this->register_global_placeholders($ph, 'taxonomy_term');
		return self::parse($this->text_tpl, $ph);

	}


	//------------------------------------------------------------------------------
	/**
	 * yearmonth: uses the date-column
	 *
	 * @return string
	 */
	private function _yearmonth() {
		$ph = $this->placeholders;
		$current_value = $this->get_value('yearmonth');
		
		$ph['options'] = '';
		global $wpdb;
		// if date_column is part of wp_posts: //!TODO
		$yearmonths = $wpdb->get_results("SELECT DISTINCT DATE_FORMAT(post_date,'%Y%m') as 'yearmonth'
			, DATE_FORMAT(post_date,'%M') as 'month'
			, YEAR(post_date) as 'year'
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			ORDER BY yearmonth");
		foreach ($yearmonths as $ym) {
			$ph2 = $this->placeholders;
			$ph2['value'] = $ym->yearmonth;
			$ph2['label'] = $ym->year . ' ' . $ym->month;
			if ($current_value == $ym->yearmonth) {
				$ph2['is_selected'] = ' selected="selected"';
				$this->placeholders['yearmonth.'.$ym->yearmonth.'.is_selected'] = ' selected="selected"';
				$this->placeholders['yearmonth.'.$ym->yearmonth.'.is_checked'] = ' checked="checked"';
			}
			$ph['options'] .=  self::parse($this->option_tpl, $ph2);
		}

		$ph['value'] = $current_value;
		$ph['name'] = 'yearmonth';
		$ph['id']  = 'yearmonth';
		$ph['label'] = __('Month', CCTM_TXTDOMAIN);
		$ph['description'] = __("Choose which month's posts you wish to view. This relies on the <em>date_column</em> parameter.", CCTM_TXTDOMAIN);
		$ph['size'] = 1;
		$this->register_global_placeholders($ph, 'yearmonth');
		return self::parse($this->select_wrapper_tpl, $ph);
	}


	//------------------------------------------------------------------------------
	//! Public Functions
	//------------------------------------------------------------------------------
	/**
	 * Format any errors in an unordered list, or returns a message saying there were no errors.
	 *
	 * @return string
	 */
	public function get_errors() {

		if (!empty($this->errors)) {
			$output = '';
			$items = '';
			foreach ($this->errors as $id => $e) {
				$items .= '<li>'.$e.'</li>' ."\n";
			}
			$output = '<ul>'."\n".$items.'</ul>'."\n";
			return $output;
		}
		else {
			return __('There were no errors.');
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Generate a form.  This is the main event.
	 *
	 * @param array (optional) $search_by specify which parameters you want to search by
	 * @param array	(optional) $existing_values to populate the form e.g. from $_POST.
	 * @return string HTML form.
	 */
	public function generate($search_by=array(), $existing_values=array()) {
		
		foreach($existing_values as $k => $v) {
			$this->Q->$k = $v;
			//$this->values[$k] = $this->Q->$k;
		}
		$this->values = $this->Q->args;
		//print '<pre>'; print_r($this->values); print '</pre>'; 
		static $instantiation_count = 0; // used to generate a unique CSS for every form on the page
		$instantiation_count++;
		$this->placeholders['form_number'] = $instantiation_count;
		$this->placeholders['css'] = $this->get_css();

		// Defaults
		if (!empty($search_by)) {
			// override
			$this->search_by = $search_by;
		}

		$output = '';
		$this->placeholders['content'] = '';
		// Each part of the form is generated by component functions that correspond
		// exactly to the $search_by arguments.
		foreach ($this->search_by as $p) {
			$function_name = '_'.$p;
			if (method_exists($this, $function_name)) {
				$this->placeholders[$p] = $this->$function_name();
				// Keep the main 'content' bit populated: the content is the summ total of all generated elements.
				$this->placeholders['content'] .= $this->placeholders[$p];
			}
			else {
				$this->placeholders[$p] = $this->__call($p);
				// Keep the main 'content' bit populated.
				$this->placeholders['content'] .= $this->placeholders[$p];
				$this->errors['invalid_searchby_parameter'] = sprintf( __('Possible invalid search_by parameter:'), "<em>$p</em>");
			}
		}

		// Get help
		$all_placeholders = array_keys($this->placeholders);
		foreach ($all_placeholders as &$ph) {
			$ph = "&#91;+$ph+&#93;";
		}
		$this->placeholders['nonce'] = $this->get_nonce_field();
		$this->placeholders['help'] = implode(', ', $all_placeholders);

		return $this->parse($this->form_tpl, $this->placeholders);
	}


	//------------------------------------------------------------------------------
	/**
	 * Get the CSS to be used with this form.
	 *
	 * @return string
	 */
	public function get_css() {
		return $this->css;
	}


	//------------------------------------------------------------------------------
	/**
	 * Retrieves the "No Results" message.
	 *
	 * @return string
	 */
	public function get_no_results_msg() {
		return $this->no_results_msg;
	}


	//------------------------------------------------------------------------------
	/**
	 * Retrieves a nonce field (set @ __construct or overriden via set_nonce)
	 *
	 * @return string
	 */
	public function get_nonce_field() {
		return $this->nonce_field;
	}

	//------------------------------------------------------------------------------
	/**
	 * Get a value -- these should be filtered via GetPostsQuery::sanitize_args()
	 *
	 * @param string $key     the key to search for in the $this->values array
	 * @param mixed (optional) $default value to return if the value is not set.
	 * @return mixed
	 */
	public function get_value($key, $default='') {
		if ( !isset($this->values[$key]) ) {
			return $default;
		}
		else {
			return $this->values[$key];
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Ensure a valid date. 0000-00-00 qualifies as valid; if you need to ensure a REAL
	 * date (i.e. where '0000-00-00' is not allowed), then simply marking the field required
	 * won't work because the string '0000-00-00' is not empty.  
	 *
	 * @param string  $date to be checked
	 * @return boolean whether or not the input is a valid date
	 */
	public function is_date($date) {
		if (empty($date)) {
			return false;
		}
		list( $y, $m, $d ) = explode('-', $date );

		if ( is_numeric($m) && is_numeric($d) && is_numeric($y) && checkdate( $m, $d, $y ) ) {
			return true;
		}
		else {
			return false;
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * SYNOPSIS: a simple parsing function for basic templating.
	 *
	 * @param boolean if true, will not remove unused [+placeholders+]
	 *
	 * with the values and the string will be returned.
	 * @param string  $tpl:                         a string containing [+placeholders+]
	 * @param array   $hash:                        an associative array('key' => 'value');
	 * @param boolean $preserve_unused_placeholders (optional)
	 * @return string placeholders corresponding to the keys of the hash will be replaced
	 */
	public static function parse($tpl, $hash, $preserve_unused_placeholders=false) {

		foreach ($hash as $key => $value) {
			if ( !is_array($value) ) {
				$tpl = str_replace('[+'.$key.'+]', $value, $tpl);
			}
		}

		// Remove any unparsed [+placeholders+]
		if (!$preserve_unused_placeholders) {
			$tpl = preg_replace('/\[\+(.*?)\+\]/', '', $tpl);
		}
		return $tpl;
	}

	//------------------------------------------------------------------------------
	/**
	 * This assists us in making custom formatting templates as flexible as possible.
	 *
	 * @return none this populates keys in $this->placeholders
	 * @param array   $array     contains key/value pairs corresponding to placeholder => replacement-values
	 * @param string  $fieldname is the name of the field for which these placeholders are being generated.
	 */
	public function register_global_placeholders($array, $fieldname) {
		foreach ($array as $key => $value) {
			$ph = $fieldname.'.'.$key;
			$this->placeholders[$ph] = $value;
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Set CSS for the form.  Due to WP's way of printing everything instead of
	 * returning it, we can't add stylesheets easily via a shortcode, so instead
	 * we slurp the CSS defintions (either from a file or string), and print them
	 * into a <style> tag above the form.  Janky-alert!
	 *
	 * @param string  $css
	 * @param boolean $is_file (optional)
	 */
	public function set_css($css, $is_file=true) {
		if ($is_file) {
			if (file_exists($css)) {
				$this->css = file_get_contents($css);
			}
			else {
				$this->errors['css_file_not_found'] = sprintf(__('CSS file not found %s'), "<em>$css</em>");
			}
		}
		else {
			$this->css = $css;
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Because there can be CSS id conflicts, this function allows the user to set
	 * a custom prefix to all the element ids generated by this class.
	 *
	 * @param string  $prefix used in the field id's.
	 */
	public function set_id_prefix($prefix) {
		if (is_scalar($prefix)) {
			$this->placeholders['id_prefix'] = $prefix;
		}
		else {
			$this->errors['set_id_prefix'] = sprintf( __('Invalid data type passed to %s function. Input must be a string.', CCTM_TXTDOMAIN), __FUNCTION__);
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Similar to the set_id_prefix function, this function allows the user to set
	 * a custom prefix to all field names generated by this class. This helps avoid
	 * conflicts in the $_POST array.
	 *
	 * @param string  $prefix
	 */
	public function set_name_prefix($prefix) {
		if (is_scalar($prefix)) {
			$this->placeholders['name_prefix'] = $prefix;
		}
		else {
			$this->errors['set_id_prefix'] = sprintf( __('Invalid data type passed to %s function. Input must be a string.', CCTM_TXTDOMAIN), __FUNCTION__);
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Sets the "No Results" message.
	 *
	 * @param string  $msg the message you want to display if no results are found.
	 */
	public function set_no_results_msg($msg) {
		if (is_scalar($msg)) {
			$this->no_results_msg;
		}
		else {
			$this->errors['set_id_prefix'] = sprintf( __('Invalid data type passed to %s function. Input must be a string.', CCTM_TXTDOMAIN), __FUNCTION__);
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * This allows for a dumb field override, but you could also pass it your own
	 * values, e.g.
	 * $str = wp_nonce_field('my_action', 'my_nonce_name', true, false);
	 *
	 * @param string  $str to be used in as the nonce fields.
	 */
	public function set_nonce_field($str) {
		if (is_scalar($str)) {
			$this->nonce_field = $str;
		}
		else {
			$this->errors['set_id_prefix'] = sprintf( __('Invalid data type passed to %s function. Input must be a string.', CCTM_TXTDOMAIN), __FUNCTION__);
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	 * If you need to add your own custom placeholders to the form_tpl formatting 
	 * string, this is the kosher way to do it.
	 *
	 * @param	string	$key the name of the [+placeholder+] e.g. 'custom_fields'
	 * @param	string	$value to replace into the placeholder, e.g. '<p>My long text...</p>'	 
	 */
	public function set_placeholder($key, $value) {
		$this->placeholders[$key] = $value;
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Set the formatting template (tpl) used to format the final output of the 
	 * generate() method.
	 * 
	 * @param	string	$tpl containing the entire formatting string.
	 * @return	none
	 */
	public function set_tpl($tpl) {
		if (!is_scalar($tpl)) {
			$this->errors['form_tpl_not_string'] = __('Invalid input to set_tpl() function. Input must be a string.');
			return;
		}
		if (empty($tpl)) {
			$this->errors['form_tpl_not_string'] = __('set_tpl(): Formatting string must not be empty!');
			return;
		}
		
		$this->form_tpl = $tpl;
	}
}


/*EOF*/