<?php
/**
 * CCTM = Custom Content Type Manager
 * 
 * This is the main class for the Custom Content Type Manager plugin.
 * It holds its functions hooked to WP events and utilty functions and configuration
 * settings.
 * 
 * Homepage:
 * http://code.google.com/p/wordpress-custom-content-type-manager/
 * 
 * This plugin handles the creation and management of custom post-types (also
 * referred to as 'content-types').
 * 
 * @package cctm
 */
class CCTM {
	// Name of this plugin and version data.
	// See http://php.net/manual/en/function.version-compare.php:
	// any string not found in this list < dev < alpha =a < beta = b < RC = rc < # < pl = p
	const name   = 'Custom Content Type Manager';
	const version = '0.9.5.6';
	const version_meta = 'pl'; // dev, rc (release candidate), pl (public release)

	// Required versions (referenced in the CCTMtest class).
	const wp_req_ver  = '3.3';
	const php_req_ver  = '5.2.6';
	const mysql_req_ver = '4.1.2';

	/**
	 * The following constants identify the option_name in the wp_options table
	 * where this plugin stores various data.
	 */
	const db_key  = 'cctm_data';

	/**
	 * Determines where the main CCTM menu appears. WP is vulnerable to conflicts
	 * with menu items, so the parameter is listed here for easier editing.
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=203
	 */
	const menu_position = 73;

	// Each class that extends either the CCTM_FormElement class or the
	// the CCTM_OutputFilter class must prefix this to its class name.
	const classname_prefix = 'CCTM_';

	// used to control the uploading of the .cctm.json files
	const max_def_file_size = 524288; // in bytes

	// Directory relative to wp-content/uploads/ where we can store def files
	// Omit the trailing slash.
	const base_storage_dir = 'cctm';

	/**
	 * Directory relative to wp-content/uploads/{self::base_storage_dir} used to store
	 * the .cctm.json definition files. Omit the trailing slash.
	 */
	const def_dir = 'defs';



	/**
	 * Directory relative to wp-content/uploads/{self::base_storage_dir} used to store
	 * any 3rd-party or custom custom field types. Omit the trailing slash.
	 */
	const custom_fields_dir = 'fields';


	/**
	 * Directory relative to wp-content/uploads/{self::base_storage_dir} used to store
	 * formatting templates (tpls)
	 * May contain the following sub directories: fields, fieldtypes, metaboxes
	 */
	const tpls_dir = 'tpls';

	// Default permissions for dirs/files created in the base_storage_dir.
	// These cannot be more permissive thant the system's settings: the system
	// will automatically shave them down. E.g. if the system has a global setting
	// of 0755, a local setting here of 0770 gets bumped down to 0750.
	const new_dir_perms = 0755;
	const new_file_perms = 0644;

	//------------------------------------------------------------------------------
	/**
	 * This contains the CCTM_Ajax object, stashed here for easy reference.
	 */
	public static $Ajax;

	// Used to filter settings inputs (e.g. descriptions of custom fields or post-types)
	public static $allowed_html_tags = '<a><strong><em><code><style>';

	// Data object stored in the wp_options table representing all primary data
	// for post_types and custom fields
	public static $data = array();

	// integer iterator used to uniquely identify groups of field definitions for
	// CSS and $_POST variables
	public static $def_i = 0;

	// This is the definition shown when a user first creates a post_type
	public static $default_post_type_def = array
	(
		'supports' => array('title', 'editor'),
		'taxonomies' => array(),
		'post_type' => '',
		'labels' => array
		(
			'menu_name' => '',
			'singular_name' => '',
			'add_new' => '',
			'add_new_item' => '',
			'edit_item' => '',
			'new_item' => '',
			'view_item' => '',
			'search_items' => '',
			'not_found' => '',
			'not_found_in_trash' => '',
			'parent_item_colon' => '',
		),
		'description' => '',
		'show_ui' => 1,
		'public' => 1, // 0.9.4.2 tried to set this verbosely, but WP still req's this attribute
		'menu_icon' => '',
		'label' => '',
		'menu_position' => '',
		'show_in_menu' => 1,

		'rewrite_with_front' => 1,
		'permalink_action' => 'Off',
		'rewrite_slug' => '',
		'query_var' => '',
		'capability_type' => 'post',
		'show_in_nav_menus' => 1,
		'publicly_queryable' => 1,
		'include_in_search' => 1, // this makes more sense to users than the exclude_from_search,
		'exclude_from_search' => 0, // but this is what register_post_type expects. Boo.
		'include_in_rss' => 1,  // this is a custom option
		'can_export' => 1,
		'use_default_menu_icon' => 1,
		'hierarchical' => 0,
		'rewrite' => '',
		'has_archive' => 0,
		'custom_order' => 'ASC',
		'custom_orderby' => '',
	);

	/**
	 * List default settings here. (checkboxes only)
	 */
	public static $default_settings = array(
		'delete_posts' => 0
		, 'delete_custom_fields' => 0
		, 'add_custom_fields' => 0
		, 'update_custom_fields' => 0
		, 'show_custom_fields_menu' => 1
		, 'show_settings_menu' => 1
		, 'show_foreign_post_types' => 1
		, 'cache_directory_scans' => 1
		, 'cache_thumbnail_images' => 0
		, 'save_empty_fields' => 1
	);

	// Where are the icons for custom images stored?
	// TODO: let the users select their own dir in their own directory
	public static $custom_field_icons_dir;

	// Built-in post-types that can have custom fields, but cannot be deleted.
	public static $built_in_post_types = array('post', 'page');

	// Names that are off-limits for custom post types b/c they're already used by WP
	public static $reserved_post_types = array('post', 'page', 'attachment', 'revision'
		, 'nav_menu', 'nav_menu_item');

	// Custom field names are not allowed to use the same names as any column in wp_posts
	public static $reserved_field_names = array('ID', 'post_author', 'post_date', 'post_date_gmt',
		'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status',
		'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt',
		'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type',
		'comment_count');

	// Future-proofing: post-type names cannot begin with 'wp_'
	// See: http://codex.wordpress.org/Custom_Post_Types
	// FUTURE: List any other reserved prefixes here (if any)
	public static $reserved_prefixes = array('wp_');

	/**
	 * Warnings are stored as a simple array of text strings, e.g. 'You spilled your coffee!'
	 * Whether or not they are displayed is determined by checking against the self::$data['warnings']
	 * array: the text of the warning is hashed and this is used as a key to identify each warning.
	 */
	public static $warnings = array();



	/**
	 * used to store validation errors. The errors take this format:
	 * self::$errors['field_name'] = 'Description of error';
	 */
	public static $errors;

	/**
	 * Used for search parameters
	 */
	public static $search_by = array();

	/**
	 * Used by the image, media, relation post-selector.
	 */
	public static $post_selector = array();

	//! Private Functions
	//------------------------------------------------------------------------------
	/**
	 * Returns a URL to a thumbnail image.  Attempts to create and cache the image;
	 * we just return the path to the full-sized image if we fail to cache it (which
	 * is what WP does.
	 * See See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=256
	 * 
	 * @param array $p post array from WP's get_post ARRAY_A
	 * @return string	thumbnail_url
	 */
	private static function _get_create_thumbnail($p) {
		// Custom handling of images. 
		$WIDTH = 32;
		$HEIGHT = 32;
		$QUALITY = 100;


		// Base image cache dir: our jumping off point.
		$cache_dir = CCTM_3P_PATH .'/cache/images/';
		$info = pathinfo($p['guid']);
		//$ext = '.'.$info['extension'];
		$ext = '.jpg';
		
		$hash_id = md5($p['guid'].$WIDTH.$HEIGHT.$QUALITY); //
		//$hash_id = md5(print_r($p,true).$WIDTH.$HEIGHT.$QUALITY); //
		
		// atomize our image so we don't overload our directories (shell wildcards)
		// See http://drupal.org/node/171444 for one example of this common problem
		$subdir_array = str_split($hash_id);
		$filename = array_pop($subdir_array); // the last letter
		$subdir = implode('/', $subdir_array); // e.g. a/b/c/1/5/e
		// The image location is relative to the cache/images directory
		$image_location = $subdir.'/'.$filename.$ext; // e.g. a/b/c/1/5/e/f.jpg
		
		
		$thumbnail_path = CCTM_3P_PATH .'/cache/images/'.$image_location;
		$thumbnail_url = CCTM_3P_URL .'/cache/images/'.$image_location;


		// If it's already there, we're done
		if (file_exists($thumbnail_path)) {
			return $thumbnail_url;
		}


		// If it's not there, we must create it.
		if (!file_exists($cache_dir.$subdir) && !mkdir($cache_dir.$subdir, 0777, true)) {

			// Notify the user
			CCTM::$errors['could_not_create_cache_dir'] = sprintf(
				__('Could not create the cache directory at %s.', CCTM_TXTDOMAIN)
				, "<code>$cache_dir</code>. Please create the directory with permissions so PHP can write to it.");

			$myFile = "/tmp/cctm.txt";
			$fh = fopen($myFile, 'a') or die("can't open file");
			fwrite($fh, 'Failed to create directory '.$cache_dir.$subdir."\n");
			fclose($fh);	


			// Failed to create the dir... now what?!?  We cram the full-sized image into the 
			// small image tag, which is exactly what WP does (yes, seriously.)				
			return $p['guid'];
				
		}
		
		// the cache directory exits; create the cached image
		require_once(CCTM_PATH.'/includes/CCTM_SimpleImage.php');
		$image = new CCTM_SimpleImage();
		$image->load($p['guid']); // You may use the image URL
		$image->resize($WIDTH, $HEIGHT);
		if (!$image->save($thumbnail_path, IMAGETYPE_JPEG, $QUALITY)) {
			CCTM::$errors['could_not_create_img'] = sprintf(
				__('Could not create cached image: %s.', CCTM_TXTDOMAIN)
				, "<code>$thumbnail_path</code>");

			$myFile = "/tmp/cctm.txt";
			$fh = fopen($myFile, 'a') or die("can't open file");
			fwrite($fh, 'Could not save the image '.$thumbnail_path."\n");
			fclose($fh);
			
			return $p['guid'];
			
		}
		
		return $thumbnail_url;
	}
	
	
	//------------------------------------------------------------------------------
	/**
	 * Prepare a post type definition for registration.  This gets run immediately 
	 * before the register_post_type() function is called.  It allows us to abstract 
	 * what WP gets from the stored definition a bit.
	 *
	 * @param mixed   the CCTM definition for a post type
	 * @param unknown $def
	 * @return mixed  the WordPress authorized definition format.
	 */
	private static function _prepare_post_type_def($def) {
		// Sigh... working around WP's irksome inputs
		if (isset($def['cctm_show_in_menu']) && $def['cctm_show_in_menu'] == 'custom') {
			$def['show_in_menu'] = $def['cctm_show_in_menu_custom'];
		}
		else {
			$def['show_in_menu'] = (bool) self::get_value($def, 'cctm_show_in_menu');
		}
		// We display "include" type options to the user, and here on the backend
		// we swap this for the "exclude" option that the function requires.
		$include = self::get_value($def, 'include_in_search');

		if (empty($include)) {
			$def['exclude_from_search'] = true;
		}
		else {
			$def['exclude_from_search'] = false;
		}

		// TODO: retro-support... if public is checked, then the following options are inferred
		/*
		if (isset($def['public']) && $def['public']) {
			$def['publicly_queriable'] = true;
			$def['show_ui'] = true;
			$def['show_in_nav_menus'] = true;
			$def['exclude_from_search'] = false;
		}
		*/

		// Verbosely check to see if "public" is inferred
		if (isset($def['publicly_queriable']) && $def['publicly_queriable']
			&& isset($def['show_ui']) && $def['show_ui']
			&& isset($def['show_in_nav_menus']) && $def['show_in_nav_menus']
			&& (!isset($def['exclude_from_search']) || (isset($def['exclude_from_search']) && !$def['publicly_queriable']))
		) {
			$def['public'] = true;
		}

		unset($def['custom_orderby']);

		return $def;
	}




	//! Public Functions
	//------------------------------------------------------------------------------
	/**
	 * Load CSS and JS for admin folks in the manager.  Note that we have to verbosely
	 * ensure that thickbox's css and js are loaded: normally they are tied to the
	 * "editor" area of the content type, so thickbox would otherwise fail
	 * if your custom post_type doesn't use the main editor.
	 * See http://codex.wordpress.org/Function_Reference/wp_enqueue_script for a list
	 * of default scripts bundled with WordPress
	 */
	public static function admin_init() {

		load_plugin_textdomain( CCTM_TXTDOMAIN, false, CCTM_PATH.'/lang/' );

		$file = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/')+1);
		$page = self::get_value($_GET, 'page');

		// Only add our junk if we are creating/editing a post or we're on
		// on of our CCTM pages
		if ( in_array($file, array('post.php', 'post-new.php', 'edit.php')) || preg_match('/^cctm.*/', $page) ) {

			wp_register_style('CCTM_css', CCTM_URL . '/css/manager.css');
			wp_enqueue_style('CCTM_css');
			// Hand-holding: If your custom post-type omits the main content block,
			// then thickbox will not be queued and your image, reference, selectors will fail.
			// Also, we have to fix the bugs with WP's thickbox.js, so here we include a patched file.
			wp_register_script('cctm_thickbox', CCTM_URL . '/js/thickbox.js', array('thickbox') );
			wp_enqueue_script('cctm_thickbox');
			wp_enqueue_style('thickbox' );

			wp_enqueue_style('jquery-ui-tabs', CCTM_URL . '/css/smoothness/jquery-ui-1.8.11.custom.css');
			wp_enqueue_script('jquery-ui-tabs');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-dialog');

			wp_enqueue_script('cctm_manager', CCTM_URL . '/js/manager.js' );
			//wp_enqueue_script('cctm_manager', CCTM_URL . '/js/summarize_posts.js' );

			// The following makes PHP variables available to Javascript the "correct" way.
			// See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=226
			$data = array();
			$data['cctm_url'] = CCTM_URL;
			$data['ajax_url'] = admin_url( 'admin-ajax.php' );
			$data['ajax_nonce'] = wp_create_nonce('ajax_nonce');			
			wp_localize_script( 'cctm_manager', 'cctm', $data );

		}

		// Allow each custom field to load up any necessary CSS/JS.
		self::initialize_custom_fields();
	}


	//------------------------------------------------------------------------------
	/**
	 * Adds a link to the settings directly from the plugins page.  This filter is
	 * called for each plugin, so we need to make sure we only alter the links that
	 * are displayed for THIS plugin.
	 *
	 * INPUTS (determined by WordPress):
	 *   array('deactivate' => 'Deactivate')
	 * relative to the plugins directory, e.g. 'custom-content-type-manager/index.php'
	 *
	 * @param array   $links is a hash of links to display in the format of name => translation e.g.
	 * @param string  $file  is the path to plugin's main file (the one with the info header),
	 * @return array $links
	 */
	public static function add_plugin_settings_link($links, $file) {
		if ( $file == basename(self::get_basepath()) . '/index.php' ) {
			$settings_link = sprintf('<a href="%s">%s</a>'
				, admin_url( 'admin.php?page=cctm' )
				, __('Settings')
			);
			array_unshift( $links, $settings_link );
		}

		return $links;
	}


	//------------------------------------------------------------------------------
	/**
	 * Solves the problem with encodings.  On many servers, the following won't work:
	 *
	 *   print 'ę'; // prints Ä™
	 *
	 * But this function solves it by converting the characters into appropriate html-entities:
	 *
	 *   print charset_decode_utf_8('ę');
	 *
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=88
	 * Solution from Squirrelmail, see http://pa2.php.net/manual/en/function.utf8-decode.php
	 *
	 * @param string $string
	 * @return string
	 */
	public static function charset_decode_utf_8($string) {
		$string = htmlspecialchars($string); // htmlentities will NOT work here.

		/* Only do the slow convert if there are 8-bit characters */
		/* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */
		if (! preg_match("/[\200-\237]/", $string) and ! preg_match("/[\241-\377]/", $string)) {
			return $string;
		}

		// decode three byte unicode characters
		$string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e", "'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'", $string);

		// decode two byte unicode characters
		$string = preg_replace("/([\300-\337])([\200-\277])/e", "'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'", $string);

		return $string;
	}


	//------------------------------------------------------------------------------
	/**
	 * WordPress lacks an "onUpdate" event, so this is a home-rolled way I can run
	 * a specific bit of code when a new version of the plugin is installed. The way
	 * this works is the names of all files inside of the updates/ folder are loaded
	 * into an array, e.g. 0.9.4, 0.9.5.  When the first new page request comes through
	 * WP, the database is in the old state, whereas the code is new, so the database
	 * will say e.g. that the plugin version is 0.1 and the code will say the plugin version
	 * is 0.2.  All the available updates are included and their contents are executed
	 * in order.  This ensures that all update code is run sequentially.
	 *
	 * Any version prior to 0.9.4 is considered "version 0" by this process.
	 *
	 */
	public static function check_for_updates() {
		
		// If it's not a new install, we check for updates
		if ( version_compare( self::get_stored_version(), self::get_current_version(), '<' ) ) {
			// set the flag
			define('CCTM_UPDATE_MODE', 1);
			// Load up available updates in order (scandir will sort the results automatically)
			$updates = scandir(CCTM_PATH.'/updates');
			foreach ($updates as $file) {
				// Skip the gunk
				if ($file === '.' || $file === '..') continue;
				if (is_dir(CCTM_PATH.'/updates/'.$file)) continue;
				if (substr($file, 0, 1) == '.') continue;
				// skip non-php files
				if (pathinfo(CCTM_PATH.'/updates/'.$file, PATHINFO_EXTENSION) != 'php') continue;

				// We don't want to re-run older updates
				$this_update_ver = substr($file, 0, -4);
				if ( version_compare( self::get_stored_version(), $this_update_ver, '<' ) ) {
					// Run the update by including the file
					include CCTM_PATH.'/updates/'.$file;
					// timestamp the update
					self::$data['cctm_update_timestamp'] = time(); // req's new data structure
					// store the new version after the update
					self::$data['cctm_version'] = $this_update_ver; // req's new data structure
					update_option( self::db_key, self::$data );
				}
			}
		}

		// If this is empty, then it is a first install, so we timestamp it
		// and prep the data structure
		if (empty(CCTM::$data)) {
			// TODO: run tests
			CCTM::$data['cctm_installation_timestamp'] = time();
			CCTM::$data['cctm_version'] = CCTM::get_current_version();
			CCTM::$data['export_info'] = array(
				'title'   => 'CCTM Site',
				'author'   => get_option('admin_email', ''),
				'url'    => get_option('siteurl', 'http://wpcctm.com/'),
				'description' => __('This site was created in part using the Custom Content Type Manager', CCTM_TXTDOMAIN),
			);
			update_option(CCTM::db_key, CCTM::$data);
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Create custom post-type menu.  This should only be visible to
	 * admin users (single-sites) or the super_admin users (multi-site).
	 *
	 * See http://codex.wordpress.org/Administration_Menus
	 * http://wordpress.org/support/topic/plugin-custom-content-type-manager-multisite?replies=18#post-2501711
	 */
	public static function create_admin_menu() {
		self::load_file('/config/menus/admin_menu.php');
	}


	/**
	 * Delete a directoroy and its contents.
	 * @param	string $dirPath
	 */
	public static function delete_dir($dirPath) {
	    if (! is_dir($dirPath)) {
	    	return false;
//	        throw new InvalidArgumentException('$dirPath must be a directory');
	    }
	    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
	        $dirPath .= '/';
	    }
	    $files = glob($dirPath . '*', GLOB_MARK);
	    foreach ($files as $file) {
	        if (is_dir($file)) {
	            self::delete_dir($file);
	        } else {
	            unlink($file);
	        }
	    }
	    rmdir($dirPath);
	}

	//------------------------------------------------------------------------------
	/**
	 * The static invocation of filtering an input through an Output Filter
	 *
	 * @param mixed $value to be filtered, usually a string.
	 * @param string $outputfilter name, e.g. 'to_array'
	 * @param mixed $options (optional) any additional arguments to pass to the filter
	 * @return mixed dependent on output filter
	 */
	public static function filter($value, $outputfilter, $options=null) {
	
		$filter_class = CCTM::classname_prefix.$outputfilter;

		require_once CCTM_PATH.'/includes/CCTM_OutputFilter.php';
		
		// If we've already loaded it, re-use it
		if (class_exists($filter_class)) { 		
			$OutputFilter = new $filter_class();
			return $OutputFilter->filter($value, $options);
		}
		
		// Load the file if we haven't already
		if (CCTM::load_file(array("/filters/$outputfilter.php"))) {
		
			// This checks if the file implemented the correct class 
			if ( !class_exists($filter_class) ) {
				self::$errors['incorrect_classname'] = sprintf( __('Incorrect class name in %s Output Filter. Expected class name: %s', CCTM_TXTDOMAIN)
					, "<strong>$outputfilter</strong>"
					, "<strong>$filter_class</strong>"
				);
				return $value;
			}
			// Ok, we've loaded the right class... let's use it.
			$OutputFilter = new $filter_class();
			return $OutputFilter->filter($value, $options);
						
		}		
		else {
			self::$errors['filter_not_found'] = sprintf(
				__('Output filter not found: %s', CCTM_TXTDOMAIN)
				, "<code>$outputfilter</code>");
			return $value;
		}


	}


	//------------------------------------------------------------------------------
	/**
	 * Adds formatting to a string to make an "error" message.
	 *
	 * @param string $msg localized error message
	 * @return string
	 */
	public static function format_error_msg($msg) {
		return sprintf('<div class="error"><p>%s</p></div>', $msg);
	}


	//------------------------------------------------------------------------------
	/**
	 * Adds formatting to a string to make an "updated" message.
	 *
	 * @param string $msg localized message
	 * @return string
	 */
	public static function format_msg($msg) {
		return sprintf('<div class="updated"><p>%s</p></div>', $msg);
	}
	
	//------------------------------------------------------------------------------
	/**
	 * This formats any errors registered in the class $errors array. The errors
	 * take this format: self::$errors['field_name'] = 'Description of error';
	 *
	 * @return string (empty string if no errors)
	 */
	public static function format_errors() {
		$error_str = '';
		if ( empty ( self::$errors ) ) {
			return '';
		}

		foreach ( self::$errors as $e ) {
			$error_str .= '<li>'.$e.'</li>
			';
		}

		return sprintf('<div class="error">
			<p><strong>%1$s</strong></p>
			<ul style="margin-left:30px">
				%2$s
			</ul>
			</div>'
			, __('Please correct the following errors:', CCTM_TXTDOMAIN)
			, $error_str
		);
	}

	//------------------------------------------------------------------------------
	/**
	 * Returns an array of active post_types (i.e. ones that will a have their fields
	 * standardized.
	 *
	 * @return array
	 */
	public static function get_active_post_types() {
		$active_post_types = array();
		if ( isset(self::$data['post_type_defs']) && is_array(self::$data['post_type_defs'])) {
			foreach (self::$data['post_type_defs'] as $post_type => $def) {
				if ( isset($def['is_active']) && $def['is_active'] == 1 ) {
					$active_post_types[] = $post_type;
				}

			}
		}

		return $active_post_types;
	}


	//------------------------------------------------------------------------------
	/**
	 * Custom manipulation of the WHERE clause used by the wp_get_archives() function.
	 * WP deliberately omits custom post types from archive results.
	 *
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=13
	 *
	 * @param string $where
	 * @param unknown $r
	 * @return string
	 */
	public static function get_archives_where_filter( $where , $r ) {
		// Get only public, custom post types
		$args = array( 'public' => true, '_builtin' => false );
		$public_post_types = get_post_types( $args );
		// Only posts get archives... not pages.
		$search_me_post_types = array('post');

		// check which have 'has_archive' enabled.
		if (isset(self::$data['post_type_defs']) && is_array(self::$data['post_type_defs'])) {
			foreach (self::$data['post_type_defs'] as $post_type => $def) {
				if ( isset($def['has_archive']) && $def['has_archive'] && in_array($post_type, $public_post_types)) {
					$search_me_post_types[] = $post_type;
				}
			}
		}
		$post_types = "'" . implode( "' , '" , $search_me_post_types ) . "'";

		return str_replace( "post_type = 'post'" , "post_type IN ( $post_types )" , $where );
	}


	//------------------------------------------------------------------------------
	/**
	 * Gets an array of full pathnames/filenames for all custom field types.
	 * This searches the built-in location AND the add-on location inside
	 * wp-content/uploads.  If there are duplicate filenames, the one inside the
	 * 3rd party directory will be registered: this allows developers to override
	 * the built-in custom field classes.
	 *
	 * This function will read the results from the cache
	 *
	 * @param boolean perform directory scan and update cache?
	 * @return array Associative array: array('shortname' => '/full/path/to/shortname.php')
	 */
	public static function get_available_custom_field_types() {

		// prep for output...
		$files = array();

		// Scan default directory
		$dir = CCTM_PATH .'/fields';
		$rawfiles = scandir($dir);
		foreach ($rawfiles as $f) {
			if ( !preg_match('/^\./', $f) && preg_match('/\.php$/', $f) ) {
				$shortname = basename($f);
				$shortname = preg_replace('/\.php$/', '', $shortname);
				$files[$shortname] = $dir.'/'.$f;
			}
		}

		// Scan 3rd party directory and subdirectories
		$upload_dir = wp_upload_dir();
		// it might come back something like
		// Array ( [error] => Unable to create directory /path/to/wp-content/uploads/2011/10. Is its parent directory writable by the server? )
		if (isset($upload_dir['error']) && !empty($upload_dir['error'])) {
			self::register_warning( __('WordPress issued the following error: ', CCTM_TXTDOMAIN) .$upload_dir['error']);
		}
		else {
			$dir = $upload_dir['basedir'] .'/'.CCTM::base_storage_dir . '/' . CCTM::custom_fields_dir;
			if (is_dir($dir)) {
				$rawfiles = scandir($dir);
				foreach ($rawfiles as $subdir) {
					if (preg_match('/^\./', $f)) {
						continue; // skip the . and .. dirs
					}
					// check subdirectories
					if (is_dir($dir.'/'.$subdir)) {
						$morerawfiles = scandir($dir.'/'.$subdir);
						foreach ($morerawfiles as $f) {
							if ( !preg_match('/^\./', $f) && preg_match('/\.class\.php$/', $f) ) {
								$shortname = basename($f);
								$shortname = preg_replace('/\.class\.php$/', '', $shortname);
								$files[$shortname] = $dir.'/'.$subdir.'/'.$f;
							}
						}
					}
					// Check the main directory too.
					elseif (preg_match('/\.php$/', $subdir) ) {
						$shortname = basename($f);
						$shortname = preg_replace('/\.php$/', '', $shortname);
						$files[$shortname] = $dir.'/'.$subdir;
					}
				}
			}
		}
		
		return $files;
	}


	//------------------------------------------------------------------------------
	/**
	 * Gets an array of full pathnames/filenames for all output filters.
	 * This searches the built-in location AND the add-on location inside
	 * wp-content/uploads. If there are duplicate filenames, the one inside the
	 * 3rd party directory will be registered: this allows developers to override
	 * the built-in output filter classes.
	 *
	 * @return array Associative array: array('shortname' => '/full/path/to/shortname.php')
	 */
	public static function get_available_output_filters() {
	
		// Ye olde output
		$files = array();
		
		// Scan default directory (should this be hardcoded?)
		$dir = CCTM_PATH .'/filters';
		$rawfiles = scandir($dir);
		foreach ($rawfiles as $f) {
			if ( !preg_match('/^\./', $f) && preg_match('/\.php$/', $f) ) {
				$shortname = basename($f);
				$shortname = preg_replace('/\.php$/', '', $shortname);
				$files[$shortname] = $dir.'/'.$f;
			}
		}

		// Scan 3rd party directory
		$upload_dir = wp_upload_dir();
		if (isset($upload_dir['error']) && !empty($upload_dir['error'])) {
			self::register_warning( __('WordPress issued the following error: ', CCTM_TXTDOMAIN) .$upload_dir['error']);
		}
		else {
			$dir = $upload_dir['basedir'] .'/'.CCTM::base_storage_dir . '/filters';
			if (is_dir($dir)) {
				$rawfiles = scandir($dir);
				foreach ($rawfiles as $f) {
					if ( !preg_match('/^\./', $f) && preg_match('/\.php$/', $f) ) {
						$shortname = basename($f);
						$shortname = preg_replace('/\.php$/', '', $shortname);
						$files[$shortname] = $dir.'/'.$f;
					}
				}
			}
		}
	//die('<pre>'.print_r($files, true).'</pre>');
		return $files;
	}


	//------------------------------------------------------------------------------
	/**
	 *  Defines the diretory for this plugin.
	 *
	 * @return string
	 */
	public static function get_basepath() {
		return dirname(dirname(__FILE__));
	}


	//------------------------------------------------------------------------------
	/**
	 * Gets the plugin version from this class.
	 *
	 * @return string
	 */
	public static function get_current_version() {
		return self::version .'-'. self::version_meta;
	}


	//------------------------------------------------------------------------------
	/**
	 * Interface with the model: retrieve the custom field definitions, sorted.
	 *
	 * @return array
	 */
	public static function get_custom_field_defs() {
		if ( isset(self::$data['custom_field_defs']) ) {
			// sort them
			$defs = self::$data['custom_field_defs'];
			usort($defs, CCTM::sort_custom_fields('name', 'strnatcasecmp'));

			foreach ($defs as $i => $d ) {
				$field_name = $d['name'];
				$defs[$field_name] = $d; // re-establish the key version.
				unset($defs[$i]); // kill the integer version
			}

			return $defs;
		}
		else {
			return array();
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Returns a path with trailing slash.
	 *
	 * @return string
	 */
	public static function get_custom_icons_src_dir() {
		self::$custom_field_icons_dir = CCTM_URL.'/images/custom-fields/';
		return self::$custom_field_icons_dir;
	}


	//------------------------------------------------------------------------------
	/**
	 * Get the flash message (i.e. a message that persists for the current user only
	 * for the next page view). See "Flashdata" here:
	 * http://codeigniter.com/user_guide/libraries/sessions.html
	 *
	 * @return message
	 */
	public static function get_flash() {
		$output = '';
		$key = self::get_user_identifier();
		if (isset(self::$data['flash'][$key])) {
			$output = self::$data['flash'][$key];
			unset( self::$data['flash'][$key] );
			update_option(self::db_key, self::$data);
			return html_entity_decode($output);
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Used to identify the current user for flash messages and screen locks
	 *
	 * @return integer
	 */
	public static function get_user_identifier() {
		global $current_user;
		if (!isset($current_user->ID) || empty($current_user->ID)) {
			return 0;
		}
		else {
			return $current_user->ID;
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * return all post-type definitions
	 *
	 * @return array
	 */
	public static function get_post_type_defs() {
		if ( isset(self::$data['post_type_defs']) && is_array(self::$data['post_type_defs'])) {
			return self::$data['post_type_defs'];
		}
		else {
			return array();
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Gets the plugin version (used to check if updates are available). This checks
	 * the database to see what the database thinks is the current version. Right
	 * after an update, the database will think the version is older than what
	 * the CCTM class will show as the current version. We use this to trigger 
	 * modifications of the CCTM data structure and/or database options.
	 *
	 * @return string
	 */
	public static function get_stored_version() {
		if ( isset(self::$data['cctm_version']) ) {
			return self::$data['cctm_version'];
		}
		else {
			return '0';
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Read the value of a setting.  Will use default value if the setting is not
	 * yet defined (e.g. when the user hasn't updated their settings.
	 *
	 * @param string $setting name (see class var $default_settings)
	 * @return mixed
	 */
	public static function get_setting($setting) {
		if (empty($setting)) {
			return '';
		}
		if (isset(self::$data['settings']) && is_array(self::$data['settings'])) {
			if (isset(self::$data['settings'][$setting])) {
				return self::$data['settings'][$setting];
			}
			elseif (isset(self::$default_settings[$setting])) {
				return self::$default_settings[$setting];
			}
			else {
				return ''; // setting not found :(
			}
		}
		elseif (isset(self::$default_settings[$setting])) {
			return self::$default_settings[$setting];
		}
		else {
			return '';
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * This will get thumbnail info and append it to the record, creating cached 
	 * images on the fly if possible.  The following keys are added to the array:
	 *
	 *		thumbnail_url
	 *
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=256
	 *
	 * What we need to get a thumbnail:
	 *	guid, post_type, ID, post_mime_type
	 * @param	integer	$id of the post for which we want the thumbnail
	 * @return	string	url of the thumbnail
	 */
	public static function get_thumbnail($id) {
		
		// Default output
		$thumbnail_url = CCTM_URL .'/images/custom-fields/default.png';

		if (empty($id) || $id == 0) {
			return $thumbnail_url;
		}
		
		$post = get_post($id, ARRAY_A);
		$guid = $post['guid'];
		$post_type = $post['post_type'];
		$post_mime_type = $post['post_mime_type'];
		$thumbnail_url = $post['guid'];
		
		// return $thumbnail_url; // Bypass for now
		
		// Some translated labels and stuff
		$r['preview'] = __('Preview', CCTM_TXTDOMAIN);
		$r['remove'] = __('Remove', CCTM_TXTDOMAIN);
		$r['cctm_url'] = CCTM_URL;
		


		// Special handling for media attachments (i.e. photos) and for 
		// custom post-types where the custom icon has been set.
		if ($post_type == 'attachment' && preg_match('/^image/',$post_mime_type) && self::get_setting('cache_thumbnail_images')) {
			$thumbnail_url = self::_get_create_thumbnail($post);
		}
		elseif ($post_type == 'post') {
			$thumbnail_url = CCTM_URL . '/images/wp-post.png';
		}
		elseif ($post_type == 'page') {
			$thumbnail_url = CCTM_URL . '/images/wp-page.png';	
		}
		// Other Attachments and other post-types: we go for the custom icon
		else
		{	
			if (isset(CCTM::$data['post_type_defs'][$post_type]['use_default_menu_icon']) 
					&& CCTM::$data['post_type_defs'][$post_type]['use_default_menu_icon'] == 0) {
				$baseimg = basename(CCTM::$data['post_type_defs'][$post_type]['menu_icon']);
				$thumbnail_url = CCTM_URL . '/images/icons/32x32/'. $baseimg;
				
			}
			// Built-in WP types: we go for the default icon.
			else {
				list($src, $w, $h) = wp_get_attachment_image_src( $id, 'tiny_thumb', true, array('alt'=>__('Preview', CCTM_TXTDOMAIN)));
				$thumbnail_url = $src;
			}
		}
		//die(print_r($r,true));
		return $thumbnail_url;	
	}

	//------------------------------------------------------------------------------
	/**
	 * Designed to safely retrieve scalar elements out of a hash. Don't use this
	 * if you have a more deeply nested object (e.g. an array of arrays).
	 *
	 * @param array   $hash    an associative array, e.g. array('animal' => 'Cat');
	 * @param string  $key     the key to search for in that array, e.g. 'animal'
	 * @param mixed   $default (optional) : value to return if the value is not set. Default=''
	 * @return mixed
	 */
	public static function get_value($hash, $key, $default='') {
		if ( !isset($hash[$key]) ) {
			return $default;
		}
		else {
			if ( is_array($hash[$key]) ) {
				return $hash[$key];
			}
			// Warning: stripslashes was added to avoid some weird behavior
			else {
				return esc_html(stripslashes($hash[$key]));
			}
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * !TODO: see http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=170
	 *
	 * @param unknown $stuff
	 * @return string
	 */
	public static function highlight_cctm_compatible_themes($stuff) {
		$stuff[] = 'CCTM compatible!';
		return $stuff;
	}


	//------------------------------------------------------------------------------
	/**
	 * Includes the class file for the field type specified by $field_type. The
	 * built-in directory is searched as well as the custom add-on directory.
	 * Precedence is given to the built-in directory.
	 * On success, the file is included and a true is returned.
	 * On error, the file is NOT included and a false is returned: errors are registered.
	 *
	 * @param string  $field_type class name WITHOUT prefix
	 * @return boolean
	 */
	public static function include_form_element_class($field_type) {
		
		if (empty($field_type) ) {
			self::$errors['missing_field_type'] = __('Field type is empty.', CCTM_TXTDOMAIN);
			return false;
		}
		
		$classname = self::classname_prefix.$field_type;
		
		if (class_exists($classname)) {
			return true;
		}


		require_once CCTM_PATH.'/includes/CCTM_FormElement.php';
		
		if (CCTM::load_file(array("/fields/$field_type.php","/fields/$field_type/$field_type.class.php"))) {
			if ( !class_exists($classname) ) {
				self::$errors['incorrect_classname'] = sprintf( __('Incorrect class name in %s file. Expected class name: %s', CCTM_TXTDOMAIN)
					, $field_type
					, $classname
				);
				return false;
			}
		}
		else {
			$msg = sprintf(__('The class file for %s fields could not be found. Did you move or delete the file?', CCTM_TXTDOMAIN), "<code>$field_type</code>");
			self::register_warning($msg);
			return false;
		}

		return true;
	}

	//------------------------------------------------------------------------------
	/**
	 * Each custom field can optionally do stuff during the admin_init event -- this
	 * was designed so custom fields could include their own JS & CSS, but it could
	 * be used for other purposes I suppose, e.g. registering other actions/filters.
	 *
	 * Custom field classes will be included and initialized only in the following
	 * two cases:
	 *  1. when creating/editing a post that uses one of these fields
	 *  2. when creating/editing a field definition of the type indicated.
	 * E.g.
	 *  post-new.php
	 *  post-new.php?post_type=page
	 * 	post.php?post=807
	 *  admin.php?page=cctm_fields&a=create_custom_field
	 *  admin.php?page=cctm_fields&a=edit_custom_field
	 */
	public static function initialize_custom_fields() {

		// Look around/read variables to get our bearings
		// $available_custom_field_files = CCTM::get_available_custom_field_types(true);
		$page = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/')+1);
		$fieldtype = self::get_value($_GET, 'type');
		$fieldname = self::get_value($_GET, 'field');
		$action = self::get_value($_GET, 'a');
		
		// Bail if we're not on the relevant pages
		if (!in_array($page,array('post.php','post-new.php','admin.php'))) {
			return;
		}
		
		if ($page == 'post-new.php') {
			$post_type = self::get_value($_GET, 'post_type', 'post'); // post_type is only set for NEW posts
		}
		else { // ( $page == 'post.php') {
			$post_id = self::get_value($_POST, 'post_ID');
			// TODO: wouldn't you think the post_type was already defined somewhere?
			if (empty($post_id)) {
				$post_id = self::get_value($_GET, 'post');		
			}
			
			$post = get_post($post_id);
			if (!empty($post)) {
				$post_type = $post->post_type;
			}
			
		}
		
		// Here's where we will load up all the field-types that are active on this particular post or page.
		$field_types = array();
		
		// Create/edit posts
		if ( ($page == 'post.php') || ($page == 'post-new.php') ) {
			if (isset(self::$data['post_type_defs'][$post_type]['is_active'])) {
				$custom_fields = self::get_value(self::$data['post_type_defs'][$post_type], 'custom_fields', array() );
				
				// We gotta convert the fieldname to fieldtype
				foreach ($custom_fields as $cf) {
					if (!isset(self::$data['custom_field_defs'][$cf])) {
						// unset this? 
						continue; // we shouldn't get here, but just in case...
					}
					// Get an array of field-types for this 
					$fieldtype = self::get_value(self::$data['custom_field_defs'][$cf], 'type');
					if (!empty($fieldtype)) {
						$field_types[] = $fieldtype;
					}
				}
			}
		}
		// Create custom field definitions
		elseif ( $page == 'admin.php' && $action == 'create_custom_field') {
			$field_types[] = $fieldtype;
		}
		// Edit custom field definitions (the name is specified, not the type)
		elseif ( $page == 'admin.php' && $action == 'edit_custom_field' && isset(self::$data['custom_field_defs'][$fieldname])) {
			$fieldtype = self::get_value(self::$data['custom_field_defs'][$fieldname], 'type');
			$field_types[] = $fieldtype;
		}

		// We only get here if we survived the gauntlet above
		foreach ($field_types as $shortname) {
			$classname = self::classname_prefix . $shortname;
			if (class_exists($classname)) {
				$Obj = new $classname();
				$Obj->admin_init();				
			}
			else {
				if (CCTM::load_file(array("/fields/$shortname.php", "/fields/$shortname/$shortname.class.php"))) {
					$Obj = new $classname();
					$Obj->admin_init();
				}
				else {
					CCTM::$errors[] = sprintf( __('Could not locate file for %s field.', CCTM_TXTDOMAIN), "<strong>$shortname</strong>");
				}
			}
		}

		if (!empty(CCTM::$errors)) {
			self::print_notices();
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Used when generating checkboxes in forms. Any non-empty non-zero incoming value will cause
	 * the function to return checked="checked"
	 *
	 * Simple usage uses just the first parameter: if the value is not empty or 0,
	 * the box will be checked.
	 *
	 * Advanced usage was built for checking a list of options in an array (see
	 * register_post_type's "supports" array).
	 *
	 * @param mixed   normally a string, but if an array, the 2nd param must be set
	 * @param string  value to look for inside the $input array.
	 * @param unknown $find_in_array (optional)
	 * @return string either '' or 'checked="checked"'
	 */
	public static function is_checked($input, $find_in_array='') {
		if ( is_array($input) ) {
			if ( in_array($find_in_array, $input) ) {
				return 'checked="checked"';
			}
			else {
				return '';
			}
		}
		else {
			if (!empty($input) && $input!=0) {
				return 'checked="checked"';
			}
		}
		return ''; // default
	}


	//------------------------------------------------------------------------------
	/**
	 * Like the is_selected function, but for radio inputs.
	 * If $option_value == $field_value, then this returns 'selected="selected"'
	 *
	 * @param string  $option_value:  the value of the <option> being tested
	 * @param string  $current_value: the current value of the field
	 * @return string
	 */
	public static function is_radio_selected($option_value, $current_value) {
		if ( $option_value == $current_value ) {
			return 'checked="checked"';
		}
		return '';
	}


	//------------------------------------------------------------------------------
	/**
	 * If $option_value == $field_value, then this returns 'selected="selected"'
	 *
	 * @param string  $option_value:  the value of the <option> being tested
	 * @param string  $current_value: the current value of the field
	 * @return string
	 */
	public static function is_selected($option_value, $current_value) {
		if ( $option_value == $current_value ) {
			return 'selected="selected"';
		}
		return '';
	}


	//------------------------------------------------------------------------------
	/**
	 * Using something like the following:
	 * if (!@fclose(@fopen($src, 'r'))) {
	 *  $src = CCTM_URL.'/images/custom-fields/default.png';
	 * }
	 * caused segfaults in some server configurations (see issue 60):
	 * http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=60
	 * So in order to check whether an image path is broken or not, we translate the
	 * $src URL into a local path so we can use humble file_exists() instead.
	 *
	 * This must also be able to handle when WP is installed in a sub directory.
	 *
	 *     or 'http://mysite.com/some/img.jpg'
	 *
	 * @param string  $src a path to an image ON THIS SERVER, e.g. '/wp-content/uploads/img.jpg'
	 * @return boolean true if the img is valid, false if the img link is broken
	 */
	public static function is_valid_img($src) {

		$info = parse_url($src);

		// Bail on malformed URLs
		if (!$info) {
			return false;
		}
		// Is this image hosted on another server? (currently that's not allowed)
		if ( isset($info['scheme']) ) {
			$this_site_info = parse_url( get_site_url() );
			if ( $this_site_info['scheme'] != $info['scheme']
				|| $this_site_info['host'] != $info['host']
				|| $this_site_info['port'] != $info['port']) {

				return false;
			}
		}

		// Gives us something like "/home/user/public_html/blog"
		$ABSPATH_no_trailing_slash = preg_replace('#/$#', '', ABSPATH);

		// This will tell us whether WP is installed in a subdirectory
		$wp_info = parse_url(site_url());

		// This works when WP is installed @ the root of the site
		if ( !isset($wp_info['path']) ) {
			$path = $ABSPATH_no_trailing_slash . $info['path'];
		}
		// But if WP is installed in a sub dir...
		else {
			$path_to_site_root = preg_replace('#'.preg_quote($wp_info['path']).'$#'
				, ''
				, $ABSPATH_no_trailing_slash);
			$path = $path_to_site_root . $info['path'];
		}

		if ( file_exists($path) ) {
			return true;
		}
		else {
			return false;
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Load CCTM data from database.
	 */
	public static function load_data() {
		self::$data = get_option( CCTM::db_key, array() );
	}

	//------------------------------------------------------------------------------
	/**
	 * When given a PHP file name relative to the CCTM_PATH, e.g. '/config/image_search_parameters.php',
	 * this function will include that file using php include(). However, if the same file exists
	 * in the same location relative to the wp-content/uploads/cctm directory, THAT version of 
	 * the file will be used. E.g. calling load_file('test.php') will include 
	 * wp-content/uploads/cctm/test.php (if it exists); if the file doesn't exist in the uploads
	 * directory, then we'll look for the file inside the CCTM_PATH, e.g.
	 * wp-content/plugins/custom-content-type-manager/test.php 
	 *
	 * The purpose of this is to let users override certain files by placing their own in a location
	 * that is *outside* of this plugin's directory so that the user-created files will be safe
	 * from any overriting or deleting that may occur if the plugin is updated.
	 *	 
	 *
	 * Developers of 3rd party components can supply additional paths $path if they wish to load files
	 * in their components: if the $additional_path is supplied, this directory will be searched for tpl in question.
	 *
	 * To prevent directory transversing, file names may not contain '..'!
	 *
	 * @param	array|string	$files: filename relative to the path, e.g. '/config/x.php'. Should begin with "/"
	 * @param	array|string	(optional) $additional_paths: this adds one more paths to the default locations. OMIT trailing /, e.g. called via dirname(__FILE__)
	 * @param	mixed	file name used on success, false on fail.
	 */
	public static function load_file($files, $additional_paths=null) {

		if (!is_array($files)){
			$files = array($files);
		}

		if (!is_array($additional_paths)){
			$additional_paths = array($additional_paths);
		}
		
		// Populate the list of directories we will search in order. 
		$upload_dir = wp_upload_dir();
		$paths = array();
		$paths[] = $upload_dir['basedir'] .'/'.CCTM::base_storage_dir;
		$paths[] = CCTM_PATH;
		$paths = array_merge($paths, $additional_paths);

		// pull a file off the stack, then look for it
		$file = array_shift($files);
		
		if (preg_match('/\.\./', $file)) {
			die( sprintf(__('Invaid file name! %s  No directory traversing allowed!', CCTM_TXTDOMAIN), '<em>'.htmlspecialchars($file).'</em>'));
		}
		
		if (!preg_match('/\.php$/', $file)) {
			die( sprintf(__('Invaid file name! %s  Name must end with .php!', CCTM_TXTDOMAIN), '<em>'.htmlspecialchars($file).'</em>'));
		}		
		
		// Look through the directories in order.
		foreach ($paths as $dir) {
			if (file_exists($dir.$file)) { 
				include($dir.$file);
				return $dir.$file;
			}
		}
		
		// Try again with the remaining files... or fail.
		if (!empty($files)) {
			return self::load_file($files, $additional_paths);
		}
		else {
			return false;
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Similar to the load_view function, this retrieves a tpl.  It allows users to
	 * override the built-in tpls (stored in the plugin's directory) with tpls stored
	 * in the wp uploads directory.
	 *
	 * If you supply an array of arguments to $name, the first tpl (in the array[0] position)
	 * will be looked for first in the customized directories, then in the built-ins.  If nothing
	 * is found, the array is shifted and the next item in the array is looked for, first in the 
	 * customized locations, then in the built-in locations.  By shifting the array, you can specify
	 * a hierarchy of "fallbacks" to look for with any tpl.
	 *
	 * Developers of 3rd party components can supply additional paths $path if they wish to use tpls
	 * in their components: if the $additional_path is supplied, this directory will be searched for tpl in question.
	 *
	 * To prevent directory transversing, tpl names may not contain '..'!
	 *
	 * @param	array|string	$name: single name or array of tpl names, each relative to the path, e.g. 'fields/date.tpl'. The first one in the list found will be used.
	 * @param	array|string	(optional) $additional_paths: this adds one more path to the default locations. OMIT trailing /, e.g. called via dirname(__FILE__)
	 * @return	string	the file contents (not parsed) OR a boolean false if nothing was found.
	 */
	public static function load_tpl($tpls, $additional_paths=null) {

		if (!is_array($tpls)){
			$tpls = array($tpls);
		}
		if (!is_array($additional_paths)){
			$additional_paths = array($additional_paths);
		}
		
		// Populate the list of directories we will search in order. 
		$upload_dir = wp_upload_dir();
		$paths = array();
		$paths[] = $upload_dir['basedir'] .'/'.CCTM::base_storage_dir.'/tpls';
		$paths[] = CCTM_PATH.'/tpls';
		$paths = array_merge($paths, $additional_paths);

		// Pull the tpl off the stack
		$tpl = array_shift($tpls);

		if (preg_match('/\.\./', $tpl)) {
			die( sprintf(__('Invaid tpl name! %s  No directory traversing allowed!', CCTM_TXTDOMAIN), '<em>'.htmlspecialchars($tpl).'</em>'));
		}
		
		if (!preg_match('/\.tpl$/', $tpl)) {
			die( sprintf(__('Invaid tpl name! %s  Name must end with .tpl!', CCTM_TXTDOMAIN), '<em>'.htmlspecialchars($tpl).'</em>'));
		}		
		
		// Look through the directories in order.
		foreach ($paths as $dir) {
			if (file_exists($dir.'/'.$tpl)) { 
				return file_get_contents($dir.'/'.$tpl);
			}
		}

		// Try again with the remaining tpls... or fail.
		if (!empty($tpls)) {
			return self::load_tpl($tpls, $additional_paths);
		}
		else {
			return false;
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Load up a PHP file into a string via an include statement. MVC type usage here.
	 *
	 * @param string  $filename (relative to the views/ directory)
	 * @param array   $data (optional) associative array of data
	 * @param string  $path (optional) pathname. Can be overridden for 3rd party fields
	 * @return string the parsed contents of that file
	 */
	public static function load_view($filename, $data=array(), $path=null) {
		if (empty($path)) {
			$path = CCTM_PATH . '/views/';
		}
		if (is_file($path.$filename)) {
			ob_start();
			include $path.$filename;
			return ob_get_clean();
		}
		die('View file does not exist: ' .$path.$filename);
	}



	//------------------------------------------------------------------------------
	/**
	 * Since WP doesn't seem to support sorting of custom post types, we have to
	 * forcibly tell it to sort by the menu order. Perhaps this should kick in
	 * only if a post_type's def has the "Attributes" box checked?
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=142
	 *
	 * @param string  $orderBy
	 * @return string
	 */
	public static function order_posts($orderBy) {
		$post_type = self::get_value($_GET, 'post_type');
		if (empty($post_type)) {
			return $orderBy;
		}
		if (isset(self::$data['post_type_defs'][$post_type]['custom_orderby']) && !empty(self::$data['post_type_defs'][$post_type]['custom_orderby'])) {
			global $wpdb;
			$order = self::get_value(self::$data['post_type_defs'][$post_type], 'custom_order', 'ASC');
			$orderBy = "{$wpdb->posts}.".self::$data['post_type_defs'][$post_type]['custom_orderby'] . " $order";

		}
		return $orderBy;
	}


	//------------------------------------------------------------------------------
	/**
	 * This is the grand poobah of functions for the admin pages: it routes requests
	 * to specific functions.
	 * This is the function called when someone clicks on the settings page.
	 * The job of a controller is to process requests and route them.
	 *
	 */
	public static function page_main_controller() {

		// TODO: this should be specific to the request
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		// Grab any possible parameters that might get passed around in the URL
		$action  = self::get_value($_GET, 'a');
		$post_type = self::get_value($_GET, 'pt');
		$file   = self::get_value($_GET, 'file');
		$field_type = self::get_value($_GET, 'type');
		$field_name = self::get_value($_GET, 'field');



		// Default Actions for each main menu item (see create_admin_menu)
		if (empty($action)) {
			$page = self::get_value($_GET, 'page', 'cctm');
			switch ($page) {
			case 'cctm': // main: custom content types
				$action = 'list_post_types';
				break;
			case 'cctm_fields': // custom-fields
				$action = 'list_custom_fields';
				break;
			case 'cctm_settings': // settings
				$action = 'settings';
				break;
			case 'cctm_themes': // themes
				$action = 'themes';
				break;
			case 'cctm_tools': // tools
				$action = 'tools';
				break;
			case 'cctm_info': // info
				$action = 'info';
				break;
			}
		}

		// Validation on the controller name to prevent mischief:
		if ( preg_match('/[^a-z_\-]/i', $action) ) {
			include CCTM_PATH.'/controllers/404.php';
			return;
		}

		$requested_page = CCTM_PATH.'/controllers/'.$action.'.php';

		if (file_exists($requested_page)) {
			include $requested_page;
		}
		else {
			include CCTM_PATH.'/controllers/404.php';
		}
		return;
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
	 * @param unknown $preserve_unused_placeholders (optional)
	 * @return string placeholders corresponding to the keys of the hash will be replaced
	 */
	public static function parse($tpl, $hash, $preserve_unused_placeholders=false) {

		// Get all placeholders in this tpl
		$all_placeholders = array_keys($hash);
		$hash['help'] = '<ul>';
		foreach ($all_placeholders as $p) {
			$hash['help'] .= "<li>&#91;+$p+&#93;</li>";
		}
		$hash['help'] .= '</ul>';

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
	 * Print errors if they were thrown by the tests. Currently this is triggered as
	 * an admin notice so as not to disrupt front-end user access, but if there's an
	 * error, you should fix it! The plugin may behave erratically!
	 * INPUT: none... ideally I'd pass this a value, but the WP interface doesn't make
	 *  this easy, so instead I just read the class variable: CCTMtests::$errors
	 *
	 * @return none  But errors are printed if present.
	 */
	public static function print_notices() {
		if ( !empty(CCTM::$errors) ) {
			$error_items = '';
			foreach ( CCTM::$errors as $e ) {
				$error_items .= "<li>$e</li>";
			}
			$msg = sprintf( __('The %s plugin encountered errors! It cannot load!', CCTM_TXTDOMAIN)
				, CCTM::name);
			printf('<div id="custom-post-type-manager-warning" class="error">
				<p>
					<strong>%1$s</strong>
					<ul style="margin-left:30px;">
						%2$s
					</ul>
				</p>
				</div>'
				, $msg
				, $error_items);
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Print warnings if there are any that haven't been dismissed
	 */
	public static function print_warnings() {

		$warning_items = '';

		// Check for warnings
		if ( !empty(self::$data['warnings']) ) {
			//   print '<pre>'. print_r(self::$data['warnings']) . '</pre>'; exit;
			$clear_warnings_url = sprintf(
				'<a href="?page=cctm&a=clear_warnings&_wpnonce=%s" title="%s" class="button">%s</a>'
				, wp_create_nonce('cctm_clear_warnings')
				, __('Dismiss all warnings', CCTM_TXTDOMAIN)
				, __('Dismiss Warnings', CCTM_TXTDOMAIN)
			);
			$warning_items = '';
			foreach ( self::$data['warnings'] as $warning => $viewed ) {
				if ($viewed == 0) {
					$warning_items .= "<li>$warning</li>";
				}
			}
		}

		if ($warning_items) {
			$msg = __('The Custom Content Type Manager encountered the following warnings:', CCTM_TXTDOMAIN);
			printf('<div id="custom-post-type-manager-warning" class="error">
				<p>
					<strong>%s</strong>
					<ul style="margin-left:30px;">
						%s
					</ul>
				</p>
				<p>%s</p>
				</div>'
				, $msg
				, $warning_items
				, $clear_warnings_url
			);
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Performs a Javascript redirect in order to refresh the page. The $url should
	 * should include only query parameters and start with a ?, e.g. '?page=cctm'
	 *
	 * @param string  the CCTM admin page to redirect to.
	 * @return none; this prints the result.
	 * @param unknown $url
	 */
	public static function redirect($url) {
		print '<script type="text/javascript">window.location.replace("'.get_admin_url(false, 'admin.php').$url.'");</script>';
		exit;
	}


	//------------------------------------------------------------------------------
	/**
	 * Register custom post-types, one by one. Data is stored in the wp_options table
	 * in a structure that matches exactly what the register_post_type() function
	 * expectes as arguments.
	 *
	 * See: http://codex.wordpress.org/Function_Reference/register_post_type
	 * See wp-includes/posts.php for examples of how WP registers the default post types
	 *
	 * $def = Array
	 * (
	 *     'supports' => Array
	 *         (
	 *             'title',
	 *             'editor'
	 *         ),
	 *
	 *     'post_type' => 'book',
	 *     'singular_label' => 'Book',
	 *     'label' => 'Books',
	 *     'description' => 'What I&#039;m reading',
	 *     'show_ui' => 1,
	 *     'capability_type' => 'post',
	 *     'public' => 1,
	 *     'menu_position' => '10',
	 *     'menu_icon' => '',
	 *     'custom_content_type_mgr_create_new_content_type_nonce' => 'd385da6ba3',
	 *     'Submit' => 'Create New Content Type',
	 *     'show_in_nav_menus' => '',
	 *     'can_export' => '',
	 *     'is_active' => 1,
	 * );
	 * FUTURE??:
	 * register_taxonomy( $post_type,
	 * $cpt_post_types,
	 * array( 'hierarchical' => get_disp_boolean($cpt_tax_type["hierarchical"]),
	 * 'label' => $cpt_label,
	 * 'show_ui' => get_disp_boolean($cpt_tax_type["show_ui"]),
	 * 'query_var' => get_disp_boolean($cpt_tax_type["query_var"]),
	 * 'rewrite' => array('slug' => $cpt_rewrite_slug),
	 * 'singular_label' => $cpt_singular_label,
	 * 'labels' => $cpt_labels
	 * ) );
	 */
	public static function register_custom_post_types() {

		$post_type_defs = self::get_post_type_defs();

		foreach ($post_type_defs as $post_type => $def) {
			$def = self::_prepare_post_type_def($def);

			if ( isset($def['is_active'])
				&& !empty($def['is_active'])
				&& !in_array($post_type, self::$built_in_post_types)) {
				register_post_type( $post_type, $def );
			}
		}
		// Added per issue 50
		// http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=50
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}


	//------------------------------------------------------------------------------
	/**
	 * Warnings are like errors, but they can be dismissed.
	 * So if the warning hasn't been logged already and dismissed,
	 * it gets its own place in the data structure.
	 *
	 * @param string  Text of the warning
	 * @return none
	 */
	public static function register_warning($str) {
		if (!empty($str) && !isset(self::$data['warnings'][$str])) {
			self::$data['warnings'][$str] = 0; // 0 = not read.
			update_option(self::db_key, self::$data);
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * This filters the basic page lookup so URLs like http://mysite.com/archives/date/2010/11
	 * will return custom post types.
	 * See issue 13 for full archive suport:
	 * http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=13
	 * and http://bajada.net/2010/08/31/custom-post-types-in-the-loop-using-request-instead-of-pre_get_posts
	 *
	 * @param unknown $query
	 * @return unknown
	 */
	public static function request_filter( $query ) {

		// This is a troublesome little query... we need to monkey with it so WP will play nice with
		// custom post types, but if you breathe on it wrong, chaos ensues. See the following issues:
		//  http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=108
		//  http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=111
		//  http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=112
		if ( empty($query)
			|| isset($query['pagename'])
			|| isset($query['preview'])
			|| isset($query['feed'])
			|| isset($query['page_id'])
			|| !empty($query['post_type']) ) {

			return $query;
		}

		// Get only public, custom post types
		$args = array( 'public' => true, '_builtin' => false );
		$public_post_types = get_post_types( $args );


		// Categories can apply to posts and pages
		$search_me_post_types = array('post', 'page');
		if ( isset($query['category_name']) ) {
			foreach ($public_post_types as $pt => $tmp) {
				$search_me_post_types[] = $pt;
			}
			$query['post_type'] = $search_me_post_types;
			return $query;
		}

		// Only posts get archives, not pages, so our first archivable post-type is "post"...
		$search_me_post_types = array('post');

		// check which have 'has_archive' enabled.
		foreach (self::$data['post_type_defs'] as $post_type => $def) {
			if ( isset($def['has_archive']) && $def['has_archive'] && in_array($post_type, $public_post_types)) {
				$search_me_post_types[] = $post_type;
			}
		}

		$query['post_type'] = $search_me_post_types;

		return $query;
	}


	//------------------------------------------------------------------------------
	/**
	 * Adds custom post-types to dashboard "Right Now" widget
	 */
	public static function right_now_widget() {
		$args = array(
			'public' => true ,
			'_builtin' => false
		);
		$output = 'object';
		$operator = 'and';

		$post_types = get_post_types( $args , $output , $operator );

		foreach ( $post_types as $post_type ) {
			$num_posts = wp_count_posts( $post_type->name );
			$num = number_format_i18n( $num_posts->publish );
			$text = _n( $post_type->labels->singular_name, $post_type->labels->name , intval( $num_posts->publish ) );

			// Make links if the user has permission to edit
			if ( current_user_can( 'edit_posts' ) ) {
				$num = "<a href='edit.php?post_type=$post_type->name'>$num</a>";
				$text = "<a href='edit.php?post_type=$post_type->name'>$text</a>";
			}
			printf('<tr><td class="first b b-%s">%s</td>', $post_type->name, $num);
			printf('<td class="t %s">%s</td></tr>', $post_type->name, $text);
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Ensures that the front-end search form can find posts or view posts in the RSS
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=143
	 * See also http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=186
	 *
	 * @param unknown $query
	 * @return unknown
	 */
	public static function search_filter($query) {
		//die(print_r($query, true));
		if ($query->is_feed) {
			if ( !isset($_GET['post_type']) && empty($_GET['post_type'])) {
				$post_types = get_post_types();
				unset($post_types['revision']);
				unset($post_types['nav_menu_item']);
				foreach ($post_types as $pt) {
					// we only exclude it if it was specifically excluded.
					if (isset(self::$data['post_type_defs'][$pt]['include_in_rss']) && !self::$data['post_type_defs'][$pt]['include_in_rss']) {
						unset($post_types[$pt]);
					}
				}
				// The format of the array of $post_types is array('post' => 'post', 'page' => 'page')
				$query->set('post_type', $post_types);
			}
		}
		elseif ($query->is_search || $query->is_category) {
			// die('ouch');
			if ( !isset($_GET['post_type']) && empty($_GET['post_type'])) {
				$post_types = get_post_types( array('exclude_from_search'=>false) );
				//die(print_r(self::$data['post_type_defs'], true));
				//die(print_r($post_types,true));
				// The format of the array of $post_types is array('post' => 'post', 'page' => 'page')
				$query->set('post_type', $post_types);
			}
		}

		return $query;
	}


	//------------------------------------------------------------------------------
	/**
	 * Sets a flash message that's viewable only for the next page view (for the current user)
	 * $_SESSION doesn't work b/c WP doesn't natively support them = lots of confused users.
	 * setcookie() won't work b/c WP has already sent header info.
	 * So instead, we store this stuff in the database. Sigh.
	 *
	 * @param string  $msg text or html message
	 */
	public static function set_flash($msg) {
		self::$data['flash'][ self::get_user_identifier() ] = $msg;
		update_option(self::db_key, self::$data);
	}


	//------------------------------------------------------------------------------
	/**
	 * Used by php usort to sort custom field defs by their sort_param attribute
	 *
	 * @param string  $field
	 * @param string  $sortfunc
	 * @return array
	 */
	public static function sort_custom_fields($field, $sortfunc) {
		return create_function('$var1, $var2', 'return '.$sortfunc.'($var1["'.$field.'"], $var2["'.$field.'"]);');
	}


	//------------------------------------------------------------------------------
	/**
	 * Recursively removes all quotes from $_POSTED data if magic quotes are on
	 * http://algorytmy.pl/doc/php/function.stripslashes.php
	 *
	 * @param array   possibly nested
	 * @param unknown $value
	 * @return array clensed of slashes
	 */
	public static function stripslashes_deep($value) {
		if ( is_array($value) ) {
			$value = array_map( 'CCTM::'. __FUNCTION__, $value);
		}
		else {
			$value = stripslashes($value);
		}
		return $value;
	}


	//------------------------------------------------------------------------------
	/**
	 * Recursively strips tags from all inputs, including nested ones.
	 *
	 * @param unknown $value
	 * @return array the input array, with tags stripped out of each value.
	 */
	public static function striptags_deep($value) {
		if ( is_array($value) ) {
			$value = array_map('CCTM::'. __FUNCTION__, $value);
		}
		else {
			$value = strip_tags($value, self::$allowed_html_tags);
		}
		return $value;
	}


}


/*EOF CCTM.php*/