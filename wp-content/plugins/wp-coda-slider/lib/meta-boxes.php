<?php
/**
 * Defines the meta box fields if option is set to show metaboxes
 * 
 * @package WP Coda Slider
 * @subpackage meta-boxes.php
 * 
 */
/**
 * Initializes the meta box class
 * @see
 */

/**
 * Defines meta box fields
 * @return array Meta box fields
 * @uses cmb_Meta_box
 */


function coda_slider_meta_boxes(array $meta_boxes ) {
	if (! is_admin() )
		return false;
	global $coda_slider;
	$ops = get_option( $coda_slider->option );
	$page = isset( $ops['slider_meta'] ) ? $ops['slider_meta'] : false;
	$ids = isset( $ops['custom_slider_meta'] ) ? array( $ops['custom_slider_meta'] ): false;

	$args  = $coda_slider->get_show( $args = array( 'page' => $page, 'ids' => $ids ) );
	if ( false == $args['page'] )
		return $meta_boxes;

	$prefix = '_c3m_';
	$meta_boxes[] = array(
		'id' => 'slider_meta_boxes',
		'title' => 'Create a coda slider for this post',
		'pages' => $args['page'], // Post type
		'show_on' => $args['show_on'],
		'context' => 'normal',
		'priority' => 'high',
		'show_names' => true, // Show field names on the left
		'fields' => array (
			array (
				'name'  => 'Slider ID',
				'desc'  => 'Give the slider a unique title ( used as div id)',
				'id'    => $prefix . 'title',
				'type'  => 'text_small',
			),
		array(
			'name'  => 'Display slider:',
			'id'    => $prefix . 'display',
			'type'  => 'radio_inline',
			'options' => array(
				array( 'name' => 'Display Slider', 'value' => 'before', ),
				array( 'name' => 'Don\'t Display on this page', 'value' => 'never', ),
				),
		),
		array(
			'name'  => 'Show Post:',
			'id'    => $prefix . 'content',
			'type'  => 'radio_inline',
			'options' => array (
				array( 'name' => 'Content', 'value' => 'content', ),
				array( 'name' => 'Excerpt', 'value' => 'excerpt', ),
					),
		),
		array(
			'name'  => 'Show Title:',
			'id'    => $prefix . 'show_title',
			'type'  => 'radio_inline',
			'options' => array(
				array( 'name' => 'Yes', 'value' => 'yes', ),
				array( 'name' => 'No', 'value' => 'no', ),
				),
		),
		array(
			'name'  => 'Category to get posts from',
			'id'    => $prefix . 'cat',
			'type'  => 'taxonomy_select',
			'taxonomy' => 'category',  // Taxonomy Slug
		),
		array(
			'name'  => 'Number of posts to query',
			'desc'  => 'enter -1 for all posts in the category',
			'id'    => $prefix . 'show',
			'type'  => 'text_small',
		),
		array(
			'name'  => 'CSS Options',
			'id'    => $prefix . 'test_title',
			'type'  => 'title',
		),
		array(
			'name'  => 'Output inline custom css',
			'id'    => $prefix . 'css_custom',
			'type'  => 'select',
			'options' => array(
				array( 'name' => 'No', 'value' => (bool)false ),
				array( 'name' => 'Yes', 'value' => (bool)true  ),
			),
		),
		array(
			'name'  => 'CSS Width',
			'desc'  => 'How wide in px, em, or %',
			'id'    => $prefix . 'width',
			'type'  => 'text_small',
		),
		array(
			'name'  => 'Tab background color',
			'id'    => $prefix . 'tab_bg',
			'type'  => 'colorpicker',
			'std'   => '#000000'
		),
		array(
			'name'  => 'Tab Active background color',
			'id'    => $prefix . 'tab_active_bg',
			'type'  => 'colorpicker',
			'std'   => '#000000'
		),
		array(
			'name'  => 'Tab text color',
			'id'    => $prefix . 'tab_color',
			'type'  => 'colorpicker',
			'std'   => '#ffffff'
		),
		array(
			'name'  => 'Tab Active text color',
			'id'    => $prefix . 'tab_active_color',
			'type'  => 'colorpicker',
			'std'   => '#ffffff'
		),
		array(
			'name'  => 'Tab title font size',
			'desc'  => 'Enter value in px, em or %',
			'id'    => $prefix . 'tab_font',
			'type'  => 'text_small',
		),
		array(
			'name'  => 'Custom CSS',
			'desc'  => 'Include any custom css',
			'id'    => $prefix . 'slider_css',
			'type'  => 'textarea_small',
		),
		array(
			'name'  => 'Coda slider Options',
			'id'    => $prefix . 'slider_args',
			'type'  => 'title',
		),
		array(
			'name'  => 'autoHeight',
			'id'    => $prefix . 'autoheight',
			'type'  => 'radio_inline',
			'options' => array(
				array( 'name' => 'True', 'value' => 'true' ),
				array( 'name' => 'False', 'value' => 'false', ),
					),
		),
		array(
			'name'  => 'autoSlide',
			'id'    => $prefix . 'autoslide',
			'type'  => 'radio_inline',
			'options' => array(
				array( 'name' => 'True', 'value' => 'true', ),
				array( 'name' => 'False', 'value' => 'false', ),
			),
		),
		array(
			'name'  => 'autoSlideInterval',
			'id'    => $prefix . 'slide_interval',
			'type'  => 'text_small',
		),
		array(
			'name'  => 'autoSlideStopWhenClicked',
			'id'    => $prefix . 'stop_click',
			'type'  => 'radio_inline',
			'options' => array(
				array( 'name' => 'True', 'value' => 'true', ),
				array( 'name' => 'False', 'value' =>  'false', ),
			),
		),
		array(
			'name'  => 'dynamicTabs',
			'id'    => $prefix . 'dyntabs',
			'type'  => 'radio_inline',
			'options' => array(
				array( 'name' => 'True', 'value' => 'true', ),
				array( 'name' => 'False', 'value' => 'false', ),
					),
		),
		array(
			'name'  => 'dynamicTabsAlign',
			'id'    => $prefix . 'tab_align',
			'type'  => 'radio_inline',
			'options' => array(
				array( 'name' => 'Center', 'value' => 'center', ),
				array( 'name' => 'Left', 'value' => 'left', ),
				array( 'name' => 'Right', 'value' => 'right', ),
			),
		),
		array(
			'name'  => 'dynamicArrows',
			'id'    => $prefix . 'dynamicarrows',
			'type'  => 'radio_inline',
			'options' => array(
				array( 'name' => 'True', 'value' => 'true', ),
				array( 'name' => 'False', 'value' => 'false', ),
				array( 'name' => 'Images', 'value' => 'image' ),
			),
		),
		array(
			'name'  => 'Dynamic Arrows Left Text',
			'id'    => $prefix . 'left_text',
			'type'  => 'text_small',
		),
		array(
			'name'  => 'Dynamic Arrows Right Text',
			'id'    => $prefix . 'right_text',
			'type'  => 'text_small',
		),
		array(
			'name'  => 'EaseDuration',
			'id'    => $prefix . 'easeduration',
			'type'  => 'text_small',
		),
		array(
			'name'  => 'SlideEaseFunction',
			'id'    => $prefix . 'slidefunc',
			'type'  => 'select',
			'options' => array (
				array( 'name' => 'jswing',             'value' => 'jswing', ),
				array( 'name' => 'easeInQuad',         'value' => 'easeInQuad', ),
				array( 'name' => 'easeOutQuad',        'value' => 'easeOutQuad', ),
				array( 'name' => 'easeInOutQuad',      'value' => 'easeInOutQuad', ),
				array( 'name' => 'easeInCubic',        'value' => 'easeInCubic', ),
				array( 'name' => 'easeOutCubic',       'value' => 'easeOutCubic', ),
				array( 'name' => 'easeInOutCubic',     'value' => 'easeInOutCubic', ),
				array( 'name' => 'easeInQuart',        'value' => 'easeInQuart', ),
				array( 'name' => 'easeOutQuart',       'value' => 'easeOutQuart', ),
				array( 'name' => 'easeInOutQuart',     'value' => 'easeInOutQuart', ),
				array( 'name' => 'easeInQuint',        'value' => 'easeInQuint', ),
				array( 'name' => 'easeOutQuint',       'value' => 'easeOutQuint', ),
				array( 'name' => 'easeInOutQuint',     'value' => 'easeInOutQuint', ),
				array( 'name' => 'easeInSine',         'value' => 'easeInSine', ),
				array( 'name' => 'easeOutSine',        'value' => 'easeOutSine', ),
				array( 'name' => 'easeInOutSine',      'value' => 'easeInOutSine', ),
				array( 'name' => 'easeInExpo',         'value' => 'easeInExpo', ),
				array( 'name' => 'easeOutExpo',        'value' => 'easeOutExpo', ),
				array( 'name' => 'easeInOutExpo',      'value' => 'easeInOutExpo', ),
				array( 'name' => 'easeInCirc',         'value' => 'easeInCirc', ),
				array( 'name' => 'easeOutCirc',        'value' => 'easeOutCirc', ),
				array( 'name' => 'easeInOutCirc',      'value' => 'easeInOutCirc', ),
				array( 'name' => 'easeInElastic',      'value' => 'easeInElastic', ),
				array( 'name' => 'easeOutElastic',     'value' => 'easeOutElastic', ),
				array( 'name' => 'easeInOutElastic',   'value' => 'easeInOutElastic', ),
				array( 'name' => 'easeInBack',         'value' => 'easeInBack', ),
				array( 'name' => 'easeOutBack',        'value' => 'easeOutBack', ),
				array( 'name' => 'easeInOutBack',      'value' => 'easeInOutBack', ),
				array( 'name' => 'easeInBounce',       'value' => 'easeInBounce', ),
				array( 'name' => 'easeOutBounce',      'value' => 'easeOutBounce', ),
				array( 'name' => 'easeInOutBounce',    'value' => 'easeInOutBounce', ),
			),
		),
	) );

	return $meta_boxes;
}

	add_action( 'init', 'cmb_initialize_cmb_meta_boxes', 9999 );
	/**
	 * Initialize the metabox class.
	 */
	function cmb_initialize_cmb_meta_boxes() {
		if ( ! class_exists( 'cmb_Meta_Box' ) )
			require_once 'init.php';

	}
