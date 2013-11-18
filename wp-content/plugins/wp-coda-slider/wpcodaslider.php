<?php
/*
Plugin Name: WP Coda Slider
Plugin URI: http://c3mdigital.com/wp-coda-slider/
Description: Add a jQuery Coda slider to any WordPress post or page
Author: c3mdigital
Author URI: http://c3mdigital.com/
Version: 0.3.6.2
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

add_action( 'wp_enqueue_scripts', 'c3m_coda_scripts' );
add_filter( 'the_content', 'c3m_slider_show', 10 );
add_filter( 'cmb_meta_boxes', 'coda_slider_meta_boxes' );
require_once dirname( __FILE__ ) . '/lib/coda-slider-options.php';
require_once dirname( __FILE__ ) . '/lib/meta-boxes.php';


/**
 * @param string $meta post meta key
 * @param bool $return
 * @param string $post_id
 * @return mixed $meta value if return defined true
 */
function c3m_meta( $meta, $return = false, $post_id = '' ) {
	if ( ! $post_id )
		$post_id = get_the_ID();

	$val = get_post_meta( $post_id, $meta, true );
	$meta = empty( $val ) ? NULL : $val;

	if ( false !== $return )
		echo $meta;

	return $meta;
	}

/**
 * @param string $content The post content
 * @return string $content The filtered content
 */
function c3m_slider_show( $content ) {
	if ( c3m_meta( '_c3m_display' ) == '' || c3m_meta( '_c3m_display' ) == 'never' || ! is_singular () )
		return $content;
	if ( c3m_find_short_code( 'wpcodaslider' ) )
		return $content;

	$cat            = c3m_meta( '_c3m_cat' );
	$show           = c3m_meta( '_c3m_show' );
	$autoheight     = c3m_meta( '_c3m_autoheight' );
	$easeduration   = c3m_meta( '_c3m_easeduration' );
	$easefunc       = c3m_meta( '_c3m_slidefunc' );
	$stop_click     = c3m_meta( '_c3m_stop_click' );
	$tab_align      = c3m_meta( '_c3m_tab_align' );
	$right_text     = c3m_meta( '_c3m_right_text' );
	$left_text      = c3m_meta( '_c3m_left_text' );
	$tabs           = c3m_meta( '_c3m_dyntabs' );
	$arrows         = c3m_meta( '_c3m_dynamicarrows' );
	$arrow_img = 'image' == $arrows ? 'true' : 'false';

		if ( 'image' == $arrows )
			$arrows = 'true';

	$slide_int      = c3m_meta( '_c3m_slide_interval' );
	$auto_slide     = c3m_meta( '_c3m_autoslide' );
	$id             = c3m_meta( '_c3m_title' );
	$tab_color      = c3m_meta( '_c3m_tab_color' );
	$tab_bg         = c3m_meta( '_c3m_tab_bg' );
	$tab_active_bg  = c3m_meta( '_c3m_tab_active_bg' );
	$tab_active_col = c3m_meta( '_c3m_tab_active_color' );
	$width          = c3m_meta( '_c3m_width' );
	$tab_font       = c3m_meta( '_c3m_tab_font' );
	$slider_css     = c3m_meta( '_c3m_slider_css' );
	$excerpt        = c3m_meta( '_c3m_content' );
	$title          = c3m_meta( '_c3m_show_title' );

	$args = array(
		'autoHeight' => $autoheight,
		'autoSlide' => $auto_slide,
		'autoSlideInterval' => $slide_int,
		'autoSlideStopWhenClicked' => $stop_click,
		'dynamicArrows' => $arrows,
		'dynamicArrowsGraphical' => $arrow_img,
		'dynamicArrowLeftText' => $left_text,
		'dynamicArrowRightText' => $right_text,
		'dynamicTabs' => $tabs,
		'dynamicTabsAlign' => $tab_align,
		'slideEaseDuration' => $easeduration,
		'slideEaseFunction' => $easefunc,
	);

	$defaults = array (
		'autoHeight' => 'true',
		'autoSlide' => 'false',
		'autoSlideInterval' => '7000',
		'autoSlideStopWhenClicked' => 'true',
		'dynamicArrows' => 'false',
		'dynamicArrowLeftText' => '&#171; left',
		'dynamicArrowRightText' => 'right &#187;',
		'dynamicTabs' => 'true',
		'dynamicTabsAlign' => 'center',
		'slideEaseDuration' => '1000',
		'slideEaseFunction' => 'easeInOutExpo'
	);

	$autoHeight         = empty( $args['autoHeight'] ) ? $defaults['autoHeight'] : $args['autoHeight'];
	$autoSlide          = empty( $args['autoSlide'] ) ? $defaults['autoSlide'] : $args['autoSlide'];
	$autoSlideInterval  = empty( $args['autoSlideInterval'] ) ? $defaults['autoSlideInterval'] : $args['autoSlideInterval'];
	$autoSlideStopWhenClicked = empty( $args['autoSlideStopWhenClicked'] ) ? $defaults['autoSlideStopWhenClicked'] : $args['autoSlideStopWhenClicked'];
	$dynamicArrows      = empty( $args['dynamicArrows'] ) ? $defaults['dynamicArrows'] : $args['dynamicArrows'];
	$dynamicArrowLeftText = empty( $args['dynamicArrowLeftText'] ) ? $defaults['dynamicArrowLeftText'] : $args['dynamicArrowLeftText'];
	$dynamicArrowRightText = empty( $args['dynamicArrowRightText'] ) ? $defaults['dynamicArrowRightText'] : $args['dynamicArrowRightText'];
	$dynamicTabs        = empty( $args['dynamicTabs'] ) ? $defaults['dynamicTabs'] : $args['dynamicTabs'];
	$dynamicTabsAlign   = empty( $args['dynamicTabsAlign'] ) ? $defaults['dynamicTabsAlign'] : $args['dynamicTabsAlign'];
	$slideEaseDuration  = empty( $args['slideEaseDuration'] ) ? $defaults['slideEaseDuration'] : $args['slideEaseDuration'];
	$slideEaseFunction  = empty( $args['slideEaseFunction'] ) ? $defaults['slideEaseFunction'] : $args['slideEaseFunction'];

	$content .= '<div class="coda-slider-wrapper">';
	$content .= '<div class="coda-slider preload" id="' . $id . '">';

	$args = array (
		'post_type' => 'post',
		'posts_per_page' => $show,
		'cat' => (int)$cat,
		'no_found_rows' => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'post_not_in' => array ( get_the_ID () )
	);

	$loop = new WP_Query( $args );
		while ( $loop->have_posts () ) : $loop->the_post ();
			if ( get_post_meta( get_the_ID(), '_c3m_display', true ) == 'before' )
				remove_filter( 'the_content', 'c3m_slider_show' );
			$content .= '<div id="post-' . get_the_ID () . '" class="panel">';
			$content .= '<div class="panel-wrapper">';
		if ( $title == 'yes' ) {
			$content .= '<h2 class="title">' . get_the_title () . '</h2>';
		} else {
			$content .= '<h2 class="title" style="display:none">' . get_the_title () . '</h2>';
		}
		if ( $excerpt == 'excerpt' ) {
			$content .=  get_the_excerpt();
		} else {
			$pc = get_the_content();
			$pc = apply_filters( 'the_content', $pc );
			$pc = str_replace( ']]>', ']]&gt;', $pc );
			$content .=  $pc;
		}
			$content .= '</div><!-- /panel-wrapper --> </div><!-- /panel -->';
		endwhile;
		wp_reset_postdata ();

		$content .= '</div><!-- /.coda-slider .preload -->';
		$content .= '</div><!-- /coda-slider-wrapper -->';
		$content .= '<script type="text/javascript" >
				jQuery(document).ready(function($) {
					$( "#' . $id . '" ) .codaSlider ({
						autoHeight:' . $autoHeight . ',
						autoSlide:' . $autoSlide . ',
						autoSlideInterval:' . $autoSlideInterval . ',
						autoSlideStopWhenClicked:' . $autoSlideStopWhenClicked . ',
						dynamicArrows: '. $dynamicArrows . ',
						dynamicArrowsGraphical:' . $arrow_img . ',
						dynamicArrowLeftText:"' . $dynamicArrowLeftText . '",
						dynamicArrowRightText:"' . $dynamicArrowRightText . '",
						dynamicTabs:' . $dynamicTabs . ',
						dynamicTabsAlign:"' . $dynamicTabsAlign . '",
						slideEaseDuration:' . $slideEaseDuration . ',
						slideEaseFunction:"' . $slideEaseFunction . '"
					});
				});

			</script>';
		if ( false !== c3m_meta( '_c3m_css_custom') ) {
		$content .= '<style type="text/css">
					.coda-slider-wrapper .coda-slider,
					.coda-slider-wrapper .coda-slider .panel{width: ' . $width . ';}
					.coda-nav-left a, .coda-nav-right a { background: ' . $tab_bg . '; color: ' . $tab_color . '; padding: 3px; }
					.coda-slider-wrapper .coda-nav .current,
					 .coda-slider-wrapper .coda-nav a:hover { background: ' . $tab_active_bg . '; color: ' . $tab_active_col . '; }
					.coda-slider-wrapper .coda-nav a  { background: ' . $tab_bg . '; color: ' . $tab_color . '; display: block; float: left; margin-right: 1px; padding: 3px 6px; font-size: ' . $tab_font . '; text-decoration: none }
					' . $slider_css . '
					</style>';
		}

	return $content;
}
/**
 * c3m_slider() Template tag to display a coda slider
 * @param array $args
 */
function c3m_slider( $args ) {

	if ( 'function' != get_post_meta( get_the_ID(), '_c3m_template_tag', true ) )
		add_metadata( 'post', get_the_ID(), '_c3m_template_tag', 'function', true );
	$defaults = array(
		'id'    => 'coda-slider',
		'cat'   => null,
		'show'  => null,
		'slider_args'   => array(
			'autoHeight'               => 'true',
			'autoSlide'                => 'false',
			'autoSliderDirection'      => 'right',
			'autoSlideInterval'        => '7000',
			'autoSlideStopWhenClicked' => 'true',
			'dynamicArrows'            => 'true',
			'dynamicArrowsGraphical'   => 'false',
			'dynamicArrowLeftText'     => '&#171; left',
			'dynamicArrowRightText'    => 'right &#187;',
			'dynamicTabs'              => 'true',
			'dynamicTabsAlign'         => 'center',
			'dynamicTabsPosition'      => 'top',
			'slideEaseDuration'        => '1000',
			'slideEaseFunction'        => 'easeInOutExpo'
		),
	);
	/**
	 * @var int $cat The category to query posts from
	 * @var int $show The number of posts to query
	 * @var string $id The unique div id of the slider
	 * @var array $slider_args The Coda Slider js arguments
	 */
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

		$query_args = array (
			'post_type'              => 'post',
			'cat'                    => $cat,
			'posts_per_page'         => $show,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'post_not_in'            => array ( get_the_ID() ),
		);

		$coda_query = new WP_Query( $query_args ); ?>
	<div class="coda-slider-wrapper"> <!-- yes -->
        <div class="coda-slider preload <?php echo 'id="' . $id; ?>">

		<?php  while ( $coda_query->have_posts() ) : $coda_query->the_post(); ?>
			<?php remove_filter( 'the_content', 'c3m_slider_show', 9999 ); ?>
	        <div id="post-<?php the_ID(); ?>" <?php post_class( 'panel' ); ?>>

                <div class="panel-wrapper">
                    <h2 class="title"><?php the_title(); ?></h2>
	                <?php echo apply_filters( 'the_content', get_the_content() ); ?>
                </div> <!-- .panel-wrapper -->

            </div> <!-- .panel -->

	        <?php endwhile;
	        wp_reset_postdata(); ?>

		</div><!-- .coda-slider .preload -->
		</div><!-- coda-slider-wrapper -->

	<?php echo'<script type="text/javascript">
        		jQuery(document).ready(function($){
                    $(\'#' . $id . '\').codaSlider({
						autoHeight: '.$slider_args['autoHeight'].',
						autoSlide: '. $slider_args['autoSlide'] .',
						autoSlideInterval: '. $slider_args['autoSlideInterval'] .',
						autoSlideStopWhenClicked: '. $slider_args['autoSlideStopWhenClicked'] .',
						autoSlideDirection: "'. $slider_args['autoSlideDirection']. '",
						dynamicArrows: '. $slider_args['dynamicArrows'] . ',
						dynamicArrowsGraphical: '. $slider_args['dynamicArrowsGraphical']. ',
						dynamicArrowLeftText: "'. $slider_args['dynamicArrowLeftText'] .'",
						dynamicArrowRightText: "'. $slider_args['dynamicArrowRightText'] .'",
						dynamicTabs: '. $slider_args['dynamicTabs'] .',
						dynamicTabsAlign: "'. $slider_args['dynamicTabsAlign'] .'",
						dynamicTabsPosition: "'.$slider_args['dynamicTabsPosition']. '",
						slideEaseDuration: ' . $slider_args['slideEaseDuration'] .',
						slideEaseFunction: "' . $slider_args['slideEaseFunction'] .'"
					});
        		});
             </script>';

	}

function c3m_find_short_code( $shortcode = '' ) {
	$post_to_check = get_post( get_the_ID() );
	$found = false;

	if ( ! $shortcode )
		return $found;

	if ( stripos( $post_to_check->post_content, '[' . $shortcode ) !== false )
		$found = true;

	return $found;
}

	$wpcodaslider = new wpcodaslider();

	class wpcodaslider {
		var $shortcode_name = 'wpcodaslider';
		var $pattern = '<!-- wpcodaslider -->';
		var $posts_content = '';

		function __construct() {
			add_shortcode( $this->shortcode_name, array ( $this, 'shortcode' ) );

		}

		/**
		 * @param array $atts Extracted args from short code
		 * @param null $content
		 * @return string The queried posts
		 */

		function shortcode( $atts, $content = null ) {
			extract( shortcode_atts( array (
						'cat'  => null,
						'id'   => 'coda-slider',
						'show' => 5,
						'args' => null
					), $atts
				)
			);
			/**
			 * @var $cat
			 * @var $id
			 * @var $show
			 * @var $args
			 *
			 */
		if ( ! $cat )
				return 'Could not load slider. Post Category missing';
			$o = '<div class="coda-slider-wrapper">
                   <div class="coda-slider preload" id="' . $id . '">';

			$query_args = array (
				'post_type'      => 'post',
				'cat'            => (int) $cat,
				'posts_per_page' => (int) $show,
				'exclude'        => array( get_the_ID() )
			);
			$posts = get_posts( $query_args );
				foreach ( $posts as $post ) {

			$o .= '<div class="panel" id="post-' . $post->ID . '">
                    <div class="panel-wrapper">
                        <h2 class="title">' . $post->post_title . '</h2>
                        ' . apply_filters( 'the_content', $post->post_content ) . '
                    </div> <!-- .panel-wrapper --><!-- .panel #post-' . $post->ID . ' -->
				</div>';
			}
			$o .= '</div><!-- .coda-slider .preload -->'."\r\n";
            $o .= '</div><!-- coda-slider-wrapper -->'."\r\n";
            $o .= '<script type="text/javascript">'."\r\n";
			$o .= '//<![CDATA['."\r\n";
            $o .= 'jQuery(document).ready(function($){'."\r\n";
            $o .=  '$(\'#' . $id . '\').codaSlider({' . $args . '});'."\r\n";
            $o .=  '});'."\r\n";
			$o .= '//]]>';
            $o .=  '</script>';

			return $o;
		}
	}
/**
 * Enqueue the javascript and css on the front end
 */
function c3m_coda_scripts() {
	$short_code = c3m_find_short_code( 'wpcodaslider' );
	$display = get_post_meta( get_the_ID(), '_c3m_display', true );
	$function = get_post_meta( get_the_ID(), '_c3m_template_tag', true );
	if ( $short_code || 'before' == $display || 'function' == $function ) {
		wp_enqueue_script( 'jquery.easing', plugins_url( 'wp-coda-slider/js/jquery.easing.1.3.js' ), array( 'jquery' ) );
		wp_enqueue_script( 'coda_slider', plugins_url( 'wp-coda-slider/js/jquery.coda-slider-3.0.js' ), array( 'jquery', 'jquery.easing' ) );

		if ( file_exists( get_stylesheet_directory(). 'coda-slider.css' ) )
			wp_enqueue_style( 'coda_slider_css', get_stylesheet_directory_uri() . 'coda-slider.css' );
		else
			wp_enqueue_style( 'coda_slider_css', plugins_url( 'css/coda-slider-3.0.css', __FILE__ ) );

		wp_localize_script( 'coda_slider', 'Plugin_Url', array( 'plugin_url' => plugins_url( 'wp-coda-slider/images' ) ) );
	}
}

include_once dirname( __FILE__ ) . '/lib/deprecated.php';
