<?php
/**
 * CCTM_checkbox
 *
 * Implements an HTML text input.
 *
 * @package CCTM_FormElement
 */


class CCTM_checkbox extends CCTM_FormElement
{
	public $props = array(
		'label' => '',
		'name' => '',
		'description' => '',
		// checked_by_default determines whether 'checked_value' or 'unchecked_value' is passed to
		// the current value for new field instances.  This value should be 1 (checked) or 0 (unchecked)
		'checked_by_default' => '0',
		'checked_value' => '1',
		'unchecked_value' => '0',
		'class' => '',
		'extra' => '',
		'is_checked' => '',
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
		return __('Checkbox', CCTM_TXTDOMAIN);
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
		return __('Checkbox fields implement the standard <input="checkbox"> element.
			"Extra" parameters, e.g. "alt" can be specified in the definition.', CCTM_TXTDOMAIN);
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
		return 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Checkbox';
	}


	//------------------------------------------------------------------------------
	/**
	 * Some custom handling here because the checkbox fields are super-simple.
	 *
	 * @return string	HTML to be used in the WP manager for an instance of this type of element. 
	 */
	public function get_create_field_instance() {
		if ( $this->checked_by_default) {
			$current_value = $this->checked_value;
		}
		else {
			$current_value = $this->unchecked_value;
		}
		return $this->get_edit_field_instance($current_value); // pass on to
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param string  $current_value
	 * @return string
	 */
	public function get_edit_field_instance($current_value) {

		$this->is_checked = '';
		if ($current_value == $this->checked_value) {
			$this->is_checked = 'checked="checked"';
		}

		$fieldtpl = CCTM::load_tpl(
			array('fields/elements/'.$this->name.'.tpl'
				, 'fields/elements/_'.$this->type.'.tpl'
				, 'fields/elements/_default.tpl'
			)
		);

		$wrappertpl = CCTM::load_tpl(
			array('fields/wrappers/'.$this->name.'.tpl'
				, 'fields/wrappers/_'.$this->type.'.tpl'
				, 'fields/wrappers/_default.tpl'
			)
		);

		$this->id      = $this->name;
		$this->value    = htmlspecialchars($this->checked_value);
		$this->content = CCTM::parse($fieldtpl, $this->get_props());
		return CCTM::parse($wrappertpl, $this->get_props());

	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param mixed   current definition array
	 * @param array $def instance of the $props array.
	 * @return string
	 */
	public function get_edit_field_definition($def) {
		$is_checked = '';
		if (isset($def['checked_by_default']) && $def['checked_by_default']==1) {
			$is_checked = 'checked="checked"';
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

		// Value when Checked
		$out .= '<div class="'.self::wrapper_css_class .'" id="checked_value_wrapper">
				 <label for="checked_value" class="cctm_label cctm_text_label" id="checked_value_label">'
			. __('Value when checked', CCTM_TXTDOMAIN) .
			'</label>
				 <input type="text" name="checked_value" size="8" class="cctm_text" id="checked_value" value="'.htmlspecialchars($def['checked_value']) .'"/>'
			. $this->get_translation('checked_value') .'
			 	</div>';

		// Value when Unchecked
		$out .= '<div class="'.self::wrapper_css_class .'" id="unchecked_value_wrapper">
				 <label for="unchecked_value" class="cctm_label cctm_text_label" id="unchecked_value_label">'
			. __('Value when Unchecked', CCTM_TXTDOMAIN) .
			'</label>
				 <input type="text" name="unchecked_value" size="8" class="cctm_text" id="unchecked_value" value="'.htmlspecialchars($def['unchecked_value']) .'"/>'
			. $this->get_translation('unchecked_value') .'
			 	</div>';
		// Is Checked by Default?
		$out .= '<div class="'.self::wrapper_css_class .'" id="checked_by_default_wrapper">
				 <label for="checked_by_default" class="cctm_label cctm_checkbox_label" id="checked_by_default_label">'
			. __('Checked by default?', CCTM_TXTDOMAIN) .
			'</label>
				 <br />
				 <input type="checkbox" name="checked_by_default" class="cctm_checkbox" id="checked_by_default" value="1" '. $is_checked.'/> <span>'.$this->descriptions['checked_by_default'].'</span>
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

		// Description
		$out .= '<div class="'.self::wrapper_css_class .'" id="description_wrapper">
			 	<label for="description" class="'.self::label_css_class.'">'
			.__('Description', CCTM_TXTDOMAIN) .'</label>
			 	<textarea name="description" class="cctm_textarea" id="description" rows="5" cols="60">'
			. htmlspecialchars($def['description'])
			.'</textarea>
			 	' . $this->get_translation('description').'
			 	</div>';

		// Output Filter
		$out .= $this->format_available_output_filters($def);

		return $out;
	}


	//------------------------------------------------------------------------------
	/**
	 * Handle the "checked by default" option
	 *
	 * @param array $posted_data -- a copy of $_POST
	 * @return array	filtered $_POST data
	 */
	public function save_definition_filter($posted_data) {
		$posted_data = parent::save_definition_filter($posted_data);

		if (!isset($posted_data['checked_by_default'])) {
			$posted_data['checked_by_default'] = 0; // set it
		}

		return $posted_data;
	}


	//------------------------------------------------------------------------------
	/**
	 * Here we do some smoothing of the checkbox warts... normally if the box is not
	 * checked, no value is sent in the $_POST array.  But that's a pain in the ass
	 * when it comes time to read from the database, so here we toggle between
	 * 'checked_value' and 'unchecked_value' to force a value under all circumstances.
	 *
	 * See parent function for full documentation.
	 *
	 * @param mixed   $posted_data $_POST data
	 * @param string  $field_name: the unique name for this instance of the field
	 * @return string whatever value you want to store in the wp_postmeta table where meta_key = $field_name
	 */
	public function save_post_filter($posted_data, $field_name) {
		if ( isset($posted_data[ CCTM_FormElement::post_name_prefix . $field_name ]) ) {
			return $this->checked_value;
		}
		else {
			return $this->unchecked_value;
		}
	}


}


/*EOF*/