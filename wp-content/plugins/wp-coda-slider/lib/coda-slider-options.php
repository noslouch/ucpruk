<?php
/**
 * Admin Options Page
 *
 * @package WP Coda Slider
 * @subpackage options.php
 *
 */

$coda_slider = new WP_Coda_Slider_Options();

class WP_Coda_Slider_Options {

	 public $coda_option;

	/**
	 * Option name
	 * @var array $options
	 * @since 3.6
	 */
	var $option = '_slider_settings';


	/**
	 * Options page title
	 * @var string
	 */
	var $title = 'Coda Slider Options';

	/**
	 * Settings section
	 * @var string
	 * @since 3.6
	 */
	var $group = 'coda_settings_group';

	var $coda_hook;

	var $section = 'coda_settings_section';

	/**
	 * Options page slug
	 * @var string
	 * @since 3.6
	 */
	var $page_slug = 'coda_slider';

	var $count = 1;

	var $description = 'WP Coda Slider options';

	/**
	 * Capability validation
	 * @var string $cap
	 * @since 3.6
	 */
	var $cap = 'manage_options';

	public function _options() {
		$options = array();
		$options[] = array(
			'name' => 'Coda Slider Options',
			'type' => 'heading',
		);
		$options[] = array (
			'name'    => 'Add Slider Meta Boxes',
			'id'      => 'slider_meta',
			'type'    => 'select',
			'desc'    => 'Choose post types to add meta boxes to',
			'options' => $this->p_types()
		);
		$options[] = array(
			'name'        => 'Add Slider Meta Boxes to individual posts or pages',
			'id'          => 'custom_slider_meta',
			'type'        => 'text',
			'desc' => 'To add the meta boxes to individual posts or pages enter the id or for multiple enter comma separated ids',
			);
		return $options;
	}

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_js' ) );

	}

	function get_show( $args ) {
		/**
		 * @var string $page page option( post, page, none, specific id, specific template, all)
		 * @var mixed $ids comma spaced post ids, or page template filename
		 * @var $built_in array of built in post types
		 */
		$built_in = array ( 'post', 'page' );
		extract( $args, EXTR_SKIP );
		$args['show_on'] = array( 'key' => false, 'value' => false );
		if ( in_array( $page, array( 'all', 'id-only', 'template' ) ) )
			$args['page'] =  array_merge( $built_in, array_values( $this->get_p_types() ));
		elseif ( 'none' == $page )
			$args['page'] = false;
		else
			$args['page'] = (array)$page;

		if ( 'template' == $page )
			$args['show_on'] = array ( 'key' => 'page-template', 'value' => array( $ids ) );

		elseif ( 'id-only' == $page ) {
			$args['id'] = array();
			foreach( $ids as $id => $val ) {
				$id = is_array( $val ) ? $val : explode( ",", $val );
				if( count( $id ) > 1 ) {
					foreach( $id as $v ) {
						$args['id'][] = (int)$v;
					}
				} else {
					$args['id'][] = (int)$val;
				}

			}
			$args['show_on'] = array( 'key' => 'id', 'value' => $args['id'] );
		} unset( $args['id'] );
		return $args;
	}

	function p_types() {
		$custom = array( 'all' => 'All', 'post' => 'Posts', 'page' => 'Pages', 'none' => 'None', 'id-only' => 'Specific IDs', 'template' => 'Specific Page Template'  );
		$custom = array_merge( $custom, $this->get_p_types() );
		return $custom;
	}

	function get_p_types() {
		$custom = array();
		foreach ( get_post_types( array ( '_builtin' => false, 'can_export' => true, 'show_ui' => true ), 'objects' ) as $post_type ) {
			if ( $post_type )
				$custom[$post_type->label] = $post_type->name;
		}
		return $custom;
	}

	function admin_menu() {
		$this->coda_hook = add_options_page( $this->title, $this->title, $this->cap, $this->page_slug, array( $this, 'coda_options_page' ) );
	}


	function coda_sanitize( $option ) {
		return $option;
	}

	function register() {
		register_setting( $this->page_slug, $this->option );
	}
	function admin_js( $hook ) {
		if ( $hook != $this->coda_hook )
			return;
		wp_enqueue_script( 'coda-admin', plugins_url( 'js/coda.admin.js', __FILE__ ), array( 'jquery') );
	}

	function coda_options_page() { ?>
		<div class="wrap">
			<form method="post" action="options.php">
				<div class="coda-options-wrapper">
				<h2>WP Coda Slider Settings</h2>
				<?php wp_nonce_field( $this->page_slug ); ?>
				<?php settings_fields( $this->page_slug ); ?>
				<?php $this->option_fields(); /* Settings */ ?>
				<?php submit_button( 'Save Options' ); ?>
				<div class="clear"></div>
				</div>
			</form>
		</div>

	<?php }

	function option_fields() {
		$option_name = $this->option;
		$settings = get_option( $option_name );
		$options = $this->_options();
		$counter = 0;

		foreach ( $options as $value ) {

			$counter ++;
			$val = '';
			$output = '';

			if ( ( $value['type'] != "heading" ) && ( $value['type'] != "info" ) ) {

				$value['id'] = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($value['id']) );

				$id = 'section-' . $value['id'];

				$class = 'section ';
				if ( isset( $value['type'] ) )
					$class .= ' section-' . $value['type'];

				if ( isset( $value['class'] ) )
					$class .= ' ' . $value['class'];

				$output .= '<div id="' . esc_attr( $id ) .'" class="' . esc_attr( $class ) . '">'."\n";
				if ( isset( $value['name'] ) )
					$output .= '<h4 class="heading">' . esc_html( $value['name'] ) . '</h4>' . "\n";

				if ( $value['type'] != 'editor' )
					$output .= '<div class="option">' . "\n" . '<div class="controls">' . "\n";
				else
					$output .= '<div class="option">' . "\n" . '<div>' . "\n";
			}

			if ( isset( $value['std'] ) )
				$val = $value['std'];

			if ( ( $value['type'] != 'heading' ) && ( $value['type'] != 'info' ) ) {
				if ( isset( $settings[( $value['id'] )] ) ) {
					$val = $settings[( $value['id'] )];

					if ( ! is_array( $val ) ) {
						$val = stripslashes( $val );
					}
				}
			}

			$explain_value = '';
			if ( isset( $value['desc'] ) )
				$explain_value = $value['desc'];

			switch ( $value['type'] ) {
				case 'text':
					$output .= '<input id="'.esc_attr( $value['id'] ).'" class="of-input" name="'.esc_attr( $option_name.'[' .$value['id'].']' ).'" type="text" value="' . esc_attr( $val ) . '" />';
					break;

				case ( $value['type'] == 'select' ):
					$output .= '<select class="of-input" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" id="' . esc_attr( $value['id'] ) . '">';

					foreach ( $value['options'] as $key => $option ) {
						$selected = '';
						if ( $val != '' ) {
							if ( $val == $key ) {
								$selected = ' selected="selected"';
							}
						}
						$output .= '<option' . $selected . ' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
					}
					$output .= '</select>';
					break;
		}
		if ( ( $value['type'] != "heading" ) && ( $value['type'] != "info" ) ) {
			$output .= '</div>';
			if ( ( $value['type'] != "checkbox" ) && ( $value['type'] != "editor" ) )
				$output .= '<div class="explain">' . esc_html( $explain_value ) . '</div>'."\n";

			$output .= '</div></div>'."\n";
		}
			echo $output;
		}
		echo '</div>';
	}

}