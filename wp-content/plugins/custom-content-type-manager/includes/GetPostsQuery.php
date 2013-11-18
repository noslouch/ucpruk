<?php
/**
 * GetPostsQuery
 *
 * New and improved post selection functions, now with formatting!
 *
 * This class has similar functionality (and arguments) to the WordPress
 * get_posts() function, but this class does things that were simply not
 * possible using the built-in WP functions, including automatically fetching
 * custom fields, far more powerful (and sensible) search criteria,
 * and the pagination of results.
 *
 * I've constructed a custom MySQL query that does the searching because I ran into
 * weird and whacky restrictions with the WP db API functions; this lets me
 * join on foreign tables and cut down on multiple inefficient select queries.
 *
 * @package SummarizePosts
 */


class GetPostsQuery {
	// Used to separate post data from wp_postmeta into key=>value pairs.
	// These values should be distinct enough so they will NOT appear in
	// any of the custom fields' content.
	const colon_separator = '::::';
	const comma_separator = ',,,,';
	// We append this to the end of concatenated results to ensure that the MySQL
	// GROUP_CONCAT() function is getting everything.  If the 'group_concat_max_len'
	// setting is too small, the caboose won't be at the end of the concatenated data,
	// and then we'll know the results are borked.
	const caboose = '$$$$';

	private $P; // stores the Pagination object.
	private $pagination_links = ''; // stores the html for the pagination links (if any).

	private $page;


	// Goes to true if orderby is set to a value not in the $wp_posts_columns array
	private $sort_by_meta_flag = false;

	// Goes to true if orderby is set to 'random'
	private $sort_by_random = false;

	// Goes to true if the date_column is set to something not in wp_posts
	private $custom_field_date_flag = false;

	// Goes to true if the user does a direct column search, e.g. $Q->ID = 234; 
	private $direct_filter_flag = false;
	private $direct_filter_columns = array(); // populated with each column that uses a direct filter
	
	// Should the query retrieve "private" custom fields?  I.e. those whose names begin with an underscore
	public $include_hidden_fields = false;
	
	// Set in the controller. If set to true, some helpful debugging msgs are printed.
	public $debug = false;

	// Stores the number of results available (used only when paginate is set to true)
	public $found_rows = null;

	// Contains all arguments listed in the $defaults, with any modifications passed by the user
	// at the time of instantiation.
	public $args = array();

	// Used to track which $arg uses which operator for the comparison
	// '=', '!=', '>', '>=', '<', '<=', '^', '$'
	public $operators = array();
	
	public $registered_post_types = array();
	public $registered_taxonomies = array();

	// Stores any errors/warnings/notices encountered.  These are all simple arrays containing localized
	// message strings.
	public $errors = array();
	public $warnings = array();
	public $notices = array();
	
	public static $static_errors = array(); // like $errors, but for static context.
	
	// Added by the set_default() function: sets default values to use for empty fields.
	public $default_values_empty_fields = array();

	// Some functions need to know which columns exist in the wp_posts, e.g. the orderby SQL
	// changes when it is a column in the wp_posts table vs. when it is a virtual column from wp_postmeta
	private static $wp_posts_columns = array(
		'ID',
		'post_author',
		'post_date',
		'post_date_gmt',
		'post_content',
		'post_title',
		'post_excerpt',
		'post_status',
		'comment_status',
		'ping_status',
		'post_password',
		'post_name',
		'to_ping',
		'pinged',
		'post_modified',
		'post_modified_gmt',
		'post_content_filtered',
		'post_parent',
		'guid',
		'menu_order',
		'post_type',
		'post_mime_type',
		'comment_count'
	);

	// For date searches (greater than, less than). If the $this->date_column is not one of these values
	// then we know we are filtering on a custom field.
	private $date_cols = array('post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt');
	
	// See set_boundaries() -- these values kick in if arguments are empty
	public $boundaries = array();
	
	// Used to time execution
	private $start_time;
	private $stop_time;
	private $duration;
	
	//! Defaults
	// args and defaults for get_posts()
	public $defaults = array(
		'limit'   => 0,
		'offset'   => null,
		'orderby'  => 'ID', // valid column (?) cannot be a metadata column
		'order'   => 'DESC', // ASC or DESC
		// include: comma-sparated string or array of IDs. Any posts you want to include. This shrinks the "pool" of resources available: all other search parameters will only search against the IDs listed, so this paramter is probably best suited to be used by itself alone. If you want to always return a list of IDs in addition to results returned by other search parameters, use the "append" parameter instead.
		'include'  => '', // see above: usually this parameter is used by itself.
		'exclude'  => '', // comma-sparated string or array of IDs. Any posts you want to exclude from search results.
		'append'  => '', // comma-sparated string or array of IDs. Any posts you always want to include *in addition* to any search criteria. (This uses the 'OR' criteria)

		// used to search custom fields
		'meta_key'  => '',
		'meta_value' => '',

		// Direct searches (mostly by direct column matches)
		'post_type'   => '',   // comma-sparated string or array
		'omit_post_type' => '*non-public*', // comma-sparated string or array; SET AT RUN-TIME to any non-public post-types
		'post_mime_type'  => '',    // comma-sparated string or array
		'post_parent'  => '',   // comma-sparated string or array
		'post_status'   => array('publish','inherit'), // comma-sparated string or array
		'post_title'  => '',    // for exact match
		'author'   => '',    // search by author's display name
		'post_date'   => '',   // matches YYYY-MM-DD.
		'post_modified'  => '',    // matches YYYY-MM-DD.
		'yearmonth'   => '',    // yyyymm

		// Date searches: set the date_column to change the column used to filter the dates.
		'date_min'  => '',     // YYYY-MM-DD (optionally include the time)
		'date_max'  => '',    // YYYY-MM-DD (optionally include the time)

		// Specify the desired date format to be used in the output of the following date columns:
		// post_date, post_date_gmt, post_modified, post_modified_gmt
		// The default is the standard MySQL YYYY-MM-DD.
		// Internally, the native YYYY-MM-DD is used.
		// Use these short-cuts:
		// --------------------------------------------------
		// 		Verbose				Sample
		// --------------------------------------------------
		// 1 =	'F j, Y, g:i a' 	March 10, 2011, 5:16 pm
		// 2 =	'j F, Y'			10 March, 2011
		// 3 =	'l F jS, Y'			Thursday March 10th, 2011
		// 4 =	'n/j/y'				3/30/11
		// 5 =	'n/j/Y'				3/30/2011		
		// or write in your own value.
		'date_format' => '',

		// Search by Taxonomies
		'taxonomy'  => '',  // category, post_tag (tag), or any custom taxonomy
		'taxonomy_term' => '', // comma-separated string or array. "term" is usually English
		'taxonomy_slug' => '', // comma-separated string or array. "slug" is usually lowercase, URL friendly ver. of "term"
		'taxonomy_depth' => 1,  // how deep do we go? http://code.google.com/p/wordpress-summarize-posts/issues/detail?id=21

		// uses LIKE %matching%
		'search_term' => '', // Don't use this with the above search stuff
		'search_columns' => array('post_title', 'post_content'), // comma-sparated string or array or more one of the following columns; if not one of the post columns, this will search the meta columns.

		// Global complicated stuff
		'join_rule'  => 'AND', // AND | OR. You can set this to OR if you really know what you're doing. Defines how the WHERE criteria are joined.
		'match_rule' => 'contains', // contains|starts_with|ends_with corresponding to '%search_term%', 'search_term%', '%search_term'
		'date_column' => 'post_modified', // which date column to use for date searches: post_date, post_date_gmt, post_modified, post_modified_gmt

		'paginate'  => false, // limit will become the 'results_per_page'


	);

	// Accessed by the set_default function, this affects field values when the recordset is
	// normalized.
	private $custom_default_values = array();

	public $cnt; // number of search results
	public $SQL; // store the query here for debugging.


	//------------------------------------------------------------------------------
	/**
	 * Read input arguments into the global parameters. Relies on the WP shortcode_atts()
	 * function to "listen" for and filter a predefined set of inputs. See the $defaults
	 * associative array for an example of valid input.
	 *
	 * @param array   $raw_args (optional)
	 */
	public function __construct($raw_args=array()) {
		
		$a = explode (' ',microtime()); 
    	$this->start_time = (double) $a[0] + $a[1];
    
		$this->registered_post_types = array_keys( get_post_types() );
		$this->registered_taxonomies = array_keys( get_taxonomies() );
	
		// Skip the non-public post-types
		$this->defaults['omit_post_type'] = array_keys(get_post_types(array('public' => false)));
		
		// Use the default args?  That fetters operation as an API
		//$this->args = $this->defaults; 
		if (!empty($raw_args) && is_array($raw_args)) {
			// Scrub up for dinner
			foreach ($raw_args as $k => $v) {
				$this->$k = $v; // this will utilize the _sanitize_arg() function.
			}
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Accessor to the object's "blessed" attributes.  Some attributes cannot be 
	 * null, lest the query break.
	 *
	 * @param string  $var
	 * @return mixed
	 */
	public function __get($var) {
/*
		if ($var == 'omit_post_type') {
			die(print_r(debug_backtrace(), true));
		}
*/
		if ( in_array($var, array_keys($this->args))) {
			return $this->args[$var];
		}
		elseif(isset($this->defaults[$var])) {
			return $this->defaults[$var];
		}
		elseif ($var == 'orderby') {
			return 'ID';
		}
		elseif ($var == 'join_rule') {
			return 'AND';
		}
		else {
			return '';
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Simple tie-in for testing whether "blessed" attributes are set.
	 *
	 * @param string  $var
	 * @return boolean
	 */
	public function __isset($var) {
		return isset($this->args[$var]);
	}


	//------------------------------------------------------------------------------
	/**
	 * Used for debugging, this prints out the active search criteria and SQL query.
	 * It is triggered when a user prints the GetPostsQuery object, e.g.
	 * $Q = new GetPostsQuery();
	 * print $Q;
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->debug();
	}


	//------------------------------------------------------------------------------
	/**
	 * Not quite "unset" in the traditional sense... this reverts back to the default
	 * values where applicable.
	 *
	 * @param string  $var
	 */
	public function __unset($var) {
		if ( isset($this->defaults[$var]) ) {
			$this->args[$var] = $this->defaults[$var];
		}
		else {
			unset($this->args[$var]);
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Validate/Sanitize and set parameters. Problems in the sanitizing process will
	 * be indicated by flagging an error/warning/notice and returning a literal null,
	 * so we don't bother setting any arg that comes back as a null.
	 *
	 * @param string  $var
	 * @param mixed   $val
	 */
	public function __set($var, $val) {
		$test = $this->_sanitize_arg($var,$val);
		if ($test !== null) {
			$this->args[$var] = $test;
		}
	}

	//------------------------------------------------------------------------------
	//! Private Functions
	//------------------------------------------------------------------------------
	/**
	 * If the user is doing a taxonomy-based search and they need to retrieve
	 * hierarchical data, then we follow the rabit hole down n levels as
	 * defined by taxonomy_depth, then we append the results to the $this->args['taxonomy_term']
	 * argument.
	 *
	 * See http://code.google.com/p/wordpress-summarize-posts/issues/detail?id=21
	 *
	 * @param array   taxonomy_terms that we want to follow down for their children terms
	 * @param unknown $all_terms_array
	 * @return array inital taxonomy_terms and their children (to the nth degree as def'd by taxonomy_depth)
	 */
	private function _append_children_taxonomies($all_terms_array) {

		global $wpdb;

		// We start with the parent terms...
		$parent_terms_array = $all_terms_array;

		for ( $i= 1; $i <= $this->taxonomy_depth; $i++ ) {
			$terms = '';
			foreach ($parent_terms_array as &$t) {
				$t = $wpdb->prepare('%s', $t);
			}

			$terms = '('. implode(',', $parent_terms_array) . ')';

			$query = $wpdb->prepare("SELECT {$wpdb->terms}.name
				FROM
				{$wpdb->terms} JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id={$wpdb->term_taxonomy}.term_id
				WHERE
				{$wpdb->term_taxonomy}.parent IN (
					SELECT {$wpdb->terms}.term_id
					FROM {$wpdb->terms}
					JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id={$wpdb->term_taxonomy}.term_id
					WHERE {$wpdb->terms}.name IN $terms
					AND {$wpdb->term_taxonomy}.taxonomy=%s
				)", $this->taxonomy);

			$results = $wpdb->get_results( $query, ARRAY_A );

			if ( empty($restuls) ) {
				break; // if there are no results, then we've traced this out.
			}

			$parent_terms_array = array(); // <-- reset this thing for the next iteration
			foreach ($results as $r) {
				$all_terms_array[] = $r['name']; // append
				$parent_terms_array[] = $r['name']; // and set this for the next generation
			}
		}

		return array_unique($all_terms_array);

	}


	//------------------------------------------------------------------------------
	/**
	 * Takes a comma separated string and turns it to an array, or passes the array
	 *
	 * BEWARE: Sometimes you get arrays like this: array('');  THOSE ARE NOT EMPTY!!!
	 *
	 * @param mixed   $input is either a comma-separated string or an array
	 * @param string  $type  describing the type of input: 'integer','alpha',
	 * @return array
	 */
	private function _comma_separated_to_array($input, $type) {
		$output = array();
		if ( empty($input) ) {
			return $output;
		}
		if ( is_array($input) ) {
			$output = $input;
		}
		else {
			$output = explode(',', $input);
		}

		foreach ($output as $i => $item) {
			$output[$i] = trim($item);
			$item = trim($item);
			
			// Remove quotes, e.g. $input = '"1","2","3"'
			$item = preg_replace('/^"/', '', $item);
			$item = preg_replace('/"$/', '', $item);
			$item = preg_replace("/^'/", '', $item);
			$item = preg_replace("/'$/", '', $item);
			
			if (empty($item)) {
				unset($output[$i]); // this covers the nefarious empty arrays!
				continue; 
			}
			switch ($type) {
				case 'integer':
					$output[$i] = (int) $item;
					break;
					// Only a-z, _, - is allowed.
				case 'alpha':
					if ( !preg_match('/[a-z_\-]/i', $item) ) {
						$this->errors[] = __('Invalid alpha input:') . $item;
					}
					break;
				case 'post_type':
					if ( !empty($item) && !post_type_exists($item) ) {
						$this->errors[] = __('Invalid post_type:') . $item . '<-- '. print_r($this->registered_post_types, true) . ' Line:' .__LINE__;
	
					}
					break;
				case 'post_status':
					if ( !in_array($item, array('inherit', 'publish', 'auto-draft', 'draft')) ) {
						$this->errors[] = __('Invalid post_status:') . $item;
					}
					break;
				case 'search_columns':
					// Taking this on: http://code.google.com/p/wordpress-summarize-posts/issues/detail?id=27
					if ( !preg_match('/[a-z_0-9]/i', $item) ) {
						$this->errors[] = __('Invalid column name. Column names may only contain alphanumeric characters and underscores: ') . $item;
					}
	
					break;
				case 'no_tags':
					$output[$i] = strip_tags($item);
			}
		}

		return $output;
	}


	//------------------------------------------------------------------------------
	/**
	 * Returns the number of results for the query executed.
	 * Must have included the SQL_CALC_FOUND_ROWS option in the query. This is done if
	 * the paginate option is set to true.
	 *
	 * @return integer
	 */
	private function _count_posts() {
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT FOUND_ROWS() as cnt', OBJECT );
		return $results[0]->cnt;
	}


	//------------------------------------------------------------------------------
	/**
	 * Change the date of results (depending on whether or not the 'date_format'
	 * option was set.
	 *
	 * @param mixed   result set
	 * @param array $results -- a record set.
	 * @return array  result set
	 */
	private function _date_format($results) {
		if ( $this->date_format) {

			$date_cols = $this->date_cols;
			if (!in_array($this->date_column, $this->date_cols)) {
				$date_cols[] = $this->date_column;
			}

			foreach ($results as &$r) {
				foreach ($date_cols as $key) {
					if (isset($r[$key]) && !empty($r[$key])) {
						$date = date_create($r[$key]);
						$r[$key] = date_format($date, $this->date_format);
					}
				}
			}
		}

		return $results;
	}


	//------------------------------------------------------------------------------
	/**
	 * Ensure a valid date. 0000-00-00 qualifies as valid; if you need to ensure a REAL
	 * date (i.e. where '0000-00-00' is not allowed), then simply marking the field required
	 * won't work because the string '0000-00-00' is not empty.  To require a REAL date, use
	 * the following syntax in your definitions:
	 * 'mydatefield' => 'date["YYYY-MM-DD","required"]
	 *
	 * @param string  $date to be checked
	 * @return boolean whether or not the input is a valid date
	 */
	private function _is_date( $date ) {
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
	 * Is a datetime in MySQL YYYY-MM-DD HH:MM:SS date format?  (Time is optional).
	 *
	 * @param string
	 * @param unknown $datetime
	 * @return boolean
	 */
	private function _is_datetime( $datetime ) {
		$date = null;
		$time = null;
		if (strpos($datetime, ' ')) {
			list ($date, $time) = explode(' ', $datetime);
		}
		// Time was omitted
		else {
			$date = $datetime;
		}

		if ( !$this->_is_date($date) ) {
			return false;
		}
		elseif ( empty($time) ) {
			return true;
		}

		$time_format = 'H:i:s';
		$unixtime = strtotime($time);
		$converted_time =  date($time_format, $unixtime);

		if ( $converted_time != $time ) {
			return false;
		}

		return true;

	}

	//------------------------------------------------------------------------------
	/**
	 * Tests whether a string is valid for use as a MySQL column name.  This isn't 
	 * 100% accurate, but the postmeta virtual columns can be more flexible.
	 * @param	string
	 * @return	boolean
	 */
	private function _is_valid_column_name($str) {
		if (preg_match('/[^a-zA-Z0-9\/\-\_]/', $str)) {
			return false;
		}
		else {
			return true;
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	 * This makes each record in the recordset have the same attributes.  This helps
	 * us avoid "Undefined property" or "Undefined index" PHP notices. This pools
	 * ALL available attributes and ensures that each record in the recordset has the
	 * same attributes.  Any missing attributes are added as an empty string.
	 *
	 * @param array   $records an array of objects or array of arrays
	 * @return array recordset (an array of objects or array of arrays)
	 */
	private function _normalize_recordset($records) {
		// Default values will force an attribute, even if the attribute doesn't exist in the recordset
		$unique_attributes = array_keys($this->custom_default_values);

		// Get unique attributes
		foreach ($records as $r) {
			$unique_attributes = array_merge( array_keys( (array) $r), $unique_attributes);
		}
		$unique_attributes = array_unique($unique_attributes);

		// Ensure that each record has the same attributes
		foreach ($records as &$r) {
			foreach ($unique_attributes as $a) {
				if (!isset($r[$a])) {
					$r[$a] = '';
				}
			}
		}

		// Set any default values
		if (!empty($this->custom_default_values)) {
			foreach ($this->custom_default_values as $key => $value) {
				foreach ($records as &$r) {
					if (empty($r[$key])) {
						$r[$key] = $value;
					}
				}
			}
		}

		return $records;
	}

	//------------------------------------------------------------------------------
	/**
	 * Filter an argument.  All inputs hinge on this function: it ensures valid input.
	 * In many ways, this is the function that does all the work.
	 *
	 * $arg should always be a string.
	 * $val may be a string or an array, depending on what the $arg accepts.
	 * Also, $val may include an operator, e.g. Array([>] => 2011-01-05)
	 *
	 * @param	string	$arg name of the argument being set 
	 * @param	mixed	$val value to set it to
	 * @return	mixed	sanitized argument or literal null on error
	 */
	private function _sanitize_arg($arg, $val) {

		if (is_array($arg)) {
			$this->warnings[] = __('Invalid input argument.  Arrays not allowed as argument names.', CCTM_TXTDOMAIN);
			return null;
		}
		
		if (empty($arg)) {
			$this->warnings[] = __('Empty input argument.', CCTM_TXTDOMAIN);
			return null;
		}

		// Some cleanup, fine-tuning
		if (is_array($val)) {
			// Filter out "empty" arrays, e.g. array('') or Array([0] => '')
			// these arise from certain form submissions.
			foreach ($val as $k => $v) {
				if (empty($v)) {
					unset($val[$k]);
				}			
			}
			// Get the optional operator for this $arg
			// '=', '!=', '>', '>=', '<', '<=', '^', '$'
			foreach ($val as $k => $v) {
				if (is_numeric($k)) {
					break;
				}
				
				switch($k) {
					case '!=':
					case 'ne':
					case '<>':
						$this->operators[$arg] = '!=';
						break;
					case '>':
					case 'gt':
						$this->operators[$arg] = '>';
						break;
					case '>=':
					case 'gte':
						$this->operators[$arg] = '>=';
						break;
					case '<':
					case 'lt':
						$this->operators[$arg] = '<';
						break;
					case '<=':
					case 'lte':
						$this->operators[$arg] = '<=';
						break;
					case '^':
					case 'starts_with':
						$this->operators[$arg] = 'starts_with';
						break;
					case '$':
					case 'ends_with':
						$this->operators[$arg] = 'ends_with';
						break;
					case '%':
					case 'like':
					case 'contains':
						$this->operators[$arg] = 'like';
						break;
					default:
						$this->operators[$arg] = '=';
				}
				// Override/shift the value
				$val = $v;
			}
		}
		

		// Some corrections required: if you set the post_type that you want, remove that post_type
		// from the 'omit_post_type' argument.
		if ($arg == 'post_type') {
			$omit_post_type = $this->omit_post_type;
			if (in_array($val, $omit_post_type)) {
				$new_omits = array();
				foreach ($omit_post_type as $pt) {
					if ($pt != $val) {
						$new_omits[] = $pt;		
					}
				}
				$this->omit_post_type = $new_omits;
			}
		} 
		// Don't do this: WP uses 'ID' as a db column
		//$arg = strtolower($arg);
		
		// fill in default value if the parameter is empty
		// We gotta handle cases where the user tries to set something to null that would break the query
		// if it went to null.
		// beware of empty arrays
		if (empty($val)) {
			if (isset($this->defaults[$arg]) && !empty($this->defaults[$arg])) {
				return $this->defaults[$arg];
				$this->notices[] = sprintf(__('Empty input for %s. Using default parameters.', CCTM_TXTDOMAIN ),  "<em>$var</em>");				
			}
			else {
				return '';
			}
		}
		
		switch ($arg) {
		// Integers
		case 'limit':
		case 'offset':
		case 'yearmonth':
			return (int) $val;
			break;
		// ASC or DESC
		case 'order':
			$val = strtoupper($val);
			if ( $val == 'ASC' || $val == 'DESC' ) {
				return $val;
			}
			else {
				$this->errors[] = sprintf(__('Invalid order: %s. Order may only be "ASC" or "DESC".'), '<em>'.htmlspecialchars($val).'</em>');
			}
			break;
		case 'orderby':
			if ($val == 'random') {
				$this->sort_by_random = true;
				// $args['order'] = ''; // blank this out
			}
			elseif (!in_array( $val, self::$wp_posts_columns) ) {
				$this->sort_by_meta_flag = true;
				if ($this->_is_valid_column_name($val)) {					
					$this->notices[] = sprintf(__('orderby column not a default post column: %s', CCTM_TXTDOMAIN), '<em>'.htmlspecialchars($val).'</em>');
					return $val;
				}
				else {
					$this->errors[] = sprintf(__('Invalid column name supplied for orderby: %s', CCTM_TXTDOMAIN), '<em>'.htmlspecialchars($val).'</em>');
					return null;
				}
				
			}
			else {
				return $val;
			}
			break;
		// List of Integers
		case 'include':
		case 'exclude':
		case 'append':
		case 'post_parent':
			return $this->_comma_separated_to_array($val, 'integer');
			break;
			// Dates
		case 'post_modified':
		case 'post_date':
		case 'date':
			// if it's a date
			if ($this->_is_date($val) ) {
				return $val;
			}
			else {
				$this->errors[] = sprintf( __('Invalid date argument: %s', CCTM_TXTDOMAIN), $arg.':'.htmlspecialchars($val) );
				return null;
			}
			break;
		// Datetimes
		case 'date_min':
		case 'date_max':
			// if is a datetime
			if ($this->_is_datetime($val) ) {
				return $val;
			}
			else {
				$this->errors[] = sprintf( __('Invalid datetime argument: %s', CCTM_TXTDOMAIN), $var.':'.htmlspecialchars($val) );
				return null;
			}
			break;
		// Date formats, some short-hand (see http://php.net/manual/en/function.date.php)
		case 'date_format':
			switch ($val) {
			case '1': // e.g. March 10, 2011, 5:16 pm
				return 'F j, Y, g:i a';
				break;
			case '2': // e.g. 10 March, 2011
				return 'j F, Y';
				break;
			case '3': // e.g. Thursday March 10th, 2011
				return 'l F jS, Y';
				break;
			case '4': // e.g. 3/30/11
				return 'n/j/y';
				break;
			case '5': // e.g. 3/30/2011
				return 'n/j/Y';
				break;
			default:
				return $val;
			}
			break;
		// Post Types
		case 'post_type':
		case 'omit_post_type':
			return $this->_comma_separated_to_array($val, 'post_type');
			break;
		// Post Status
		case 'post_status':
			return $this->_comma_separated_to_array($val, 'post_status');
			break;

		// Almost any value... prob. should use $wpdb->prepare( $query, $val )
		case 'meta_key':
		case 'meta_value':
		case 'post_title':
		case 'author':
		case 'search_term':
			return $val;
			break;

		// Taxonomies
		case 'taxonomy':
			if ( taxonomy_exists($val) ) {
				return $val;
			}
			else {
				$this->warnings[] = sprintf(__('Taxonomy does not exist: %s',CCTM_TXTDOMAIN), '<em>'.htmlspecialchars($val).'</em>');
				return null;
			}
			break;
			// The category_description() function adds <p> tags to the value.
		case 'taxonomy_term':
			return $this->_comma_separated_to_array($val, 'no_tags');
			break;
		case 'taxonomy_slug':
			return $this->_comma_separated_to_array($val, 'alpha');
			break;
		case 'taxonomy_depth':
			return (int) $val;
			break;
		case 'search_columns':
			return $this->_comma_separated_to_array($val, 'search_columns');
			break;

			// And or Or
		case 'join_rule':
			$val = strtoupper($val);
			if ( in_array($val, array('AND', 'OR')) ) {
				return $val;
			}
			else {
				$this->errors[] = __('Invalid parameter for join_rule. join_rule must be "AND" or "OR"', CCTM_TXTDOMAIN);
				return null;
			}
			break;
			// match rule...
		case 'match_rule':
			$val = strtolower($val);
			if ( in_array($val, array('contains', 'starts_with', 'ends_with')) ) {
				return $val;
			}
			else {
				$this->errors[] = __('Invalid parameter for match_rule. match_rule may be "contains", "starts_with", or "ends_with"', CCTM_TXTDOMAIN);
				return null;
			}
			break;
		case 'date_column':
			// Simple case: user specifies a column from wp_posts
			if ( in_array($val, $this->date_cols) ) {
				$this->custom_field_date_flag = false; // redundant setting in case the user sets this parameter repeatedly
				return $val;
			}
			// You can't do a date sort on a built-in wp_posts column other than the ones id'd in $this->date_cols
			elseif ( in_array($val, self::$wp_posts_columns)) {
				$this->errors[] = __('Invalid date column.', CCTM_TXTDOMAIN);
				return null;
			}
			// Otherwise, we're in custom-field land
			else {
				$this->custom_field_date_flag = true;
				return $val;
			}
			break;
		case 'paginate':
			return (bool) $val;
			break;
		case 'post_mime_type':
			if (preg_match('/[^a-zA-Z0-9\/\-\_]/', $val)) {
				$this->errors[] = __('post_mime_type contains illegal characters.  Input ignored.', CCTM_TXTDOMAIN);
				return null;
			}
			else {
				return $val;				
			}
			break;				
		// If you're here, it's assumed that you're trying to filter directly on a wp_posts column or 
		// on a custom field.  The argument MUST be a valid column name.  Otherwise this might leak into 
		// a MySQL injection attack.
		default:
			if (!$this->_is_valid_column_name($arg)) {
				$this->errors[] = sprintf(__('Invalid argument name %s.  Input ignored.', CCTM_TXTDOMAIN), '<em>'.htmlspecialchars($arg).'</em>');
				return null;
			}
			else {
				
				$this->direct_filter_flag =  true;
				$this->direct_filter_columns[] = $arg;
				$this->notices[] = sprintf(__('Filtering on direct column/value: %s', CCTM_TXTDOMAIN ), '<em>'.$arg.':'.htmlspecialchars($val).'</em>');
				// We can easily filter for integers...
				if (in_array($arg, array('ID','post_parent','menu_order','comment_count'))) {
					return (int) $val;
				}
				// TO-DO: filter for other data-types?  Or should this just be moved to the above?
				return wp_kses($val, array());
			}
		}
	}


	//! SQL
	/**------------------------------------------------------------------------------
	 * This is the main SQL query constructor: it is the engine that drives this
	 * entire plugin.
	 * It's meant to be called by the various querying functions:
	 *	get_posts()
	 *	count_posts()
	 *	query_distinct_yearmonth()
	 *
	 * INPUT:
	 *	none; this relies on the values set in class variables.
	 *
	 * OUTPUT:
	 * An array of results.
	 *
	 * You can't use the WP query_posts() function here because the global $wp_the_query
	 * isn't defined yet.  get_posts() works, however, but its format is kinda whack.
	 * Jeezus H. Christ. Crufty ill-defined API functions.
	 * http://shibashake.com/wordpress-theme/wordpress-query_posts-and-get_posts
	 *
	 * @return string
	 */
	private function _get_sql() {
		global $wpdb;

		$this->SQL =
			"SELECT
			[+select+]
			{$wpdb->posts}.*
			, parent.ID as 'parent_ID'
			, parent.post_title as 'parent_title'
			, parent.post_excerpt as 'parent_excerpt'
			, author.display_name as 'author'
			, thumbnail.ID as 'thumbnail_id'
			, thumbnail.guid as 'thumbnail_src'
			, metatable.metadata

			[+select_metasortcolumn+]

			FROM {$wpdb->posts}
			LEFT JOIN {$wpdb->posts} parent ON {$wpdb->posts}.post_parent=parent.ID
			LEFT JOIN {$wpdb->users} author ON {$wpdb->posts}.post_author=author.ID
			LEFT JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
			LEFT JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id={$wpdb->term_relationships}.term_taxonomy_id
			LEFT JOIN {$wpdb->terms} ON {$wpdb->terms}.term_id={$wpdb->term_taxonomy}.term_id
			LEFT JOIN {$wpdb->postmeta} thumb_join ON {$wpdb->posts}.ID=thumb_join.post_id
				AND thumb_join.meta_key='_thumbnail_id'
			LEFT JOIN {$wpdb->posts} thumbnail ON thumbnail.ID=thumb_join.meta_value
			LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID={$wpdb->postmeta}.post_id
			LEFT JOIN
			(
				SELECT
				{$wpdb->postmeta}.post_id,
				CONCAT( GROUP_CONCAT( CONCAT({$wpdb->postmeta}.meta_key,'[+colon_separator+]', {$wpdb->postmeta}.meta_value) SEPARATOR '[+comma_separator+]'), '[+caboose+]') as metadata
				FROM {$wpdb->postmeta}
				[+hidden_fields+]
				GROUP BY {$wpdb->postmeta}.post_id
			) metatable ON {$wpdb->posts}.ID=metatable.post_id

			[+join_for_metasortcolumn+]

			WHERE
			(
			1
			[+direct_filter+]
			[+include+]
			[+exclude+]
			[+omit_post_type+]
			[+post_type+]
			[+post_mime_type+]
			[+post_parent+]
			[+post_status+]
			[+yearmonth+]
			[+meta+]
			[+author+]


			[+taxonomy+]
			[+taxonomy_term+]
			[+taxonomy_slug+]

			[+search+]
			[+exact_date+]
			[+date_min+]
			[+date_max+]
			)
			[+append+]

			GROUP BY {$wpdb->posts}.ID
			ORDER BY [+orderby+] [+order+]
			[+limit+]
			[+offset+]";

		// Substitute into the query.
		$hash = array();
		$hash['select'] = ($this->paginate)? 'SQL_CALC_FOUND_ROWS' : '';
		$hash['colon_separator'] = self::colon_separator;
		$hash['comma_separator'] = self::comma_separator;
		$hash['caboose']  = self::caboose;

		$hash['include'] = $this->_sql_filter($wpdb->posts, 'ID', 'IN', $this->include);
		$hash['exclude'] = $this->_sql_filter($wpdb->posts, 'ID', 'NOT IN', $this->exclude);
		$hash['append'] = $this->_sql_append($wpdb->posts);

		$hash['omit_post_type'] = $this->_sql_filter($wpdb->posts, 'post_type', 'NOT IN', $this->omit_post_type);
		$hash['post_type'] = $this->_sql_filter($wpdb->posts, 'post_type', 'IN', $this->post_type);
		$hash['post_mime_type'] = $this->_sql_filter_post_mime_type();
		$hash['post_parent'] = $this->_sql_filter($wpdb->posts, 'post_parent', 'IN', $this->post_parent);
		$hash['post_status'] = $this->_sql_filter($wpdb->posts, 'post_status', 'IN', $this->post_status);
		$hash['yearmonth'] = $this->_sql_yearmonth();
		$hash['meta'] = $this->_sql_meta();
		$hash['author'] = $this->_sql_filter('author', 'display_name', '=', $this->author);

		$hash['taxonomy'] = $this->_sql_filter($wpdb->term_taxonomy, 'taxonomy', '=', $this->taxonomy);
		$hash['taxonomy_term'] = $this->_sql_filter($wpdb->terms, 'name', 'IN', $this->taxonomy_term);
		$hash['taxonomy_slug'] = $this->_sql_filter($wpdb->terms, 'slug', 'IN', $this->taxonomy_slug);

		if ($this->custom_field_date_flag) {
			$hash['exact_date'] = $this->_sql_custom_date_filter($this->post_date);
			$hash['date_min'] = $this->_sql_custom_date_filter($this->date_min, '>=');
			$hash['date_max'] = $this->_sql_custom_date_filter($this->date_max, '<=');
		}
		else {
			$hash['exact_date'] = $this->_sql_filter($wpdb->posts, $this->date_column, '=', $this->post_date);
			$hash['date_min'] = $this->_sql_filter($wpdb->posts, $this->date_column, '>=', $this->date_min);
			$hash['date_max'] = $this->_sql_filter($wpdb->posts, $this->date_column, '<=', $this->date_max);
			//   die($hash['date_min']);
		}

		$hash['search'] = $this->_sql_search();

		$hash['order'] = $this->order;
		
		// Custom handling for sorting on custom fields
		// http://code.google.com/p/wordpress-summarize-posts/issues/detail?id=12
		if ($this->sort_by_random) {
			$hash['orderby'] = 'RAND()';
			$hash['order'] = ''; // <-- blanks this out!
			$hash['select_metasortcolumn'] = '';
			$hash['join_for_metasortcolumn'] = '';
		}
		// See http://code.google.com/p/wordpress-summarize-posts/issues/detail?id=20
		elseif ($this->sort_by_meta_flag) {
			$hash['orderby'] = 'metasortcolumn';
			$hash['select_metasortcolumn'] = ', orderbymeta.meta_value as metasortcolumn';
			$hash['join_for_metasortcolumn'] = sprintf("LEFT JOIN {$wpdb->postmeta} orderbymeta ON %s.ID=orderbymeta.post_id AND orderbymeta.meta_key = %s"
				, $wpdb->posts
				, $wpdb->prepare('%s', $this->orderby)
			);
		}
		// Standard: sort by a column in wp_posts
		else {
			$hash['orderby'] = $wpdb->posts.'.'.$this->orderby;
			$hash['select_metasortcolumn'] = '';
			$hash['join_for_metasortcolumn'] = '';
		}

		$hash['limit'] = $this->_sql_limit();
		$hash['offset'] = $this->_sql_offset();

		// Direct filters (if any), e.g. 
		$hash['direct_filter'] = '';
		if ($this->direct_filter_flag) {
			foreach($this->direct_filter_columns as $c) {
				if (in_array($c, self::$wp_posts_columns)) {
					//$hash['direct_filter'] .= $this->_sql_filter($wpdb->posts, $c, '=', $this->$c);
					$hash['direct_filter'] .= $this->_sql_filter($wpdb->posts, $c, $this->operators[$c], $this->$c);
				}
				else {
					$query = " {$this->join_rule} ({$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value = %s)";
					$hash['direct_filter'] .= $wpdb->prepare( $query, $c, $this->$c );				
				}
			}
		}

		if (!$this->include_hidden_fields) {
			$hash['hidden_fields'] = "WHERE {$wpdb->postmeta}.meta_key NOT LIKE '\_%'";
		}

		$this->SQL = self::parse($this->SQL, $hash);
		// Strip whitespace
		$this->SQL  = preg_replace('/\s\s+/', ' ', $this->SQL );

		return $this->SQL;

	}


	//------------------------------------------------------------------------------
	/**
	 * _sql_append: always include the IDs listed.
	 *
	 * @param string  $table
	 * @return string part of the MySQL query.
	 */
	private function _sql_append($table) {
		if ($this->append) {
			return "OR $table.ID IN ({$this->append})";
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Used when the date_column is set to something that's a custom field
	 *
	 * @param string  $date_value
	 * @param string  $operation  (optional)
	 * @return string part of the MySQL query.
	 */
	private function _sql_custom_date_filter($date_value, $operation='=') {
		global $wpdb;
		if ($date_value) {
			$query = " AND ({$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value $operation %s)";
			return $wpdb->prepare( $query, $this->date_column, $date_value );
		}
		else {
			return '';
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Generic SQL filter generator to handle multiple filters.
	 *
	 * @param string  $table     name (verbatim, including any prefixes)
	 * @param string  $column    name
	 * @param string  $operation logical operator, e.g. '=' or 'NOT IN'
	 * @param string  $value     being filtered for.
	 * @return string part of the MySQL query.
	 */
	private function _sql_filter($table, $column, $operation, $value) {
		global $wpdb;

		if ( empty($value) ) {
			return '';
		}

		if ( is_array($value) ) {
			foreach ($value as &$v) {
				$v = $wpdb->prepare('%s', $v);
			}

			$value = '('. implode(',', $value) . ')';
		}
		else {
			$value = $wpdb->prepare('%s', $value);
		}

		return sprintf("%s %s.%s %s %s"
			, $this->join_rule
			, $table
			, $column
			, $operation
			, $value
		);
	}

	//------------------------------------------------------------------------------
	/**
	 * Generates string to be used in the main SQL query's WHERE clause.
	 * Construct the part of the query for searching by mime type
	 *
	 * @return string
	 */
	private function _sql_filter_post_mime_type() {
		global $wpdb;
		if ( $this->post_mime_type) {
			$query = " AND {$wpdb->posts}.post_mime_type LIKE %s";
			return $wpdb->prepare( $query, $this->post_mime_type.'%' );
		}
		else {
			return '';
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Generate string to be used in the main SQL query's LIMIT/OFFSET clause.
	 *
	 * @return string
	 */
	private function _sql_limit() {
		if ( $this->limit ) {
			return ' LIMIT ' . $this->limit;
		}
		else {
			return '';
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Generates string to be used in the main SQL query's LIMIT/OFFSET clause
	 *
	 * @return string
	 */
	private function _sql_offset() {
		if ( $this->limit && $this->offset ) {
			return ' OFFSET '. $this->offset;
		}
		else {
			return '';
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Generates the string to be used in the main SQL query's WHERE clause.
	 * Construct the part of the query for searching by name.
	 *
	 *   AND (
	 *   wp_posts.post_title LIKE '%elcom%'
	 *   OR
	 *   wp_posts.post_content LIKE '%elcom%'
	 *   OR
	 *   wp_postmeta.meta_value LIKE '%elcom%'
	 *  )
	 *
	 * @return string
	 */
	private function _sql_search() {
		global $wpdb;

		if (empty($this->search_term) || empty($this->search_columns)) {
			$this->warnings[] = __('Search parameters ignored: search_term and search_columns must be set.', CCTM_TXTDOMAIN);
			return '';
		}

		$criteria = array();
//		print_r($this->search_term); exit;
		foreach ( $this->search_columns as $c ) {
			// For standard columns in the wp_posts table
			if ( in_array($c, self::$wp_posts_columns ) ) {
				switch ($this->match_rule) {
				case 'starts_with':
					$criteria[] = $wpdb->prepare("{$wpdb->posts}.$c LIKE %s", '%'.$this->search_term);
					break;
				case 'ends_with':
					$criteria[] = $wpdb->prepare("{$wpdb->posts}.$c LIKE %s", $this->search_term.'%');
					break;
				case 'contains':
				default:
					$criteria[] = $wpdb->prepare("{$wpdb->posts}.$c LIKE %s", '%'.$this->search_term.'%');
				}
			}
			// For custom field "columns" in the wp_postmeta table
			else {
				switch ($this->match_rule) {
				case 'starts_with':
					$criteria[] = $wpdb->prepare("{$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value LIKE %s"
						, $c
						, '%'.$this->search_term);
					break;
				case 'ends_with':
					$criteria[] = $wpdb->prepare("{$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value LIKE %s"
						, $c
						, $this->search_term.'%');
					break;
				case 'contains':
				default:
					$criteria[] = $wpdb->prepare("{$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value LIKE %s"
						, $c
						, '%'.$this->search_term.'%');
				}
			}
		}

		$query = implode(' OR ', $criteria);
		$query = $this->join_rule . " ($query)";
		return $query;
	}


	//------------------------------------------------------------------------------
	/**
	 * Generates string to be used in the main SQL query used to get a yearmonth
	 * column for each post.  It uses the column specified by the date_column.
	 *
	 * SELECT DISTINCT DATE_FORMAT(post_modified, '%Y%m') FROM wp_posts;
	 * http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html#function_date-format
	 *
	 * @return string
	 */
	private function _sql_yearmonth() {
		global $wpdb;
		if ( !$this->yearmonth ) {
			return '';
		}
		// e.g. AND DATE_FORMAT(wp_posts.post_modified, '%Y%m') = '201102'
		return sprintf("%s DATE_FORMAT(%s.%s, '%%Y%%m') = %s" // note the double percentage to placate sprintf 
			, $this->join_rule
			, $wpdb->posts
			, $this->date_column
			, $wpdb->prepare('%s', $this->yearmonth)
		);
	}


	//------------------------------------------------------------------------------
	/**
	 * Generate part of SQL query used to search for custom fields.
	 * AND wp_postmeta.meta_key = 'yarn'
	 * AND wp_postmeta.meta_value = 'nada'
	 *
	 * @return string
	 */
	private function _sql_meta() {
		global $wpdb;

		if ( $this->meta_key && $this->meta_value) {
			return sprintf("%s (%s.meta_key=%s AND %s.meta_value=%s)"
				, $this->join_rule
				, $wpdb->postmeta
				, $wpdb->prepare('%s', $this->meta_key)
				, $wpdb->postmeta
				, $wpdb->prepare('%s', $this->meta_value)
			);
		}
		elseif ($this->meta_key) {
			return $this->_sql_filter($wpdb->postmeta, 'meta_key', '=', $this->meta_key);
		}
		else {
			return $this->_sql_filter($wpdb->postmeta, 'meta_value', '=', $this->meta_value);
		}
	}


	//------------------------------------------------------------------------------
	//! Public Functions
	//------------------------------------------------------------------------------
	/**
	 * Prints debugging messages for those intrepid souls who are encountering problems...
	 *
	 * @return string
	 */
	public function debug() {
		if ( empty($this->SQL) ) {
			$this->SQL = $this->_get_sql();
		}
    
		return sprintf(
			'<div class="summarize-posts-summary">
				<h1>Summarize Posts</h1>

				<!-- errors, warnings, notices-->
				%s
				%s
				%s
				<!-- duration -->
				%s
				
				<h2>%s</h2>
				<p>%s</p>
					<div class="summarize-post-arguments">%s</div>

				<h2>%s</h2>
					<div class="summarize-posts-query"><textarea rows="20" cols="80">%s</textarea></div>

				<h2>%s</h2>
					<div class="summarize-posts-shortcode"><textarea rows="3" cols="80">%s</textarea></div>

				<h2>%s</h2>
					<div class="summarize-posts-results"><textarea rows="20" cols="80">%s</textarea></div>
			</div>'
			, $this->get_errors()
			, $this->get_warnings()
			, $this->get_notices()
			, $this->get_duration()
			, __('Arguments', CCTM_TXTDOMAIN)
			, __('For more information on how to use this function, see the documentation for the <a href="http://code.google.com/p/wordpress-summarize-posts/wiki/get_posts">GetPostsQuery::get_posts()</a> function.', CCTM_TXTDOMAIN)
			, $this->get_args()
			, __('Raw Database Query', CCTM_TXTDOMAIN)
			, $this->SQL
			, __('Comparable Shortcode', CCTM_TXTDOMAIN)
			, $this->get_shortcode()
			, __('Results', CCTM_TXTDOMAIN)
			, print_r( $this->get_posts(), true)
		);
	}


	//------------------------------------------------------------------------------
	/**
	 * Returns an HTML formatted version of filtered input arguments for debugging.
	 *
	 * @return string
	 */
	public function get_args() {
		if (empty($this->args)) {
			return '<p>'.__('No arguments supplied.', CCTM_TXTDOMAIN) .'</p>';
		}
		$output = '<ul class="summarize-posts-argument-list">'."\n";

		foreach ($this->args as $k => $v) {
			if ( is_array($v) && !empty($v) ) {
				$output .= '<li class="summarize-posts-arg"><strong>'.$k.'</strong>: Array
				('.implode(', ', $v).')</li>'."\n";
			}
			else {
				if ( $v === false ) {
					$v = 'false';
				}
				elseif ( $v === true ) {
					$v = 'true';
				}
				elseif ( empty($v) ) {
					$v = '--';
				}
				$output .= '<li class="summarize-posts-arg"><strong>'.$k.'</strong>: '.$v.'</li>'."\n";
			}
		}
		$output .= '</ul>'."\n";
		return $output;
	}

	//------------------------------------------------------------------------------
	/**
	 * Gets the URL of the current page to use in generating pagination links.
	 * http://www.webcheatsheet.com/PHP/get_current_page_url.php
	 * This uses wp_kses() to reduce risk of a-holes.
	 *
	 * @return string
	 */
	static function get_current_page_url() {
		if ( isset($_SERVER['REQUEST_URI']) ) {
			$_SERVER['REQUEST_URI'] = preg_replace('/&?offset=[0-9]*/', '', $_SERVER['REQUEST_URI']);
		}
		return wp_kses($_SERVER['REQUEST_URI'], '');
	}

	//------------------------------------------------------------------------------
	/**
	 * Gets script execution time (since instantiation) for debugging purposes
	 */
	public function get_duration() {
		$a = explode (' ',microtime()); 
    	$this->stop_time = (double) $a[0] + $a[1];
    	$this->duration = number_format(($this->stop_time - $this->start_time),2);

		return sprintf('<h2>%s</h2><div class="summarize-posts-errors">%s</div>'
			, __('Execution Time', CCTM_TXTDOMAIN)
			, sprintf(__('%s seconds', CCTM_TXTDOMAIN), $this->duration));
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Format any errors in an unordered list, or returns a message saying there were no errors.
	 *
	 * @return string message detailing errors.
	 */
	public function get_errors() {

		$output = '';
		
		$errors = $this->errors;
		
		if ($errors) {
			$items = '';
			foreach ($errors as $e) {
				$items .= '<li>'.$e.'</li>' ."\n";
			}
			$output = '<ul>'."\n".$items.'</ul>'."\n";
		}
		else {
			$output = __('There were no errors.', CCTM_TXTDOMAIN);
		}
		return sprintf('<h2>%s</h2><div class="summarize-posts-errors">%s</div>'
			, __('Errors', CCTM_TXTDOMAIN)
			, $output);
	}


	//------------------------------------------------------------------------------
	/**
	 * Format any errors in an unordered list, or returns a message saying there were no errors.
	 *
	 * @return string message detailing errors.
	 */
	public function get_notices() {

		$output = '';
		
		$notices = $this->notices;
		
		if ($notices) {
			$items = '';
			foreach ($notices as $n) {
				$items .= '<li>'.$n.'</li>' ."\n";
			}
			$output = '<ul>'."\n".$items.'</ul>'."\n";
		}
		else {
			$output = __('There were no notices.', CCTM_TXTDOMAIN);
		}
		return sprintf('<h2>%s</h2><div class="summarize-posts-notices">%s</div>'
			, __('Notices', CCTM_TXTDOMAIN)
			, $output);
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Only valid if the pagination option has been set.  This is how the user should
	 * retrieve the pagination links that have been generated.
	 *
	 * @return string html links
	 */
	public function get_pagination_links() {
		return $this->pagination_links;
	}


	//------------------------------------------------------------------------------
	/**
	 * Retrieves a single post by its post ID. The output format here is dictated by
	 * the set_output_type() function (ARRAY_A or OBJECT).  This function is a
	 * convenience function accessor to the get_posts() function.
	 *
	 * @param integer $id post ID of the post to be fetched
	 * @return mixed either an OBJECT or ARRAY_A representing the post
	 */
	public function get_post($id) {

		$post = $this->get_posts(array('ID' => $id ), true);
		if (!empty($post) ) {
			return $post[0]; // return first post
		}

		return null;
	}


	//------------------------------------------------------------------------------
	/**
	 * This is the main event, where all the action leads.  This is what generates
	 * database query and actually gets the results from the database, cousins to
	 * the other querying functions:
	 *   count_posts()
	 *   query_distinct_yearmonth()
	 *
	 * @param array   $args (optional)
	 * @param boolean $ignore_defaults (optional)
	 * @return array  result set
	 */
	public function get_posts($args=array(), $ignore_defaults=false) {
		
		if ($ignore_defaults) {
			$this->set_defaults(array(), true);
		}
		
		global $wpdb;
		
		foreach ($args as $k => $v) {
			$this->$k = $v; // this will utilize the _sanitize_arg() function.
		}
		
		// if we are doing hierarchical queries, we need to trace down all the components before
		// we do our query!

		if ( $this->taxonomy
			&& ($this->taxonomy_term || $this->taxonomy_slug)
			&& $this->taxonomy_depth > 1) {
			$this->taxonomy_term = $this->_append_children_taxonomies($this->taxonomy_term);
		}
		
		// Bump the group_concat_max_len unless the user has selected to manually select it.
		if ( !SummarizePosts::$manually_select_postmeta ) {
			$query = $wpdb->prepare("SET SESSION group_concat_max_len=%d", SummarizePosts::$group_concat_max_len);
			$wpdb->query($query);			
			//TODO: run a simplified query... ugh... all the filters required would suck.
		}
			
		// Execute the big old query.
		$results = $wpdb->get_results( $this->_get_sql(), ARRAY_A );

		if ( $this->paginate ) {
			$this->found_rows = $this->_count_posts();
			include_once 'CCTM_Pagination.conf.php';
			include_once 'CCTM_Pagination.php';
			$this->P = new CCTM_Pagination();
			$this->P->set_base_url( self::get_current_page_url() );
			$this->P->set_offset($this->offset); //
			$this->P->set_results_per_page($this->limit);  // You can optionally expose this to the user.
			$this->pagination_links = $this->P->paginate($this->found_rows); // 100 is the count of records
		}

		foreach ($results as &$r) {
		
			if ( !empty($r['metadata']) ) {
				// Manually grab the data
				if ( SummarizePosts::$manually_select_postmeta ) {
					$r = SummarizePosts::get_post_complete($r['ID']);
				}
				// Parse out the metadata, concat'd by MySQL
				else {
					$caboose = preg_quote(self::caboose);
					$count = 0;
					$r['metadata'] = preg_replace("/$caboose$/", '', $r['metadata'], -1, $count );
					if (!$count) {
						$this->errors[] = __('There was a problem accessing custom fields. Try increasing the <code>group_concat_max_len</code> setting.', CCTM_TXTDOMAIN);
						$r = SummarizePosts::get_post_complete($r['ID']);
					}
					else {
						$pairs = explode( self::comma_separator, $r['metadata'] );
						foreach ($pairs as $p) {
							list($key, $value) = explode(self::colon_separator, $p);
							$r[$key] = $value;
						}
					}
				}
			}

			unset($r['metadata']); // remove the concatenated meta data.

			// Additional Data
			$r['permalink']  = get_permalink( $r['ID'] );
			$r['parent_permalink'] = get_permalink( $r['parent_ID'] );
			// See http://stackoverflow.com/questions/3602941/why-isnt-apply-filterthe-content-outputting-anything
			// $r['the_content']  = get_the_content(); // only works inside the !@#%! loop
			// $r['the_content']  = apply_filters('the_content', $r['post_content']); // this will loop and die if you hit a [summarize_posts] shortcode!
			//$r['the_author'] = get_the_author(); // only works inside the !@#%! loop
			$r['post_id']  = $r['ID'];
		}

		$results = $this->_normalize_recordset($results);

		// Optionally adjust date format (depends on the 'date_format' option)
		$results = $this->_date_format($results);

		return $results;

	}

	//------------------------------------------------------------------------------
	/**
	 * Returns a string of a comparable shortcode for the query entered. Note that 
	 * this technically just shows the difference between the $this->args and the 
	 * $this->defaults settings, so if you used the set_defaults() function to set 
	 * a baseline for the search, the shortcode will come up [summarize-posts].
	 *
	 * @return string
	 */
	public function get_shortcode() {
		$args = array();
		foreach ($this->args as $k => $v) {
			// Only include info if it's not the default... save space and easier to read shortcodes
			if (isset($this->defaults[$k]) && $this->defaults[$k] != $v) { // && (!empty($this->defaults[$k]) && !empty($v))) {
				if ( !empty($v) ) {
					if ( is_array($v) ) {
						$args[] = $k.'="'.implode(',', $v).'"';
					}
					else {
						$args[] = $k.'="'.$v.'"';
					}
				}
			}
			// Direct filtering on a field
			elseif (!isset($this->defaults[$k])) {
				$args[] = $k.'="'.$v.'"';
			}
		}
		
		$args = implode(' ', $args);
		if (!empty($args)) {
			$args = ' '.$args;
		}
		return '[summarize-posts'.$args.']';
	}

	//------------------------------------------------------------------------------
	/**
	 * Gets the raw SQL query
	 *
	 * @return string
	 */
	public function get_sql() {
		return $this->SQL;
	}

	//------------------------------------------------------------------------------
	/**
	 * SYNOPSIS: a simple parsing function for basic templating.
	 * INPUT:
	 * $tpl (str): a string containing [+placeholders+]
	 * $hash (array): an associative array('key' => 'value');
	 * OUTPUT
	 * string; placeholders corresponding to the keys of the hash will be replaced
	 * with the values and the string will be returned.
	 *
	 * @param string  $tpl
	 * @param array   $hash associative array of placeholders => values
	 * @return string
	 */
	public static function parse($tpl, $hash) {
		foreach ($hash as $key => $value) {
			if ( !is_array($value) ) {
				$tpl = str_replace('[+'.$key.'+]', $value, $tpl);
			}
		}

		// Remove any unparsed [+placeholders+]
		$tpl = preg_replace('/\[\+(.*?)\+\]/', '', $tpl);

		return $tpl;
	}

	//------------------------------------------------------------------------------
	/**
	 * Format any errors in an unordered list, or returns a message saying there were no errors.
	 *
	 * @return string message detailing errors.
	 */
	public function get_warnings() {

		$output = '';
		
		$warnings = $this->warnings;
		
		if ($warnings) {
			$items = '';
			foreach ($warnings as $w) {
				$items .= '<li>'.$w.'</li>' ."\n";
			}
			$output = '<ul>'."\n".$items.'</ul>'."\n";
		}
		else {
			$output = __('There were no warnings.', CCTM_TXTDOMAIN);
		}
		return sprintf('<h2>%s</h2><div class="summarize-posts-warnings">%s</div>'
			, __('Warnings', CCTM_TXTDOMAIN)
			, $output);
	}

	//------------------------------------------------------------------------------
	/**
	 * This sets a default value for any field.  This should kick in only if the
	 * field is empty when we normalize the recordset in the _normalize_recordset
	 * function
	 *
	 * @param string  $fieldname name of the field whose default value you want to set
	 * @param string  $value     the value to set the attribute to
	 */
	public function set_default($fieldname, $value) {
		$this->custom_default_values[(string)$fieldname] = (string) $value;
	}


	//------------------------------------------------------------------------------
	/**
	 * Sets defaults for the query: defaults are just like arguments, but for cleaner
	 * UX, we can set some default arguments so users don't always have to provide every
	 * stinking detail.  These defaults kick in when a search parameter
	 * is left empty. This is especially useful for setting up limits to parameters like
	 * 'post_type', which if empty, will display ALL post-types from the db, registered
	 * AND unregistered or 'post_status' -- usually users want only 'published'.
	 *
	 * By setting the optional 2nd parameter, you can overwrite the entire defaults array.
	 * Default behavior is to "merge" the arguments.
	 *
	 * @param	array	parameters corresponding to the various search parameters
	 * @param	boolean	(optional) $overwrite: if true
	 */
	public function set_defaults($args, $overwrite=false) {
		
		$args = (array) $args;
		$overwrite = (bool) $overwrite;
		
		if ($overwrite) {
			$this->defaults = $args;		
			foreach ($args as $k => $v) {
				$this->$k = $v; // set the args
			}
		}
		else {
			foreach ($args as $k => $v) {
				$this->defaults[$k] = $v;
				$this->$k = $v; // set the args
			}
		}		
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Should hidden fields be included in the results?
	 * @param	boolean	yes or no.
	 */
	public function set_include_hidden_fields($yn) {
		$this->include_hidden_fields = (bool) $yn;
	}
}


/*EOF*/