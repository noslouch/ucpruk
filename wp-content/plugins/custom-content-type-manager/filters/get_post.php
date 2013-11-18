<?php
/**
 * @package CCTM_OutputFilter
 * 
 * Converts input (usually a JSON encoded string) into an array
 */

class CCTM_get_post extends CCTM_OutputFilter {

	/**
	 * Apply the filter.
	 *
	 * @param 	integer 	input
	 * @param	mixed	optional arguments
	 * @return mixed
	 */
	public function filter($input, $options=null) {
		if (is_array($input)) {
			return $input; // nothing to do here.
		}

		$input = (int) $input;
		
		return get_post_complete($input);
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __("The <em>get_post</em> retrieves a post by its ID.  Unlike WordPress's get_post() function, this filter appends all custom field data.", CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field',$fieldtype) {
		return '<?php 
$my_post = get_custom_field("'.$fieldname.'");
print $my_post["post_title"]; 
print $my_post["my_custom_field"];
// ... etc ...
?>';
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Get Post', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/get_post_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/