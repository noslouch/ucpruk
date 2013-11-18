<?php
/**
 * CCTM_wysiwyg
 *
 * Implements an WYSIWYG textarea input (a textarea with formatting controls).
 *
 * @package CCTM_FormElement
 */


class CCTM_wysiwyg extends CCTM_FormElement
{
	public $props = array(
		'label' => '',
		'name' => '',
		'description' => '',
		'class' => '',
		'extra' => 'cols="80" rows="10"',
		'default_value' => '',
		'output_filter' => 'do_shortcode',
		// 'type' => '', // auto-populated: the name of the class, minus the CCTM_ prefix.
	);

	//------------------------------------------------------------------------------
	/**
	 * This function provides a name for this type of field. This should return plain
	 * text (no HTML). The returned value should be localized using the __() function.
	 *
	 * @return string
	 */
	public function get_name() {
		return __('WYSIWYG', CCTM_TXTDOMAIN);
	}


	//------------------------------------------------------------------------------
	/**
	 * This function gives a description of this type of field so users will know
	 * whether or not they want to add this type of field to their custom content
	 * type. The returned value should be localized using the __() function.
	 *
	 * @return string text description
	 */
	public function get_description() {
		return __('What-you-see-is-what-you-get (WYSIWYG) fields implement a <textarea> element with formatting controls.
			"Extra" parameters, e.g. "cols" can be specified in the definition, however a minimum size is required to make room for the formatting controls.', CCTM_TXTDOMAIN);
	}


	//------------------------------------------------------------------------------
	/**
	 * This function should return the URL where users can read more information about
	 * the type of field that they want to add to their post_type. The string may
	 * be localized using __() if necessary (e.g. for language-specific pages)
	 *
	 * @return string  e.g. http://www.yoursite.com/some/page.html
	 */
	public function get_url() {
		return 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/WYSIWYG';
	}


	//------------------------------------------------------------------------------
	/**
	 * See Issue http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=138
	 * and this one: http://keighl.com/2010/04/switching-visualhtml-modes-with-tinymce/
	 *
	 * @param string  $current_value current value for this field.
	 * @return string
	 */
	public function get_edit_field_instance($current_value) {

		$this->id      = $this->name;

		$wrappertpl = CCTM::load_tpl(
			array('fields/wrappers/'.$this->name.'.tpl'
				, 'fields/wrappers/_'.$this->type.'.tpl'
				, 'fields/wrappers/_default.tpl'
			)
		);
		
		$settings = array();
		$settings['editor_class'] = $this->class;
		$settings['textarea_name'] = $this->name_prefix.$this->name;

		// see http://nacin.com/tag/wp_editor/
		ob_start();
		wp_editor($current_value, $this->id_prefix.$this->id, $settings);
		$this->content = ob_get_clean();

		$this->add_label = __('Add', CCTM_TXTDOMAIN);

		return CCTM::parse($wrappertpl, $this->get_props());
	}


	//------------------------------------------------------------------------------
	/**
	 * @param array   $def field definition; see the $props array
	 * @return string
	 */
	public function get_edit_field_definition($def) {

		$is_repeatable_checked = '';
		if (isset($def['is_repeatable']) && $def['is_repeatable'] == 1) {
			$is_repeatable_checked = 'checked="checked"';
		}

		// Label
		$out = '<div class="'.self::wrapper_css_class .'" id="label_wrapper">
			 		<label for="label" class="'.self::label_css_class.'">'
			.__('Label', CCTM_TXTDOMAIN).'</label>
			 		<input type="text" name="label" class="cctm_text" id="label" value="'.htmlspecialchars($def['label']) .'"/>
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
			 		<input type="text" name="default_value" class="cctm_text" id="default_value" value="'. htmlspecialchars($def['default_value'])
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

/*
		// Is Repeatable?
		$out .= '<div class="'.self::wrapper_css_class .'" id="is_repeatable_wrapper">
				 <label for="is_repeatable" class="cctm_label cctm_checkbox_label" id="is_repeatable_label">'
			. __('Is Repeatable?', CCTM_TXTDOMAIN) .
			'</label>
				 <br />
				 <input type="checkbox" name="is_repeatable" class="cctm_checkbox" id="is_repeatable" value="1" '. $is_repeatable_checked.'/> <span>'.$this->descriptions['is_repeatable'].'</span>
			 	</div>';
*/

		// Description
		$out .= '<div class="'.self::wrapper_css_class .'" id="description_wrapper">
			 	<label for="description" class="'.self::label_css_class.'">'
			.__('Description', CCTM_TXTDOMAIN) .'</label>
			 	<textarea name="description" class="cctm_textarea" id="description" rows="5" cols="60">'.htmlspecialchars($def['description']).'</textarea>
			 	' . $this->get_translation('description').'
			 	</div>';

		// Output Filter
		$out .= $this->format_available_output_filters($def);
			 	
		return $out;
	}

	//------------------------------------------------------------------------------
	/**
	 * Custom filter on the name due to WP's limitations:
	 * http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=271
	 */
	public function save_definition_filter($posted_data) {
	
		$posted_data = parent::save_definition_filter($posted_data);
		
		// Are there any invalid characters? 1st char. must be a letter (req'd for valid prop/func names)
		if ( !empty($posted_data['name']) && !preg_match('/^[a-z]*$/', $posted_data['name'])) {
			$this->errors['name'][] = 
				__('Due to WordPress limitations, WYSIWYG fields can contain ONLY lowercase letters.', CCTM_TXTDOMAIN);
			$posted_data['name'] = preg_replace('/[^a-z]/', '', $posted_data['name']);
		}
		
		return $posted_data;	
	}
}


/*EOF*/