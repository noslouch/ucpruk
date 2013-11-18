<?php
/**
 * CCTM_date
 *
 * Implements a date input using the jQuery datepicker:
 * http://jqueryui.com/demos/datepicker/
 *
 * @package CCTM_FormElement
 */


class CCTM_date extends CCTM_FormElement
{
	public $props = array(
		'label' => '',
		'name' => '',
		'description' => '',
		'class' => '',
		'extra' => '',
		'date_format' => '',
		'default_value' => '',
		'evaluate_default_value' => 0,
		// 'type' => '', // auto-populated: the name of the class, minus the CCTM_ prefix.
	);

	//------------------------------------------------------------------------------
	/**
	 * Add some necessary Javascript
	 */
	public function admin_init() {
		wp_enqueue_script( 'jquery-ui-datepicker', CCTM_URL . '/js/datepicker.js', 'jquery-ui-core');
	}


	//------------------------------------------------------------------------------
	/**
	 * This function provides a name for this type of field. This should return plain
	 * text (no HTML). The returned value should be localized using the __() function.
	 *
	 * @return string
	 */
	public function get_name() {
		return __('Date', CCTM_TXTDOMAIN);
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
		return __('Use date fields to store dates, including years, months, and days.', CCTM_TXTDOMAIN);
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
		return 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Date';
	}


	//------------------------------------------------------------------------------
	/**
	 * Optionally evals the default value
	 *
	 * @return string HTML for the field
	 */
	public function get_create_field_instance() {

		if ($this->evaluate_default_value ) {
			$default_value = $this->default_value;
			$this->default_value = eval("return $default_value;");

		}

		if ($this->is_repeatable) {
			$this->default_value = json_encode(array($this->default_value));
		}

		return $this->get_edit_field_instance($this->default_value)
			. '<input type="hidden" name="_cctm_is_create" value="1" />';;
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param mixed   $current_value current value for this field.
	 * @return string
	 */
	public function get_edit_field_instance($current_value) {

		$this->id      = $this->name;

		$fieldtpl = '';
		$wrappertpl = '';

		// Multi-version of the field
		if ($this->is_repeatable) {
			$fieldtpl = CCTM::load_tpl(
				array('fields/elements/'.$this->name.'.tpl'
					, 'fields/elements/_'.$this->type.'_multi.tpl'
				)
			);

			$wrappertpl = CCTM::load_tpl(
				array('fields/wrappers/'.$this->name.'.tpl'
					, 'fields/wrappers/_'.$this->type.'_multi.tpl'
					, 'fields/wrappers/_text_multi.tpl'
				)
			);

			$this->i = 0;
			$values = (array) json_decode($current_value);
			//die(print_r($values,true));
			$this->content = '';
			foreach ($values as $v) {
				$this->value = htmlspecialchars( html_entity_decode($v) );
				$this->content .= CCTM::parse($fieldtpl, $this->get_props());
				$this->i   = $this->i + 1;
			}

		}
		// Singular
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

			$this->value = htmlspecialchars( html_entity_decode($current_value) );
			$this->content = CCTM::parse($fieldtpl, $this->get_props());
		}


		$this->add_label = __('Add', CCTM_TXTDOMAIN);
		return CCTM::parse($wrappertpl, $this->get_props());
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param mixed   $def field definition; see the $props array
	 * @return string
	 */
	public function get_edit_field_definition($def) {

		$is_checked = '';
		if (isset($def['evaluate_default_value'])) {
			$is_checked = 'checked="checked"';
		}

		$is_repeatable_checked = '';
		if (isset($def['is_repeatable']) && $def['is_repeatable'] == 1) {
			$is_repeatable_checked = 'checked="checked"';
		}

		// Option - select
		$date_format = array();
		$date_format['mm/dd/yy']        = '';
		$date_format['yy-mm-dd']        = ''; // note this is really yyyy-mm-dd
		$date_format['d M, y']         = '';
		$date_format['d MM, y']        = '';
		$date_format['DD, d MM, yy']       = '';
		$date_format["'day' d 'of' MM 'in the year' yy"] = '';


		if ( $def['date_format'] == 'mm/dd/yy' ) {
			$date_format['mm/dd/yy'] = 'selected="selected"';
		}
		if ( $def['date_format'] == 'yy-mm-dd' ) {
			$date_format['yy-mm-dd'] = 'selected="selected"';
		}
		if ( $def['date_format'] == 'd M, y' ) {
			$date_format['d M, y'] = 'selected="selected"';
		}
		if ( $def['date_format'] == 'd MM, y' ) {
			$date_format['d MM, y'] = 'selected="selected"';
		}
		if ( $def['date_format'] == 'DD, d MM, yy' ) {
			$date_format['DD, d MM, yy'] = 'selected="selected"';
		}
		if ( $def['date_format'] == "'day' d 'of' MM 'in the year' yy" ) {
			$date_format["'day' d 'of' MM 'in the year' yy"] = 'selected="selected"';
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
		// Evaluate Default Value (use PHP eval)
		$out .= '<div class="'.self::wrapper_css_class .'" id="evaluate_default_value_wrapper">
				 <label for="evaluate_default_value" class="cctm_label cctm_checkbox_label" id="evaluate_default_value_label">'
			. __('Use PHP eval to calculate the default value? (Omit the php tags, e.g. <code>date(\'Y-m-d\')</code>).', CCTM_TXTDOMAIN) .
			'</label>
				 <br />
				 <input type="checkbox" name="evaluate_default_value" class="cctm_checkbox" id="evaluate_default_value" value="1" '. $is_checked.'/> '
			.$this->descriptions['evaluate_default_value'].'
			 	</div>';


		// Extra
		$out .= '<div class="'.self::wrapper_css_class .'" id="extra_wrapper">
			 		<label for="extra" class="'.self::label_css_class.'">'
			.__('Extra', CCTM_TXTDOMAIN) .'</label>
			 		<input type="text" name="extra" class="cctm_text" id="extra" value="'
			.htmlspecialchars($def['extra']).'"/>
			 	' . $this->get_translation('extra').'
			 	</div>';

		// Date Format
		$out .= '<div class="'.self::wrapper_css_class .'" id="date_format_wrapper">
			 		<label for="date_format" class="'.self::label_css_class.'">'
			.__('Date Format', CCTM_TXTDOMAIN) .'</label>
					<select id="date_format" name="date_format">
						<option value="mm/dd/yy" '.$date_format['mm/dd/yy'].'>Default - mm/dd/yy</option>
						<option value="yy-mm-dd" '.$date_format['yy-mm-dd'].'>MySQL - yyyy-mm-dd</option>
						<option value="d M, y" '.$date_format['d M, y'].'>Short - d M, y</option>
						<option value="d MM, y" '.$date_format['d MM, y'].'>Medium - d MM, y</option>
						<option value="DD, d MM, yy" '.$date_format['DD, d MM, yy'].'>Full - DD, d MM, yy</option>
						<option value="\'day\' d \'of\' MM \'in the year\' yy" '.$date_format["'day' d 'of' MM 'in the year' yy"].'>With text - \'day\' d \'of\' MM \'in the year\' yy</option>
					</select>
					<span class="cctm_description">'.__('If you need to sort your dates, it is recommended to use the MySQL date format. Change how the date displays using Output Filters in your template files.', CCTM_TXTDOMAIN).'</span>
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
				 <input type="checkbox" name="is_repeatable" class="cctm_checkbox" id="is_repeatable" value="1" '. $is_repeatable_checked.'/> <span>'.$this->descriptions['is_repeatable'].'</span>
			 	</div>';

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


}


/*EOF*/