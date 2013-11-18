<?php
/**
 * A class for programmatically creating posts with all their custom fields via 
 * a unified API.  It is similar to wp_insert_post() but with several important
 * differences:
 *	1. It does not call any actions or filters when it is executed, so for better
 *		or for worse, there is no way for 3rd parties to intervene with this.
 *	2. It automatically creates/updates/deletes all custom fields in the postmeta
 *		table without the need to have use the update_post_meta() and related functions.
 *	3. It does not check for user permissions. If you're running around in the PHP
 *		code, you have rull run of the database. 
 *
 * @pacakge SummarizePosts
 */
class SP_Post {

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

	/**
	 * Assoc. array that maps a column name to a validator function
	 */
	public $validators = array(
		'ID' 				=> 'int',
		'post_author'		=> 'int',
		'post_date'			=> 'date',
		'post_date_gmt'		=> 'date',
		'post_modified' 	=> 'date',
		'post_modified_gmt' => 'date',
		'post_parent' 		=> 'int',
		'menu_order' 		=> 'int',
		'comment_count'		=> 'int',
	);

	public $errors = array();
	
	public $props = array();
	
	//------------------------------------------------------------------------------
	/**
	 * 
	 */
	public function __construct($props=array()) {
		
	}
	
	//------------------------------------------------------------------------------
	//! Private Functions
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
	 * Convert input to datestamp
	 * @param	string	$val
	 * @param	string
	 */
	private function _date($val) {
		return date('Y-m-d H:i:s', strtotime($val));
	}
	
	//------------------------------------------------------------------------------
	/**
	 * @param	string	$val
	 * @return	integer
	 */
	private function _int($val) {
		return (int) $val;
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Used to override another filter.
	 * @param	string	$val
	 * @return	string
	 */
	private function _none($val) {
		return $val;
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Basic text protection.
	 * @param	string	$val
	 * @return	string
	 */
	private function _text($val) {
		return wp_kses($val, array());
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Run the arguments through the various validators defined
	 */
	private function _sanitize($args) {
		foreach ($args as $k => $v) {
			if (isset($this->validators[$k])) {
				$func_name = '_'.$this->validators[$k];
				$args[$k] = $this->$func_name($v);
			}
			elseif($this->_is_valid_column_name($k)) {
				$args[$k] = $this->_text($v);
			}
			else {
				$this->errors[] = 'Invalid column name: ' . $k;
			}
		}
		
		return $args;
	}
	
	
	//------------------------------------------------------------------------------
	//! Public Functions
	//------------------------------------------------------------------------------
	/**
	 * Deletes a post, its custom fields, and any revisions of that post. 
	 * @param	integer	$post_id
	 */
	public function delete($post_id) {
		global $wpdb;
		
		// Delete the custom fields
		$query = $wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE post_id = %s"
			, $post_id);
		$wpdb->query($query);		
		
		// Delete any revisions
		$query = $wpdb->prepare("DELETE a FROM {$wpdb->posts} a INNER JOIN {$wpdb->posts} b ON a.post_parent=b.ID WHERE a.post_type='revision' AND b.ID=%s"
			, $post_id);
		$wpdb->query($query);
				
		// Delete the posts
		$query = $wpdb->prepare("DELETE FROM {$wpdb->posts} WHERE ID=%s;"
			, $post_id);
		$wpdb->query($query);		
		
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Ties into GetPostsQuery, but offers a bit more flexibility.
	 *
	 * @param	mixed	$args	integer ID, or valid search params for GetPostsQuery
	 */
	public function get($args) {
		$Q = new GetPostsQuery();
		$posts = $Q->get_posts($args);
		if (!empty($posts)) {
			return $posts[0];
		}
		else {
			return false;
		}
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
	 * INSERT INTO `wp_posts` (ID,post_title) VALUES ('666','satan');
	 * If you got no values, the minimal insertion would be this:
	 * INSERT INTO `wp_posts` (ID) VALUES (NULL);
	 *
	 * @param	array	$args
	 * @return	integer	post_id 
	 */
	public function insert($args) {
		
		global $wpdb;
		
		unset($args['ID']); // just in case
		$args = $this->_sanitize($args);

		// Get the primary columns
		$posts_args = array();
		$postmeta_args = array();
		foreach ($args as $k => $v) {
			if (in_array($k, self::$wp_posts_columns)) {
				$posts_args[$k] = $v;
			}
			else {
				$postmeta_args[$k] = $v;	
			}
		}

		if ($wpdb->insert($wpdb->posts, $posts_args) == false) {
			$this->errors[] = "Error inserting row into {$wpdb->posts}";
			return false;
		}
		
		$post_id = $wpdb->insert_id;
		
		if (!empty($postmeta_args)) {
			$query = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES ";
			$meta_rows = array();
			foreach ($postmeta_args as $k => $v) {
				if (is_array($v)) {
					$v = json_encode($v);
				}
				$meta_rows[] = $wpdb->prepare('(%d, %s, %s)', $post_id, $k, $v);
			}
			
			$meta_str = implode(', ', $meta_rows);
			
			$query = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES " . $meta_str;
			$wpdb->query($query);
		}
		
		return $post_id;
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Intelligently switch between insert/update
	 *
	 * @return	integer post id on success, false on failure
	 */
	public function save($args) {
		if (isset($args['ID']) && $args['ID'] != 0) {
			$post = get_post($args['ID'], ARRAY_A);
			if (!empty($post)) {
				return $this->update($args);
			}
		}
		
		return $this->insert($args);
	}


	//------------------------------------------------------------------------------
	/**
	 * @param	array	$args in column => value pairs
	 * @param	boolean	$overwrite (optional). If true, this will nuke any custom fields not included in $args.
	 */
	public function update($args, $overwrite=false) {
		
		$post_id = '';
		global $wpdb;
		
		$args = $this->_sanitize($args);

		// Get the primary columns
		$posts_args = array();
		$postmeta_args = array();
		foreach ($args as $k => $v) {
			if (in_array($k, self::$wp_posts_columns)) {
				$posts_args[$k] = $v;
			}
			else {
				$postmeta_args[$k] = $v;	
			}
		}
		
		if (isset($args['ID']) && !empty($args['ID'])) {
			$post_id = $args['ID'];
		}
		else {
			$this->errors[] = 'Update requires the post ID';
			return false;
		}
		
		// Main fields
		$wpdb->update( $wpdb->posts, $posts_args, array('ID' => $post_id));

		// Custom fields
		if ($overwrite) {
			$query = $wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE post_id = %s", $post_id);
			$wpdb->query($query);
		}
		
		foreach ($postmeta_args as $k => $v) {
			if (is_array($v)) {
				$v = json_encode($v);
			}
			if ($wpdb->update( $wpdb->postmeta, array('meta_value' => $v), array('post_id' => $post_id, 'meta_key' => $k)) == false ) {
				// it's a new row, so we insert
				if ($wpdb->insert($wpdb->postmeta, array('post_id' => $post_id, 'meta_key' => $k, 'meta_value'=>$v)) == false) {
					$this->errors[] = "Error inserting row into {$wpdb->postmeta} for column $k";
					return false;
				}
			}	
		}
		
		return $post_id; // successful if we made it here.
		
	}
}


/*EOF*/