<?php
/**
 * @package CCTM_OutputFilter
 * 
 * The do_shortcode filter parses any shortcodes present in the input by using
 * WordPress' do_shortcode() function
 * See http://codex.wordpress.org/Function_Reference/do_shortcode.
 */

class CCTM_do_shortcode extends CCTM_OutputFilter {

	
	/**
	 * Apply the filter.
	 *
	 * @param 	string 	input
	 * @param	boolean	options: true, bypass wpautop(). Default: false
	 * @return string
	 */
	public function filter($input, $options=null) {
		if ($options) {
			
		}
		else {
			return do_shortcode(wpautop($input));
		}
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __('The <em>do_shortcode</em> filter parses any shortcodes in the input. By default, WordPress will only parse shortcodes in the main content block, not in custom fields, so it is important to use this filter on WYSIWYG fields if you use any shortcodes.', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field',$fieldtype) {
		return "<?php print_custom_field('$fieldname:do_shortcode'); ?>";
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Do Shortcode', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/do_shortcode_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/