<?php
/**
 * CCTM_relation
 *
 * Implements a special AJAX form element used to store a wp_posts.ID representing
 * another post of some kind
 *
 * @package CCTM_FormElement
 */


class CCTM_relation extends CCTM_FormElement
{
	public $props = array(
		'label' => '',
		'button_label' => '',
		'name' => '',
		'description' => '',
		'class' => '',
		'extra' => '',
		'is_repeatable' => '',
		'default_value' => '',
		'search_parameters' => '',
		'output_filter' => 'to_link_href',
		// 'type' => '', // auto-populated: the name of the class, minus the CCTM_ prefix.
	);

	//------------------------------------------------------------------------------
	/**
	 * Thickbox support
	 */
	public function admin_init() {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_register_script('cctm_relation', CCTM_URL.'/js/relation.js', array('jquery', 'media-upload', 'thickbox'));
		wp_enqueue_script('cctm_relation');
	}


	//------------------------------------------------------------------------------
	/**
	 * This function provides a name for this type of field. This should return plain
	 * text (no HTML). The returned value should be localized using the __() function.
	 *
	 * @return string
	 */
	public function get_name() {
		return __('Relation', CCTM_TXTDOMAIN);
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
		return __('Relation fields are used to store a reference to another post, including media posts. For example you can use a relation to link to a parent post or to an image or attachment.', CCTM_TXTDOMAIN);
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
		return 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Relation';
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param mixed   $current_value current value for this field (an integer ID).
	 * @return string
	 */
	public function get_edit_field_instance($current_value) {

		require_once CCTM_PATH.'/includes/SummarizePosts.php';
		require_once CCTM_PATH.'/includes/GetPostsQuery.php';

		$Q = new GetPostsQuery();
		
		// Populate the values (i.e. properties) of this field
		$this->id   = $this->name;
		$this->content  = '';

		if (empty($this->button_label)) {
			$this->button_label = __('Choose Relation', CCTM_TXTDOMAIN);
		}

		$this->post_id = $this->value;

		$fieldtpl = '';
		$wrappertpl = '';
		// Multi field?
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

			// Test for an empty JSON array
			if ($current_value != '[""]') {
				
				$values = (array) json_decode($current_value,true);
				
				foreach ($values as $v) {
					$hash 					= $this->get_props();
					$hash['post_id']    	= (int) $v;
					$hash['thumbnail_url']	= CCTM::get_thumbnail($hash['post_id']);

					// Look up all the data on that foriegn key
					// We gotta watch out: what if the related post has custom fields like "description" or 
					// anything that would conflict with the definition?
					$post = (array) $Q->get_post($hash['post_id']);
					foreach($post as $k => $v) {
						// Don't override the def's attributes!
						if (!isset($hash[$k])) {
							$hash[$k] = $v;
						}
					}
					
					$this->content .= CCTM::parse($fieldtpl, $hash);
				}
			}
		}
		// Regular old Single-selection
		else {

			$this->post_id    = (int) $current_value; // Relations only store the foreign key.			
			$this->thumbnail_url = CCTM::get_thumbnail($this->post_id);

			$fieldtpl = CCTM::load_tpl(
				array('fields/elements/'.$this->name.'.tpl'
					, 'fields/elements/_'.$this->type.'.tpl'
				)
			);

			$wrappertpl = CCTM::load_tpl(
				array('fields/wrappers/'.$this->name.'.tpl'
					, 'fields/wrappers/_'.$this->type.'.tpl'
				)
			);

			if ($this->post_id) {
				// Look up all the data on that foriegn key
				// We gotta watch out: what if the related post has custom fields like "description" or 
				// anything that would conflict with the definition?
				$post = (array) $Q->get_post($this->post_id);
				foreach($post as $k => $v) {
					// Don't override the def's attributes!
					if (!isset($this->$k)) {
						$this->$k = $v;
					}
				}
				$this->content = CCTM::parse($fieldtpl, $this->get_props());
			}
		}

		if (empty($this->button_label)) {
			$this->button_label = __('Choose Relation', CCTM_TXTDOMAIN);
		}

		return CCTM::parse($wrappertpl, $this->get_props());
	}


	//------------------------------------------------------------------------------
	/**
	 * This should return (not print) form elements that handle all the controls required to define this
	 * type of field.  The default properties correspond to this class's public variables,
	 * e.g. name, label, etc. The form elements you create should have names that correspond
	 * with the public $props variable. A populated array of $props will be stored alongside
	 * the custom-field data for the containing post-type.
	 *
	 * @param array $def
	 * @return string HTML input fields
	 */
	public function get_edit_field_definition($def) {

		// Used to fetch the default value.
		require_once CCTM_PATH.'/includes/SummarizePosts.php';
		require_once CCTM_PATH.'/includes/GetPostsQuery.php';
		$Q = new GetPostsQuery();

		$is_checked = '';
		if (isset($def['is_repeatable']) && $def['is_repeatable'] == 1) {
			$is_checked = 'checked="checked"';
		}
		
		// Note fieldtype: used to set the default value on new fields
		$out = '<input type="hidden" id="fieldtype" value="relation" />';
		
		// Label
		$out .= '<div class="'.self::wrapper_css_class .'" id="label_wrapper">
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

		// Initialize / defaults
		$preview_html = '';
		$click_label = __('Choose Relation');
		$label = __('Default Value', CCTM_TXTDOMAIN);
		$remove_label = __('Remove');


		// Handle the display of the default value
		if ( !empty($def['default_value']) ) {

			$hash = CCTM::get_thumbnail($def['default_value']);

			$fieldtpl = CCTM::load_tpl(
				array('fields/elements/'.$this->name.'.tpl'
					, 'fields/elements/_'.$this->type.'.tpl'
					, 'fields/elements/_relation.tpl'
				)
			);
			$preview_html = CCTM::parse($fieldtpl, $hash);
		}

		// Button Label
		$out .= '<div class="'.self::wrapper_css_class .'" id="button_label_wrapper">
			 		<label for="button_label" class="'.self::label_css_class.'">'
			.__('Button Label', CCTM_TXTDOMAIN).'</label>
			 		<input type="text" name="button_label" class="'.self::css_class_prefix.'text" id="button_label" value="'.htmlspecialchars($def['button_label']) .'"/>
			 		' . $this->get_translation('button_label').'
			 	</div>';

		// Set Search Parameters
		$out .= '
			<div class="cctm_element_wrapper" id="search_parameters_wrapper">
				<label for="name" class="cctm_label cctm_text_label" id="search_parameters_label">'
			. __('Search Parameters', CCTM_TXTDOMAIN) .
			'</label>
				<span class="cctm_description">'.__('Define which posts are available for selection by narrowing your search parameters.', CCTM_TXTDOMAIN).'</span>
				<br/>
				<span class="button" onclick="javascript:search_form_display(\''.$def['name'].'\',\''.$def['type'].'\');">'.__('Set Search Parameters', CCTM_TXTDOMAIN) .'</span>
				<div id="cctm_thickbox"></div>
				<input type="hidden" id="search_parameters" name="search_parameters" value="'.CCTM::get_value($def, 'search_parameters').'" />
				<br/>
			</div>';

		// Default Value
		$out .= '
			<div class="cctm_element_wrapper" id="default_value_wrapper">
				<input type="hidden" id="fieldname" value="'.$def['name'].'" />
				<label for="default_value" class="'.self::label_css_class.'">'
			.__('Default Value', CCTM_TXTDOMAIN).'</label>
				<span class="cctm_description">'.__('Choose a default value(s) to display on new posts using this field.', CCTM_TXTDOMAIN).'</span>
					<span class="button" onclick="javascript:thickbox_results(\'cctm_'.$def['name'].'\');">'.$label.'</span>
				</span>
				<div id="target_cctm_'.$def['name'].'"></div>
				<input type="hidden" id="default_value" name="default_value" value="'
			.htmlspecialchars($def['default_value']).'" /><br />
				<div id="cctm_instance_wrapper_'.$def['name'].'">'.$preview_html.'</div>
				<br />
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