<?php
/**
 * This class can be extended for each type of custom field, e.g. dropdown, textarea, etc.
 * so that instances of these field types can be created and attached to a post_type.
 * The notion of a "class" or "object" has two layers here: First there is a general class
 * of form element (e.g. dropdown) which is implemented inside of a given post_type. E.g.
 * a "State" dropdown might be attached to an "Address" post_type. Secondly, instances of
 * the post_type create instances of the "State" field are created with each "Address" post.
 * The second layer here is really another way of saying that each field has its own value.
 *
 * The functions in this class serve the following primary purposes:
 *  1. Generate forms which allow a custom field definition to be created and edited.
 *   2.  Generate form elements which allow an instance of custom field to be displayed
 *   when a post is created or edited
 *  3. Retrieve and filter the meta_value stored for a given post and return it to the
 *   theme file, e.g. if an image id is stored in the meta_value, the filter function
 *   can translate this id into a full image tag.
 *
 * When a new type of custom field is defined, all the abstract functions must be implemented.
 * This is how we force the children classes to implement their own behavior. Bruhaha.
 * Usually the forms to create and edit a definition or element are the same, but if needed,
 * there are separate functions to create and edit a definition or value.
 *
 * @package CCTM_FormElement
 */


abstract class CCTM_FormElement {

	/**
	 * The $props array acts as a template which defines the properties for each 
	 * instance of this type of field. When added to a post_type, an instance of this data 
	 * structure is stored in the array of custom_fields (in CCTM::$data['custom_field_defs']).
	 *
	 * Some properties are required of all fields, some are automatically generated (see below), 
	 * but each type of custom field (i.e. each class that extends CCTM_FormElement) can have 
	 * whatever properties it needs in order to work, e.g. a dropdown field uses an 'options' 
	 * property to define a list of possible values.
	 *
	 * The following properties MUST be implemented:
	 * 'name'  => Unique name for an instance of this type of field; corresponds to wp_postmeta.meta_key for each post
	 * 'label' =>
	 * 'description' => a description of this type of field.
	 *
	 * The following properties are set automatically:
	 *
	 *  'type'    => the name of this class, minus the CCTM_ prefix.
	 */
	public $element_i = 0; // used to increment CSS ids as we wrap multiple elements

	// Contains reusable localized descriptions of common field definition elements, e.g. 'label'
	public $descriptions = array();

	// Stores any errors with fields.  The structure is array( 'field_name' => array('Error msg1','Error msg2') )
	public $errors = array();

	// tracks field instances
	//public $i = 0;

	/**
	 * The $props array acts as a template which defines the properties for each instance of this type of field.
	 * When a custom field is created, an instance of this data structure is stored in the array of custom_fields_defs.
	 * Some properties are required of all fields (see below), some are automatically generated (e.g. 'type'), but
	 * each type of custom field (i.e. each class that extends CCTM_FormElement) can have whatever properties it needs
	 * in order to work, e.g. a dropdown field uses an 'options' property to define a list of possible values.
	 *
	 * Below is a sample array that most fields will utilize.
	 *
	 * The following properties MUST be implemented:
	 * 'name'  => Unique name for an instance of this type of field; corresponds to wp_postmeta.meta_key for each post
	 * 'label' =>
	 * 'description' => a description of this type of field.
	 *
	 * The following properties are set automatically:
	 *
	 *  'type'    => the name of this class, minus the CCTM_ prefix.
	 */
	protected $props = array(
		'label' => '',
		'name' => '',
		'description' => '',
		'class' => '',
		'extra' => '',
		'default_value' => '',
		'output_filter' => '',
		// set in the __construct
		'id_prefix'	=> '', 
		'name_prefix' => '',
		'css_prefix' => '',
		'i' => 0,
	);

	/**
	 * The $immutable array is used to set any attributes that will exist across all instances of this
	 * field type. These attributes will be stored with the field definition.  Any value here will overrite
	 * any value in the $props array.  The effect is similar to having hidden fields in your definition form:
	 * these values will ALWAYS be in the stored definitions, and they cannot be changed at runtime.
	 *
	 * 'type'    :  the classname, minus the prefix.
	 * 'hide_from_templates':  most custom fields are visible in the manager AND in the templates, but
	 *       setting this to true will hide the field from the sample templates.
	 *
	 * Do not use booleans here. Scalars only for values, please.
	 */
	protected $immutable = array(
		// 'type' => '', // auto-populated: the name of the class, minus the CCTM_ prefix.
		'hide_from_templates' => 0,
	);


	// Added to each key in the $_POST array, to avoid name pollution e.g. $_POST['cctm_firstname']
	
	// Can't use underscores due to issue 271 and WP 3.3 
	const post_name_prefix  = 'cctm_';
	const css_class_prefix  = 'cctm_';
	const css_id_prefix  = 'cctm';


	// CSS stuff
	// label_css_class: Always include this CSS class in generated input labels, e.g.
	//  <label for="xyz" class="cctm_label cctm_text_label" id="xyz_label">Address</label>
	const label_css_class    = 'cctm_label';
	const wrapper_css_class   = 'cctm_element_wrapper';
	const label_css_id_prefix   = 'cctm_label_';
	const css_class_description  = 'cctm_description';
	const error_css     = 'cctm_error'; // used for validation errors

	//! Magic Functions
	//------------------------------------------------------------------------------
	/**
	 * Add additional items if necessary, e.g. localizations of the $props by
	 * tying into the parent constructor, e.g.
	 *
	 *  public function __construct() {
	 *  parent::__construct();
	 *  $this->props['special_stuff'] = __('Translate me');
	 * }
	 *
	 */
	public function __construct() {
		// instantiate properties
		$this->immutable['type'] = preg_replace('/^'. CCTM::classname_prefix.'/', '', get_class($this));
		$this->props['id_prefix'] = self::css_id_prefix;
		$this->props['name_prefix'] = self::post_name_prefix;
		$this->props = array_merge($this->props, $this->immutable);

		// Run-time Localization
		$this->descriptions['button_label'] = __('How should the button be labeled?', CCTM_TXTDOMAIN);
		$this->descriptions['class'] = __('Add a CSS class to instances of this field. Use this to customize styling in the WP manager.', CCTM_TXTDOMAIN);
		$this->descriptions['extra'] = __('Any extra attributes for this text field, e.g. <code>size="10"</code>', CCTM_TXTDOMAIN);
		$this->descriptions['default_option'] = __('The default option will appear selected. Make sure it matches a defined option.', CCTM_TXTDOMAIN);
		$this->descriptions['default_value'] = __('The default value is presented to users when a new post is created.', CCTM_TXTDOMAIN);
		$this->descriptions['description'] = __('The description is visible when you view all custom fields or when you use the <code>get_custom_field_meta()</code> function.');
		$this->descriptions['description'] .= __('The following html tags are allowed:')
			. '<code>'.htmlspecialchars(CCTM::$allowed_html_tags).'</code>';
		$this->descriptions['evaluate_default_value'] = __('You can check this box if you want to enter a bit of PHP code into the default value field.');
		$this->descriptions['label'] = __('The label is displayed when users create or edit posts that use this custom field.', CCTM_TXTDOMAIN);
		$this->descriptions['name'] = __('The name identifies the meta_key in the wp_postmeta database table. The name should contain only letters, numbers, and underscores. You will use this name in your template functions to identify this custom field.', CCTM_TXTDOMAIN);
		$this->descriptions['name'] .= sprintf('<br /><span style="color:red;">%s</span>'
			, __('WARNING: if you change the name, you will have to update any template files that use the <code>get_custom_field()</code> or <code>print_custom_field()</code> functions or any other functions that reference this field by its name.', CCTM_TXTDOMAIN));
			
		$this->descriptions['is_repeatable'] = __('If selected, the user will be able to enter multiple instances of this field, e.g. multiple images. Storing multiple values infers storing an array of values, so you will have to use the "to_array" output filter, even if you only use one instance of the field.', CCTM_TXTDOMAIN);
						

		$this->descriptions['checked_value'] = __('What value should be stored in the database when this checkbox is checked?', CCTM_TXTDOMAIN);
		$this->descriptions['unchecked_value'] =  __('What value should be stored in the database when this checkbox is unchecked?', CCTM_TXTDOMAIN);
		$this->descriptions['checked_by_default'] =  __('Should this field be checked by default?', CCTM_TXTDOMAIN);
		$this->descriptions['output_filter'] =  __('How should values be displayed in your theme files?', CCTM_TXTDOMAIN);
		$this->descriptions['use_key_values'] = __('Check this to make the stored values distinct from the options displayed to the user, e.g. Option:"Red", Stored Value:"#ff0000;"', CCTM_TXTDOMAIN);
	}


	//------------------------------------------------------------------------------
	/**
	 * This is a magic interface to "controlled" class properties in $this->props
	 *
	 * @param string  $k
	 * @return string
	 */
	public function __get($k) {
		if ( isset($this->props[$k]) ) {
			return $this->props[$k];
		}
		else {
			return ''; // Error?
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * This is a magic interface to "controlled" class properties in $this->props
	 *
	 * @param string  $k
	 * @return boolean
	 */
	public function __isset($k) {
		if ( isset($this->props[$k]) ) {
			return true;
		}
		else {
			return false;
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * This is a magic interface to "controlled" class properties in $this->props
	 *
	 * @param string  $k representing the attribute name
	 * @param mixed   $v value for the requested attribute
	 */
	public function __set($k, $v) {
		if ( !in_array($k, array_keys($this->immutable)) ) {
			$this->props[$k] = $v;
		}
	}

	//------------------------------------------------------------------------------
	//! Abstract and Public Functions... Implement Me!
	//------------------------------------------------------------------------------
	/**
	 * This runs when the WP dashboard (i.e. admin area) is initialized.
	 * Override this function to register any necessary CSS/JS req'd by your field.
	 */
	public function admin_init() { }

	//------------------------------------------------------------------------------
	/**
	 * Generate select dropdown for listing and selecting the active output filter.
	 *
	 * @param mixed   $def is the existing field definition
	 * @return string	html dropdown
	 */
	public function format_available_output_filters($def) {

		$out = '<div class="'.self::wrapper_css_class .'" id="output_filter_wrapper">
			 	<label for="output_filter" class="cctm_label cctm_select_label" id="output_filter_label">'
			.__('Default Output Filter', CCTM_TXTDOMAIN) .'
			 		<a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/OutputFilters" target="_blank"><img src="'.CCTM_URL .'/images/question-mark.gif" width="16" height="16" /></a>
			 		</label>';

		$out .= '<select name="output_filter" class="cctm_select" id="output_filter">
				<option value="">'.__('None (raw)').'</option>
				';

		$available_output_filters = CCTM::get_available_output_filters();
		require_once(CCTM_PATH.'/includes/CCTM_OutputFilter.php');
		
		foreach ($available_output_filters as $filter => $filename) {
		
			require_once($filename);
			
			$classname = CCTM::classname_prefix . $filter;
		
			$Obj = new $classname();

			if ($Obj->show_in_menus) {
				$is_selected = '';
				if ( isset($def['output_filter']) && $def['output_filter'] == $filter ) {
					$is_selected = 'selected="selected"';
				}
				$out .= '<option value="'.$filter.'" '.$is_selected.'>'.$Obj->get_name().' ('.$filter.')</option>';
			}
			
		}

		$out .= '</select>
			' . $this->get_translation('output_filter')
			.'</div>';

		return $out;
	}


	//------------------------------------------------------------------------------
	/**
	 * This should return (not print) form elements that handle all the controls
	 * required to define this type of field.  The default properties (stored in
	 * $this->props)correspond to this class's public variables, e.g. name, label,
	 * etc. and should be defined at the top of the child class.
	 *
	 * The form elements you create should have names that correspond to the public
	 * $props variable. A populated array of $props will be stored with each custom
	 * field definition. (See notes on the CCTM data structure).
	 *
	 * Override this function in the rare cases when you need behavior that is specific
	 * to when you first define a field definition. Most of the time, the create/edit
	 * functions are nearly identical. When you create a field definition, the
	 * current values are the values hard-coded into the $props array at the top
	 * of the child FieldElement class; when editing a field definition, the current
	 * values are read from the database (the array should be the same structure as
	 * the $props array, but the values may differ).
	 *
	 * @return string HTML input fields
	 */
	public function get_create_field_definition() {
		return $this->get_edit_field_definition( $this->props );
	}


	//------------------------------------------------------------------------------
	/**
	 * get_create_field_instance
	 *
	 * This generates the field elements when a user creates a new post that uses a
	 * field of this type.  In most cases, the form elements generated for a new post
	 * are identical to the form elements generated when editing a post, so the default
	 * behavior is to set the current value to the default value and hand this off to
	 * the get_edit_field_instance() function.
	 *
	 * Override this function in the rare cases when you need behavior that is specific
	 * to when you create a post (e.g. to specify a dynamic default value).
	 * Most of the time, the create/edit functions are nearly identical.
	 *
	 * @return string HTML field(s)
	 */
	public function get_create_field_instance() {
		
		if($this->is_repeatable) {			
			$this->default_value = json_encode(array($this->default_value));
		}
		
		// Add this to flag that it's a new post.
		return $this->get_edit_field_instance($this->default_value) 
			. '<input type="hidden" name="_cctm_is_create" value="1" />';
	}

	//------------------------------------------------------------------------------
	/**
	 * This function gives a description of this type of field so users will know
	 * whether or not they want to add this type of field to their custom content
	 * type. The string should be no longer than 255 characters.
	 * The returned value should be localized using the __() function.
	 *
	 * @return string plain text description
	 */
	abstract public function get_description();

	//------------------------------------------------------------------------------
	/**
	 * get_edit_field_instance
	 *
	 * The form returned is what is displayed when a user is editing a post that contains
	 * an instance of this field type.
	 *
	 * @param string  $current_value is the current value for the field, as stored in the
	 *     wp_postmeta table for the post being edited.
	 * @return string HTML element.
	 */
	abstract public function get_edit_field_instance($current_value);

	//------------------------------------------------------------------------------
	/**
	 * This should return (not print) form elements that handle all the controls required to define this
	 * type of field.  The default properties correspond to this class's public variables,
	 * e.g. name, label, etc. The form elements you create should have names that correspond
	 * with the public $props variable. A populated array of $props will be stored alongside
	 * the custom-field data for the containing post-type.
	 *
	 * @param mixed   $current_values should be an associative array.
	 * @return string HTML input fields
	 */
	abstract public function get_edit_field_definition($current_values);

	//------------------------------------------------------------------------------
	/**
	 * This function provides a name for this type of field. This should return plain
	 * text (no HTML). The string should be no longer than 32 characters.
	 * The returned value should be localized using the __() function.
	 *
	 * @return string
	 */
	abstract public function get_name();


	//------------------------------------------------------------------------------
	/**
	 * This function should return the URL where users can read more information about
	 * the type of field that they want to add to their post_type. The string may
	 * be localized using __() if necessary (e.g. for language-specific pages)
	 * 3rd party field devs can use this to point to their awesome docs!
	 *
	 * @return string  e.g. http://www.yoursite.com/some/page.html
	 */
	abstract public function get_url();


	//------------------------------------------------------------------------------
	/**
	 * Formats errors.  This function is useful only for devs as they develop their
	 * own types of custom fields.
	 *
	 * @return string HTML describing any errors tracked in the class $errors variable
	 */
	public function format_errors() {
		$error_str = '';
		foreach ( $this->errors as $tmp => $errors ) {
			foreach ( $errors as $e ) {
				$error_str .= '<li>'.$e.'</li>
				';
			}
		}

		return sprintf('<div class="error">
			<h3>%1$s</h3>
			<ul style="margin-left:30px">
				%2$s
			</ul>
			</div>'
			, __('There were errors in your custom field definition.', CCTM_TXTDOMAIN)
			, $error_str
		);
	}




	//------------------------------------------------------------------------------
	/**
	 * Return URL to a 48x48 PNG image that should represent this type of field.
	 * Looks inside the images/custom-fields directory.  3rd party devs should
	 * override this function.
	 *
	 * @return string URL for image, e.g. http://mysite/images/coolio.png
	 */
	public function get_icon() {
		$field_type = str_replace(
			CCTM::classname_prefix,
			'',
			get_class($this) );
		// Default image
		if (file_exists(CCTM_PATH.'/images/custom-fields/'.$field_type.'.png')) {
			return CCTM_URL.'/images/custom-fields/'.$field_type.'.png';
		}
		// Snap, we can't find it.
		else {
			return CCTM_URL.'/images/custom-fields/default.png';
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Accessor to $this->props
	 */
	public function get_props() {
		return $this->props;
	}

	//------------------------------------------------------------------------------
	/**
	 * Implement this function if your custom field has global settings that apply
	 * to *all* instances of the field (e.g. an API key). If this function returns
	 * anything except for false, then a menu item will be created for the custom
	 * field type. The function (if implemented), should return an HTML form that
	 * allows users to modify the settings. The function must also handle the form
	 * submission.
	 *
	 * @return mixed: false or HTML form
	 */
	public function get_settings_page() {
		return false;
	}


	//------------------------------------------------------------------------------
	/**
	 * A little clearing house for getting wrapped translations for various components
	 *
	 * @param string  $item to identify which description you want.
	 * @return string HTML localized description
	 */
	public function get_translation($item) {
		$tpl = '<span class="cctm_description">%s</span>';
		return sprintf($tpl, $this->descriptions[$item]);
	}


	//------------------------------------------------------------------------------
	/**
	 * This function allows for custom handling of submitted post/page data just before
	 * it is saved to the database; it can be thought of loosely as the "on save" event.
	 * Data validation and filtering should happen here, although it's difficult to
	 * enforce any validation errors due to lack of an appropriate event.
	 *
	 * Output should be whatever string value you want to store in the wp_postmeta table
	 * for the post in question. Default behavior is to simply trim the values.
	 *
	 * Note that the field name in the $_POST array is prefixed by CCTM_FormElement::post_name_prefix,
	 * e.g. the value for you 'my_field' custom field is stored in $_POST['cctm_my_field']
	 * (where CCTM_FormElement::post_name_prefix = 'cctm_'). This is done to avoid name
	 * collisions in the $_POST array.
	 *
	 * @param mixed   $posted_data $_POST data
	 * @param string  $field_name: the unique name for this instance of the field
	 * @return string whatever value you want to store in the wp_postmeta table where meta_key = $field_name
	 */
	public function save_post_filter($posted_data, $field_name) {
	
		global $wp_version;
	
		if ( isset($posted_data[ CCTM_FormElement::post_name_prefix . $field_name ]) ) {

			// is_array is equivalent to "is_repeatable"
			if (is_array($posted_data[ CCTM_FormElement::post_name_prefix . $field_name ])) {
				foreach($posted_data[ CCTM_FormElement::post_name_prefix . $field_name ] as &$f) {
					$f = stripslashes(trim($f));
				}
				// This is what preserves the foreign characters while they traverse the json and WP gauntlet
				// (yes, seriously we have to doubleslash it when we create a new post in versions
				// of WP prior to 3.3
				if (isset($posted_data['_cctm_is_create']) && version_compare($wp_version,'3.3','<')) {
					return addslashes(addslashes(json_encode($posted_data[ CCTM_FormElement::post_name_prefix . $field_name ])));
				}
				else {
					return addslashes(json_encode($posted_data[ CCTM_FormElement::post_name_prefix . $field_name ]));
				}				
			}
			// Normal single field
			else{
				return stripslashes(trim($posted_data[ CCTM_FormElement::post_name_prefix . $field_name ]));
			}
		}
		else {
			return '';
		}
	}



	//------------------------------------------------------------------------------
	/**
	 * Validate and sanitize any submitted data. Used when editing the definition for
	 * this type of element. Default behavior here is require only a unique name and
	 * label. Override this if customized validation is required: usually you'll want
	 * to override and still reference the parent:
	 *   public function save_definition_filter($posted_data) {
	 *   	$posted_data = parent::save_definition_filter($posted_data);
	 *   	// your code here...
	 *   	return $posted_data;
	 *  }
	 *
	 *
	 *     into the field values.
	 *
	 * @param array   $posted_data = $_POST data
	 * @return array filtered field_data that can be saved OR can be safely repopulated
	 */
	public function save_definition_filter($posted_data) {

		if ( empty($posted_data['name']) ) {
			$this->errors['name'][] = __('Name is required.', CCTM_TXTDOMAIN);
		}
		else {
			// Are there any invalid characters? 1st char. must be a letter (req'd for valid prop/func names)
			if ( !preg_match('/^[a-z]{1}[a-z_0-9]*$/i', $posted_data['name'])) {
				$this->errors['name'][] = sprintf(
					__('%s contains invalid characters. The name may only contain letters, numbers, and underscores, and it must begin with a letter.', CCTM_TXTDOMAIN)
					, '<strong>'.$posted_data['name'].'</strong>');
				$posted_data['name'] = preg_replace('/[^a-z_0-9]/', '', $posted_data['name']);
			}
			// Is the name too long?
			if ( strlen($posted_data['name']) > 255 ) {
				$posted_data['name'] = substr($posted_data['name'], 0 , 255);
				$this->errors['name'][] = __('The name is too long. Names must not exceed 255 characters.', CCTM_TXTDOMAIN);
			}
			// Run into any reserved words?
			if ( in_array($posted_data['name'], CCTM::$reserved_field_names ) ) {
				$this->errors['name'][] = sprintf(
					__('%s is a reserved name.', CCTM_TXTDOMAIN)
					, '<strong>'.$posted_data['name'].'</strong>');
				$posted_data['name'] = '';
			}

			// it's a CREATE operation
			if ( empty($this->original_name) ) {

				if ( isset(CCTM::$data['custom_field_defs']) && is_array(CCTM::$data['custom_field_defs'])
					&& in_array( $posted_data['name'], array_keys(CCTM::$data['custom_field_defs']))) {
					$this->errors['name'][] = sprintf( __('The name %s is already in use. Please choose another name.', CCTM_TXTDOMAIN), '<em>'.$posted_data['name'].'</em>');
					$posted_data['name'] = '';
				}
			}
			// it's an EDIT operation and we're renaming the field
			elseif ( $this->original_name != $posted_data['name'] ) {
				if ( isset(CCTM::$data['custom_field_defs']) && is_array(CCTM::$data['custom_field_defs'])
					&& in_array( $posted_data['name'], array_keys(CCTM::$data['custom_field_defs']) ) ) {
					$this->errors['name'][] = sprintf( __('The name %s is already in use. Please choose another name.', CCTM_TXTDOMAIN), '<em>'.$posted_data['name'].'</em>');
					$posted_data['name'] = '';
				}
			}
		}


		// You may need to do this for any textarea fields. Saving a '</textarea>' tag
		// in your description field can wreak everything.
		if ( !empty($posted_data['description']) ) {
			$posted_data['description'] = strip_tags($posted_data['description'], CCTM::$allowed_html_tags);
		}

		$posted_data = CCTM::striptags_deep($posted_data);
		// WP always quotes data (!!!), so we don't bother checking get_magic_quotes_gpc et al.
		// See this: http://kovshenin.com/archives/wordpress-and-magic-quotes/
		$posted_data = CCTM::stripslashes_deep($posted_data);

		// Apply immutable properties, and return filtered data
		return array_merge($posted_data, $this->immutable);
	}

	//------------------------------------------------------------------------------
	/**
	 * Shepherded access to the $this->props array.
	 */
	public function set_props($array) {
		if (!is_array($array)) {
			$this->errors['improper_input_set_props'] = __('Improper input to the set_props() function.', CCTM_TXTDOMAIN);
		}
		
		foreach ($array as $k => $v) {
			$this->$k = $v;
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	 * If your custom field has done any customizations (e.g. of the database)
	 * then you should implement this function to do cleanup: this is run when the
	 * the field is uninstalled or the CCTM plugin is uninstalled.
	 */
	public function uninstall() { }
}


/*EOF CCTM_FormElement.php */