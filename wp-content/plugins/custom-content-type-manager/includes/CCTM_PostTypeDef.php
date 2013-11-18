<?php
/**
 * Library used by the create_post_type.php and edit_post_type.php controllers
 */
class CCTM_PostTypeDef {

	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @return string representing all img tags for all post-type icons
	 */
	public static function get_post_type_icons() {

		$icons = array();
		if ($handle = opendir(CCTM_PATH.'/images/icons/16x16')) {
			while (false !== ($file = readdir($handle))) {
				if ( !preg_match('/^\./', $file) ) {
					$icons[] = $file;
				}
			}
			closedir($handle);
		}

		$output = '';

		foreach ( $icons as $img ) {
			$output .= sprintf('
				<span class="cctm-icon">
					<img src="%s" title="%s" onclick="javascript:send_to_menu_icon(\'%s\');"/>
				</span>'
				, CCTM_URL.'/images/icons/32x32/'.$img
				, $img
				, CCTM_URL.'/images/icons/16x16/'.$img
			);
		}

		return $output;
	}


	//------------------------------------------------------------------------------
	/**
	 * SYNOPSIS: checks the custom content data array to see $post_type exists as one
	 * of CCTM's defined post types (it doesn't check against post types defined
	 * elsewhwere).
	 *
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/wiki/DataStructures
	 *
	 * Built-in post types 'page' and 'post' are considered valid (i.e. existing) by
	 * default, even if they haven't been explicitly defined for use by this plugin
	 * so long as the 2nd argument, $search_built_ins, is not overridden to false.
	 * We do this because sometimes we need to consider posts and pages, and other times
	 * not.
	 *
	 * $built_in_post_types array.
	 *
	 * @param string  $post_type        the lowercase database slug identifying a post type.
	 * @param boolean $search_built_ins (optional) whether or not to search inside the
	 * @return boolean indicating whether this is a valid post-type
	 */
	public static function is_existing_post_type($post_type, $search_built_ins=true) {

		// If there is no existing data, check against the built-ins
		if ( empty(CCTM::$data['post_type_defs']) && $search_built_ins ) {
			return in_array($post_type, CCTM::$built_in_post_types);
		}
		// If there's no existing $data and we omit the built-ins...
		elseif ( empty(CCTM::$data['post_type_defs']) && !$search_built_ins ) {
			return false;
		}
		// Check to see if we've stored this $post_type before
		elseif ( array_key_exists($post_type, CCTM::$data['post_type_defs']) ) {
			return true;
		}
		// Check the built-ins
		elseif ( $search_built_ins && in_array($post_type, CCTM::$built_in_post_types) ) {
			return true;
		}
		else {
			return false;
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Check for errors: ensure that $post_type is a valid post_type name.
	 *
	 * @param mixed   $data describes a post type (this will be input to the register_post_type() function
	 * @param boolean $new  (optional) whether or not the post_type is new (default=false)
	 * @return mixed  returns null if there are no errors, otherwise returns a string describing an error.
	 */
	public static function post_type_name_has_errors($data, $new=false) {

		$errors = null;

		$taxonomy_names_array = get_taxonomies('', 'names');

		if ( empty($data['post_type']) ) {
			return __('Name is required.', CCTM_TXTDOMAIN);
		}
		if ( empty($data['labels']['menu_name'])) // remember: the location in the $_POST array is different from the name of the option in the form-def.
			{
			return __('Menu Name is required.', CCTM_TXTDOMAIN);
		}

		foreach ( CCTM::$reserved_prefixes as $rp ) {
			if ( preg_match('/^'.preg_quote($rp).'.*/', $data['post_type']) ) {
				return sprintf( __('The post type name cannot begin with %s because that is a reserved prefix.', CCTM_TXTDOMAIN)
					, $rp);
			}
		}

		$registered_post_types = get_post_types();
		$cctm_post_types = array_keys(CCTM::$data['post_type_defs']);
		$other_post_types = array_diff($registered_post_types, $cctm_post_types);
		$other_post_types = array_diff($other_post_types, CCTM::$reserved_post_types);

		// Is reserved name?
		if ( in_array($data['post_type'], CCTM::$reserved_post_types) ) {
			$msg = __('Please choose another name.', CCTM_TXTDOMAIN );
			$msg .= ' ';
			$msg .= sprintf( __('%s is a reserved name.', CCTM_TXTDOMAIN )
				, '<strong>'.$post_type.'</strong>' );
			return $msg;
		}
		// Make sure the post-type name does not conflict with any registered taxonomies
		elseif ( in_array( $data['post_type'], $taxonomy_names_array) ) {
			$msg = __('Please choose another name.', CCTM_TXTDOMAIN );
			$msg .= ' ';
			$msg .= sprintf( __('%s is already in use as a registered taxonomy name.', CCTM_TXTDOMAIN)
				, $post_type );
			return $msg;
		}
		// If this is a new post_type or if the $post_type name has been changed,
		// ensure that it is not going to overwrite an existing post type name.
		elseif ( $new && is_array(CCTM::$data['post_type_defs']) && in_array($data['post_type'], $cctm_post_types ) ) {
			return sprintf( __('The name %s is already in use.', CCTM_TXTDOMAIN), htmlspecialchars($data['post_type']) );
		}
		// Is the name taken by an existing post type registered by some other plugin?
		elseif (in_array($data['post_type'], $other_post_types) ) {
			return sprintf( __('The name %s has been registered by some other plugin.', CCTM_TXTDOMAIN), htmlspecialchars($data['post_type']) );
		}
		// Make sure there's not an unsuspecting theme file named single-my_post_type.php
		/*
		$dir = get_stylesheet_directory();
		if ( file_exists($dir . '/single-'.$data['post_type'].'.php')) {
			return sprintf( __('There is a template file named single-%s.php in your theme directory (%s).', CCTM_TXTDOMAIN)
				, htmlspecialchars($data['post_type'])
				, get_stylesheet_directory());
		}
*/

		return; // no errors
	}

	//------------------------------------------------------------------------------
	/**
	 * Everything when creating a new post type must be filtered here.
	 *
	 * Problems with:
	 *  hierarchical
	 *  rewrite_with_front
	 *
	 * This is janky... sorta doesn't work how it's supposed when combined with save_post_type_settings().
	 *
	 *
	 * @param mixed   $raw unsanitized $_POST data
	 * @return mixed filtered $_POST data (only white-listed are passed thru to output)
	 */
	public static function sanitize_post_type_def($raw) {
		$sanitized = array();

		unset($raw['custom_content_type_mgr_create_new_content_type_nonce']);
		unset($raw['custom_content_type_mgr_edit_content_type_nonce']);

		$raw = CCTM::striptags_deep(($raw));

		// WP always adds slashes: see http://kovshenin.com/archives/wordpress-and-magic-quotes/
		$raw = CCTM::stripslashes_deep(($raw));


		
		// Handle unchecked checkboxes
		if ( empty($raw['cctm_hierarchical_custom'])) {
			$sanitized['cctm_hierarchical_custom'] = '';
		}
		if ( empty($raw['cctm_hierarchical_includes_drafts'])) {
			$sanitized['cctm_hierarchical_includes_drafts'] = '';
		}
		if ( empty($raw['cctm_hierarchical_post_types'])) {
			$sanitized['cctm_hierarchical_post_types'] = array();
		}

		// This will be empty if no "supports" items are checked.
		if (!empty($raw['supports']) ) {
			$sanitized['supports'] = $raw['supports'];
			unset($raw['supports']);
		}
		else {
			$sanitized['supports'] = array();
		}

		if (!empty($raw['taxonomies']) ) {
			$sanitized['taxonomies'] = $raw['taxonomies'];
		}
		else {
			// do this so this will take precedence when you merge the existing array with the new one in the save_post_type_settings() function.
			$sanitized['taxonomies'] = array();
		}
		// You gotta unset arrays if you want the foreach thing below to work.
		unset($raw['taxonomies']);

		// Temporary thing... ????
		unset($sanitized['rewrite_slug']);

		// The main event
		// We grab everything except stuff that begins with '_', then override specific $keys as needed.
		foreach ($raw as $key => $value ) {
			if ( !preg_match('/^_.*/', $key) ) {
				$sanitized[$key] = CCTM::get_value($raw, $key);
			}
		}

		// Specific overrides below:
		$sanitized['description'] = strip_tags($raw['description']);
		
		// post_type is the only required field
		$sanitized['post_type'] = CCTM::get_value($raw, 'post_type');
		$sanitized['post_type'] = strtolower($sanitized['post_type']);
		$sanitized['post_type'] = preg_replace('/[^a-z0-9_\-]/', '_', $sanitized['post_type']);
		$sanitized['post_type'] = substr($sanitized['post_type'], 0, 20);

		// Our form passes integers and strings, but WP req's literal booleans,
		// so we do some type-casting here to ensure literal booleans.
		$sanitized['public']    = (bool) CCTM::get_value($raw, 'public');
		$sanitized['rewrite_with_front']     = (bool) CCTM::get_value($raw, 'rewrite_with_front');
		$sanitized['show_ui']     = (bool) CCTM::get_value($raw, 'show_ui');
		$sanitized['public']     = (bool) CCTM::get_value($raw, 'public');
		$sanitized['show_in_nav_menus']  = (bool) CCTM::get_value($raw, 'show_in_nav_menus');
		$sanitized['can_export']    = (bool) CCTM::get_value($raw, 'can_export');
		$sanitized['use_default_menu_icon'] = (bool) CCTM::get_value($raw, 'use_default_menu_icon');
		$sanitized['hierarchical']    = (bool) CCTM::get_value($raw, 'hierarchical');
		$sanitized['include_in_search']    = (bool) CCTM::get_value($raw, 'include_in_search');
		$sanitized['publicly_queryable']    = (bool) CCTM::get_value($raw, 'publicly_queryable');
		$sanitized['include_in_rss']    = (bool) CCTM::get_value($raw, 'include_in_rss');

		if ( empty($sanitized['has_archive']) ) {
			$sanitized['has_archive'] = false;
		}
		else {
			$sanitized['has_archive'] = true;
		}

		// *facepalm*... Special handling req'd here for menu_position because 0
		// is handled differently than a literal null.
		if ( (int) CCTM::get_value($raw, 'menu_position') ) {
			$sanitized['menu_position'] = (int) CCTM::get_value($raw, 'menu_position', null);
		}
		else {
			$sanitized['menu_position'] = null;
		}
		$sanitized['show_in_menu']    = CCTM::get_value($raw, 'show_in_menu');

		$sanitized['cctm_show_in_menu']    = CCTM::get_value($raw, 'cctm_show_in_menu');


		// menu_icon... the user will lose any custom Menu Icon URL if they save with this checked!
		// TODO: let this value persist.
		if ( $sanitized['use_default_menu_icon'] ) {
			unset($sanitized['menu_icon']); // === null;
		}

		if (empty($sanitized['query_var'])) {
			$sanitized['query_var'] = false;
		}

		// Cleaning up the labels
		if ( empty($sanitized['label']) ) {
			$sanitized['label'] = ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['singular_name']) ) {
			$sanitized['labels']['singular_name'] = ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['add_new']) ) {
			$sanitized['labels']['add_new'] = __('Add New');
		}
		if ( empty($sanitized['labels']['add_new_item']) ) {
			$sanitized['labels']['add_new_item'] = __('Add New') . ' ' .ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['edit_item']) ) {
			$sanitized['labels']['edit_item'] = __('Edit'). ' ' .ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['new_item']) ) {
			$sanitized['labels']['new_item'] = __('New'). ' ' .ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['view_item']) ) {
			$sanitized['labels']['view_item'] = __('View'). ' ' .ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['search_items']) ) {
			$sanitized['labels']['search_items'] = __('Search'). ' ' .ucfirst($sanitized['labels']['menu_name']);
		}
		if ( empty($sanitized['labels']['not_found']) ) {
			$sanitized['labels']['not_found'] = sprintf( __('No %s found', CCTM_TXTDOMAIN), strtolower($raw['labels']['menu_name']) );
		}
		if ( empty($sanitized['labels']['not_found_in_trash']) ) {
			$sanitized['labels']['not_found_in_trash'] = sprintf( __('No %s found in trash', CCTM_TXTDOMAIN), strtolower($raw['labels']['menu_name']) );
		}
		if ( empty($sanitized['labels']['parent_item_colon']) ) {
			$sanitized['labels']['parent_item_colon'] = __('Parent Page');
		}


		// Rewrites. TODO: make this work like the built-in post-type permalinks
		switch ($sanitized['permalink_action']) {
		case '/%postname%/':
			$sanitized['rewrite'] = true;
			break;
		case 'Custom':
			$sanitized['rewrite']['slug'] = $raw['rewrite_slug'];
			$sanitized['rewrite']['with_front'] = (bool) $raw['rewrite_with_front'];
			break;
		case 'Off':
		default:
			$sanitized['rewrite'] = false;
		}

		return $sanitized;
	}


	//------------------------------------------------------------------------------
	/**
	 * this saves a serialized data structure (arrays of arrays) to the db
	 *
	 * @return
	 * @param mixed   $def associative array definition describing a single post-type.
	 */
	public static function save_post_type_settings($def) {

		$key = $def['post_type'];

		unset(CCTM::$data['post_type_defs'][$key]['original_post_type_name']);

		// Update existing settings if this post-type has already been added
		if ( isset(CCTM::$data['post_type_defs'][$key]) ) {
			CCTM::$data['post_type_defs'][$key] = array_merge(CCTM::$data['post_type_defs'][$key], $def);
		}
		// OR, create a new node in the data structure for our new post-type
		else {
			CCTM::$data['post_type_defs'][$key] = $def;
		}
		if (CCTM::$data['post_type_defs'][$key]['use_default_menu_icon']) {
			unset(CCTM::$data['post_type_defs'][$key]['menu_icon']);
		}

		update_option( CCTM::db_key, CCTM::$data );
	}
}
/*EOF*/