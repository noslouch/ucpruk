<?php
/**
 * @package CCTM_OutputFilter
 * 
 * Retrieves an excerpt from the input string, separating either via a string separator 
 * (e.g. <!--more->) OR using an integer to denote the max number of words you'd like 
 * the excerpt to contain.
 */

class CCTM_excerpt extends CCTM_OutputFilter {

	/**
	 * Apply the filter.
	 *
	 * @param 	mixed 	input
	 * @param	mixed	optional arguments: default '<!--more-->'
	 * @return mixed
	 */
	public function filter($input, $options='<!--more-->') {
		
		if (empty($input)) { 
			return '';
		}
		
		$output = do_shortcode($input);
		// Strip space
		$output = preg_replace('/\s\s+/', ' ', $output);
		
		// Count the number of words
		if (is_integer($options)) {
			// Strip HTML *before* we count the words.
			$output = strip_tags($output);
			$words_array = explode(' ', $output);
			$max_word_cnt = $options;
			$output = implode(' ', array_slice($words_array, 0, $max_word_cnt - 1)) . '&#0133;';
		}
		// Split on a separator
		else {
			$parts = explode($options, $output);
			$output = $parts[0];
			// Strip HTML *after* we split on the separator
			$output = strip_tags($output);
		}
		
		
		return $output;
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __('The <em>excerpt</em> takes a long string and returns a shorter excerpt of it, either chopping the input on the separator string or limiting it to a given number of words.', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field',$fieldtype) {
		return "<?php print_custom_field('$fieldname:excerpt', 100); ?>";
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Excerpt', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/excerpt_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/