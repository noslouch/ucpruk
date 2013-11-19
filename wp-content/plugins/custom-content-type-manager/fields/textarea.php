<?php
/**
 * CCTM_textarea
 *
 * Implements an HTML textarea input.
 *
 * @package CCTM_FormElement
 */


class CCTM_textarea extends CCTM_FormElement
{

	public $props = array(
		'label' => '',
		'name' => '',
		'description' => '',
		'class' => '',
		'extra' => '',
		'default_value' => '',
		// 'type' => '', // auto-populated: the name of the class, minus the CCTM_ prefix.
		// 'sort_param' => '', // handled automatically
	);



	/**
	 * Multi/repeatable support
	 */
	public function admin_init() {
		// we double-up on the text fields.
		wp_register_script('cctm_text', CCTM_URL.'/js/text.js');
		wp_enqueue_script('cctm_text');
	}


	//------------------------------------------------------------------------------
	/**
	 * This function provides a name for this type of field. This should return plain
	 * text (no HTML). The returned value should be localized using the __() function.
	 *
	 * @return string
	 */
	public function get_name() {
		return __('Textarea', CCTM_TXTDOMAIN);
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
		return __('Textarea fields implement the standard <textarea> element.
			"Extra" parameters, e.g. "cols" can be specified in the definition.', CCTM_TXTDOMAIN);
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
		return 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Textarea';
	}


	//------------------------------------------------------------------------------
	/**
	 * <label for="[+name+]" class="cctm_label cctm_textarea_label" id="cctm_label_[+name+]">[+label+]</label>
	 * <textarea name="[+name+]" class="cctm_textarea" id="[+name+]" [+extra+]>[+value+]</textarea>
	 *
	 * @param string  $current_value current value for this field.
	 * @return string
	 */
	public function get_edit_field_instance($current_value) {

		$this->id   = $this->name;
		$this->value = $current_value;

		$fieldtpl = '';
		$wrappertpl = '';

		// Multi-versions
		if ($this->is_repeatable) {
			$fieldtpl = CCTM::load_tpl(
				array('fields/elements/'.$this->name.'.tpl'
					, 'fields/elements/_'.$this->type.'_multi.tpl'
				)
			);

			$wrappertpl = CCTM::load_tpl(
				array('fields/wrappers/'.$this->name.'.tpl'
					, 'fields/wrappers/_'.$this->type.'_multi.tpl'
				)
			);

			$this->i = 0;

			$values = (array) json_decode($current_value);

			foreach ($values as $v) {
				$this->value = htmlspecialchars( html_entity_decode($v) );
				$this->content .= CCTM::parse($fieldtpl, $this->get_props());
				$this->i   = $this->i + 1;
			}
		}
		// Simple stuff
		else {
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

			$this->content = CCTM::parse($fieldtpl, $this->get_props());
		}

		// wrap it.
		$this->add_label = __('Add', CCTM_TXTDOMAIN);
		return CCTM::parse($wrappertpl, $this->get_props());
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param mixed   $def field definition; see the $props array
	 * @return unknown
	 */
	public function get_edit_field_definition($def) {
		$is_checked = '';
		if (isset($def['is_repeatable']) && $def['is_repeatable'] == 1) {
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

		// Is Repeatable?
		$out .= '<div class="'.self::wrapper_css_class .'" id="is_repeatable_wrapper">
				 <label for="is_repeatable" class="cctm_label cctm_checkbox_label" id="is_repeatable_label">'
			. __('Is Repeatable?', CCTM_TXTDOMAIN) .
			'</label>
				 <br />
				 <input type="checkbox" name="is_repeatable" class="cctm_checkbox" id="is_repeatable" value="1" '. $is_checked.'/> <span>'.$this->descriptions['is_repeatable'].'</span>
			 	</div>';

		// Description
		$out .= '<div class="'.self::wrapper_css_class .'" id="description_wrapper">
			 	<label for="description" class="'.self::label_css_class.'">'
			.__('Description', CCTM_TXTDOMAIN) .'</label>
			 	<textarea name="description" class="cctm_textarea" id="description" rows="5" cols="60">'
			. htmlspecialchars($def['description']).'</textarea>
			 	' . $this->get_translation('description').'
			 	</div>';

		// Output Filter
		$out .= $this->format_available_output_filters($def);
			 	
		return $out;
	}


}


/*EOF*/