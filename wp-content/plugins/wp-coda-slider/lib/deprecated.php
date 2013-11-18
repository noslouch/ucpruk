<?php
	/**
	 * Deprecated functions from past WP Coda Slider versions. You shouldn't use these
	 * functions and look for the alternatives instead. The functions will be
	 * removed in a later version.
	 *
	 * @package WP Coda Slider
	 * @subpackage Deprecated
	 */

	/**
	 * Deprecated functions come here to die.
	 */


	/**
	 * @deprecated 0.2.5
	 * @deprecated Use c3m_slider()
	 * @param $id string
	 * @param $cat integer
	 * @param $show integer
	 * @param $args string
	 */
	function c3m_wpcodaslider( $id, $cat, $show, $args ) {
		_deprecated_function( __FUNCTION__, '0.2.5', 'Use c3m_slider instead' );

		if ( 'function' != get_post_meta( get_the_ID(), '_c3m_template_tag', true ) )
			add_metadata( 'post', get_the_ID(), '_c3m_template_tag', 'function', true );

		$template_args = array (
			'post_type'      => 'post',
			'cat'            => $cat,
			'posts_per_page' => $show,
			'no_found_rows'  => true,
			'update_post_meta_cache' => false,
			'post_not_in'            => array ( get_the_ID() ),
		); ?>

	<div class="coda-slider-wrapper"> <!-- yes -->
        <div class="coda-slider preload <?php echo 'id="' . $id; ?>">

		<?php $q = new WP_Query( $template_args );
	        while( $q->have_posts() ) : $q->the_post();  ?>
			 <?php if ( c3m_find_short_code( 'wpcodaslider' ) )
			        continue; ?>
	        <div id="post-<?php the_ID(); ?>" class="panel">

                <div class="panel-wrapper">
                    <h2 class="title"><?php the_title();  ?></h2>
	                <?php the_content(); ?>
                </div> <!-- .panel-wrapper -->

            </div> <!-- .panel -->

	     <?php endwhile; wp_reset_postdata(); ?>

		</div><!-- .coda-slider .preload -->
		</div><!-- coda-slider-wrapper -->

	<?php echo'<script type="text/javascript">
        				jQuery(document).ready(function($){
                            $().ready(function() {
                                $(\'#' . $id . '\').codaSlider({' . $args . '});
                            });
        				});
                        </script>';

	}