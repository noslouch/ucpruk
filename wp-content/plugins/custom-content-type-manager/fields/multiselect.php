<?php
/**
 * CCTM_multiselect
 *
 * Implements an HTML multi-select element with options (multiple select).
 *
 * @package CCTM_FormElement
 */
 
class CCTM_multiselect extends CCTM_FormElement
{
	public $props = array(
		'label' => '',
		'name' => '',
		'description' => '',
		'class' => '',
		'extra'	=> '',
		'default_value' => '',
		'options'	=> array(),
		'values'	=> array(), // only used if use_key_values = 1
		'use_key_values' => 0, // if 1, then 'options' will use key => value pairs.
		'output_filter' => 'to_array',
		// 'type'	=> '', // auto-populated: the name of the class, minus the CCTM_ prefix.
	);

	//------------------------------------------------------------------------------
	/**
	 * Register the appropriatejs
	 */
	public function admin_init() {
		wp_register_script('cctm_dropdown', CCTM_URL.'/js/dropdown.js', array('jquery'));
		wp_enqueue_script('cctm_dropdown');
	}
	
	//------------------------------------------------------------------------------
	/**
	* This function provides a name for this type of field. This should return plain
	* text (no HTML). The returned value should be localized using the __() function.
	* @return	string
	*/
	public function get_name() {
		return __('Multi-select',CCTM_TXTDOMAIN);	
	}
	
	//------------------------------------------------------------------------------
	/**
	* This function gives a description of this type of field so users will know 
	* whether or not they want to add this type of field to their custom content
	* type. The returned value should be localized using the __() function.
	* @return	string text description
	*/
	public function get_description() {
		return __('Multi-select fields implement a <select> element which lets you select mutliple items.
			"Extra" parameters, e.g. "size" can be specified in the definition.',CCTM_TXTDOMAIN);
	}

	//------------------------------------------------------------------------------
	/**
	* This function should return the URL where users can read more information about
	* the type of field that they want to add to their post_type. The string may
	* be localized using __() if necessary (e.g. for language-specific pages)
	*
	* @return	string 	e.g. http://www.yoursite.com/some/page.html
	*/
	public function get_url() {
		return 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/MultiSelect';
	}
	
	//------------------------------------------------------------------------------
	/**
	 * This is the odd duck... it could (should?) be implemented as a variation of 
	 * the dropbox field (it is so similar), but conceptually, the multi-select is a
	 * different animal.  It doesn't get "repeated" like the other fields, instead
	 * it _always_ stores an array of values.  Notably, this field uses only the 
	 * option and wrapper .tpl's.
	 *
	 * This is hands-down the most complex field due to the way we have to do 
	 * literal comparisions of foreign comparisons.  Whereas the other fields 
	 * are fine if we store a "&agrave;" or "ˆ" so long as it displays correctly,
	 * the multiselect fields must get the $current_value to be EXACTLY equal
	 * to the available options, otherwise we won't know whether or not 
	 * to check the checkbox.
	 *
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=88
	 *
	 * @param string $current_value json-encoded array of selected values for the current post
	 * @return string
	 */
	public function get_edit_field_instance($current_value) {

		$this->id 					= $this->name; 

		$optiontpl = '';
		$fieldtpl = '';
		$wrappertpl = '';

		$optiontpl = CCTM::load_tpl(
			array('fields/options/'.$this->name.'.tpl'
				, 'fields/options/_'.$this->type.'.tpl'
				, 'fields/options/_checkbox.tpl'
			)
		);

/*
		$fieldtpl = CCTM::load_tpl(
			array('fields/elements/'.$this->name.'.tpl'
				, 'fields/elements/_'.$this->type.'.tpl'
				, 'fields/elements/_default.tpl'
			)
		);
*/
		
		$wrappertpl = CCTM::load_tpl(
			array('fields/wrappers/'.$this->name.'.tpl'
				, 'fields/wrappers/_'.$this->type.'.tpl'
				, 'fields/wrappers/_default.tpl'
			)
		);
		
		// $current_values_arr: represents what's actually been selected.
		$current_values_arr = (array) json_decode(html_entity_decode($current_value), true );
	
		// Bring the foreign characters back from the dead.  We need this extra step 
		// because we have to do exact comparisons to see if the options are selected or not.
		if ( $current_values_arr and is_array($current_values_arr) ) {
			foreach ( $current_values_arr as $i => $v ) {
				$current_values_arr[$i] = trim(CCTM::charset_decode_utf_8($v));
			}
		}

		// $this->options: represents what's _available_ to be selected.
		// Some error messaging: the options thing is enforced at time of def creation too,
		// but we're doing it here too just to play it safe.
		if ( !isset($this->options) || !is_array($this->options) ) {
			return sprintf('<p><strong>%$1s</strong> %$2s %$3s</p>'
				, __('Custom Content Error', CCTM_TXTDOMAIN)
				, __('No options supplied for the following custom field: ', CCTM_TXTDOMAIN)
				, $this->name
			);
		}

		// Get the options!
		// we use a for loop so we can read places out of 2 similar arrays: values & options
		$opt_cnt = count($this->options);
		for ( $i = 0; $i < $opt_cnt; $i++ ) {
			
			// initialize
			$hash = $this->get_props();
			$hash['is_checked'] = '';
			$hash['is_selected'] = '';
			$hash['option'] = '';
			$hash['value'] = '';
			$hash['i'] = $i;
			
			if (isset($this->options[$i])) {
				$hash['option'] = CCTM::charset_decode_utf_8($this->options[$i]);
			}
			
			if (isset($this->values[$i])) {
				$hash['value'] = CCTM::charset_decode_utf_8($this->values[$i]);
			}
			// Simplistic behavior if we don't use key=>value pairs
			if ( !$this->use_key_values ) {
				$hash['value'] = $hash['option'];
			}

			if ( is_array($current_values_arr) && in_array( trim($hash['value']), $current_values_arr) ) {
				$hash['is_checked'] = 'checked="checked"';
				$hash['is_selected'] = 'selected="selected"';
				
			}
		
			$this->content .= CCTM::parse($optiontpl, $hash);
		}

		return CCTM::parse($wrappertpl, $this->get_props());

	}

	//------------------------------------------------------------------------------
	/**
	 * Note that the HTML in $option_html should match the JavaScript version of 
	 * the same HTML in js/dropdown.js (see the append_dropdown_option() function).
	 * I couldn't think of a clean way to do this, but the fundamental problem is 
	 * that both PHP and JS need to draw the same HTML into this form:
	 * PHP draws it when an existing definition is *edited*, whereas JS draws it
	 * when you dynamically *create* new dropdown options.
	 *
	 * @param mixed $def	nested array of existing definition.
	 */
	public function get_edit_field_definition($def) {
		$is_checked = '';
		$readonly_str = ' readonly="readonly"';
		if (isset($def['use_key_values']) && $def['use_key_values']) {
			$is_checked = 'checked="checked"';
			$readonly_str = '';
		}

			
		// Label
		$out = '<div class="'.self::wrapper_css_class .'" id="label_wrapper">
			 		<label for="label" class="'.self::label_css_class.'">'
			 			.__('Label', CCTM_TXTDOMAIN).'</label>
			 		<input type="text" name="label" class="'.self::css_class_prefix.'text" id="label" value="'.htmlspecialchars($def['label']) .'"/>
			 		' . $this->get_translation('label').'
			 	</div>';
		// Name
		$out .= '<div class="'.self::wrapper_css_class .'" id="name_wrapper">
				 <label for="name" class="cctm_label cctm_text_label" id="name_label">'
					. __('Name', CCTM_TXTDOMAIN) .
			 	'</label>
				 <input type="text" name="name" class="cctm_text" id="name" value="'.htmlspecialchars($def['name']) .'"/>'
				 . $this->get_translation('name') .'
			 	</div>';
			 	
		// Default Value
		$out .= '<div class="'.self::wrapper_css_class .'" id="default_value_wrapper">
			 	<label for="default_value" class="cctm_label cctm_text_label" id="default_value_label">'
			 		.__('Default Value', CCTM_TXTDOMAIN) .'</label>
			 		<input type="text" name="default_value" class="cctm_text" id="default_value" value="'. CCTM::charset_decode_utf_8($def['default_value'])
			 		.'"/>
			 	' . $this->get_translation('default_value') .'
			 	</div>';

		// Extra
		$out .= '<div class="'.self::wrapper_css_class .'" id="extra_wrapper">
			 		<label for="extra" class="'.self::label_css_class.'">'
			 		.__('Extra', CCTM_TXTDOMAIN) .'</label>
			 		<input type="text" name="extra" class="cctm_text" id="extra" value="'
			 			.htmlspecialchars($def['extra']).'"/>
			 	' . $this->get_translation('extra').'
			 	</div>';

		// Class
		$out .= '<div class="'.self::wrapper_css_class .'" id="class_wrapper">
			 	<label for="class" class="'.self::label_css_class.'">'
			 		.__('Class', CCTM_TXTDOMAIN) .'</label>
			 		<input type="text" name="class" class="cctm_text" id="class" value="'
			 			.htmlspecialchars($def['class']).'"/>
			 	' . $this->get_translation('class').'
			 	</div>';

		// Use Key => Value Pairs?  (if not, the simple usage is simple options)
		$out .= '<div class="'.self::wrapper_css_class .'" id="use_key_values_wrapper">
				 <label for="use_key_values" class="cctm_label cctm_checkbox_label" id="use_key_values_label">'
					. __('Distinct options/values?', CCTM_TXTDOMAIN) .
			 	'</label>
				 <br />
				 <input type="checkbox" name="use_key_values" class="cctm_checkbox" id="use_key_values" value="1" onclick="javascript:toggle_readonly();" '. $is_checked.'/> <span>'.$this->descriptions['use_key_values'].'</span>
			 	</div>';
			
		// OPTIONS
		$option_cnt = 0;
		if (isset($def['options'])) {
			$option_cnt = count($def['options']);
		}

		// using the parse function because this got too crazy with escaping single quotes
		$hash = array();
		$hash['option_cnt'] 	= $option_cnt;
		$hash['delete'] 		= __('Delete');
		$hash['options'] 		= __('Options', CCTM_TXTDOMAIN);
		$hash['values']			= __('Stored Values', CCTM_TXTDOMAIN);
		$hash['add_option'] 	= __('Add Option',CCTM_TXTDOMAIN);
		$hash['set_as_default'] = __('Set as Default', CCTM_TXTDOMAIN);		
		
		$tpl = '
			<table id="dropdown_options">
				<thead>
				<td width="200"><label for="options" class="cctm_label cctm_select_label" id="cctm_label_options">[+options+]</label></td>
				<td width="200"><label for="options" class="cctm_label cctm_select_label" id="cctm_label_options">[+values+]</label></td>
				<td>
				 <span class="button" onclick="javascript:append_dropdown_option(\'dropdown_options\',\'[+delete+]\',\'[+set_as_default+]\',\'[+option_cnt+]\');">[+add_option+]</span>
				</td>
				</thead>';
				
		$out .= CCTM::parse($tpl, $hash);
		
		// this html should match up with the js html in manager.js
		$option_html = '
			<tr id="%s">
				<td><input type="text" name="options[]" id="option_%s" value="%s"/></td>
				<td><input type="text" name="values[]" id="value_%s" value="%s" class="possibly_gray"'.$readonly_str.'/></td>
				<td><span class="button" onclick="javascript:remove_html(\'%s\');">%s</span>
				<span class="button" onclick="javascript:set_as_default(\'%s\');">%s</span></td>
			</tr>';


		$opt_i = 0; // used to uniquely ID options.
		if ( !empty($def['options']) && is_array($def['options']) ) {

			$opt_cnt = count($def['options']);
			for ( $i = 0; $i < $opt_cnt; $i++ ) {
				// just in case the array isn't set
				$option_txt = '';
				if (isset($def['options'][$i])) {
					$option_txt = CCTM::charset_decode_utf_8(trim($def['options'][$i]));
				}
				$value_txt = '';
				if (isset($def['values'][$i])) {
					$value_txt = CCTM::charset_decode_utf_8(trim($def['values'][$i]));
				}
				
				$option_css_id = 'cctm_dropdown_option'.$opt_i;
				$out .= sprintf($option_html
					, $option_css_id
					, $opt_i
					, $option_txt
					, $opt_i
					, $value_txt
					, $option_css_id, __('Delete') 
					, $opt_i
					, __('Set as Default') 
				);
				$opt_i = $opt_i + 1;
			}
		}
			
		$out .= '</table>'; // close id="dropdown_options" 
				
		// Description	 
		$out .= '<div class="'.self::wrapper_css_class .'" id="description_wrapper">
			 	<label for="description" class="'.self::label_css_class.'">'
			 		.__('Description', CCTM_TXTDOMAIN) .'</label>
			 	<textarea name="description" class="cctm_textarea" id="description" rows="5" cols="60">'
			 		.htmlspecialchars($def['description'])
			 		.'</textarea>
			 	' . $this->get_translation('description').'
			 	</div>';

		// Output Filter
		$out .= $this->format_available_output_filters($def);
		 
		return $out;
	}

	//------------------------------------------------------------------------------
	/**
	 * Validate and sanitize any submitted data. Used when editing the definition for 
	 * this type of element. Default behavior here is require only a unique name and 
	 * label. Override this if customized validation is required.
	 *
	 * @param	array	$posted_data = $_POST data
	 * @return	array	filtered field_data that can be saved OR can be safely repopulated
	 *					into the field values.
	 */
	public function save_definition_filter($posted_data) {
		$posted_data = parent::save_definition_filter($posted_data);		
		if ( empty($posted_data['options']) ) {
			$this->errors['options'][] = __('At least one option is required.', CCTM_TXTDOMAIN);
		}
		return $posted_data; // filtered data
	}
}


/*EOF*/