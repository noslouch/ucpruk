<?php
/** 
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Colorway
 * @since Colorway 1.0
 */
get_header(); ?>
<!--Start Content Grid-->
<div class="grid_24 content">
  <div  class="grid_16 alpha">
    <div class="content-wrap">
      <div class="content-info">
        <?php if (function_exists('inkthemes_breadcrumbs')) inkthemes_breadcrumbs(); ?>
      </div>
      <!--Start Blog Post-->
      <div class="blog">
        <ul class="single">
          <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
          <li>
            <h2><?php the_title(); ?></h2>
            Posted on
            <?php the_time('F j, Y'); ?>
            by
            <?php the_author_posts_link() ?>
            <div class="clear"></div>
            
						<div class="entry-attachment">
							<div class="attachment">
<?php
	/**
	 * Grab the IDs of all the image attachments in a gallery so we can get the URL of the next adjacent image in a gallery,
	 * or the first image (if we're looking at the last image in a gallery), or, in a gallery of one, just the link to that image file
	 */
	$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
	foreach ( $attachments as $k => $attachment ) {
		if ( $attachment->ID == $post->ID )
			break;
	}
	$k++;
	// If there is more than 1 attachment in a gallery
	if ( count( $attachments ) > 1 ) {
		if ( isset( $attachments[ $k ] ) )
			// get the URL of the next image attachment
			$next_attachment_url = get_attachment_link( $attachments[ $k ]->ID );
		else
			// or get the URL of the first image attachment
			$next_attachment_url = get_attachment_link( $attachments[ 0 ]->ID );
	} else {
		// or, if there's only 1 image, get the URL of the image
		$next_attachment_url = wp_get_attachment_url();
	}
?>
								<a href="<?php echo esc_url( $next_attachment_url ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" rel="attachment"><?php
								$attachment_size = apply_filters( 'colorway_attachment_size', 848 );
								echo wp_get_attachment_image( $post->ID, array( $attachment_size, 1024 ) ); // filterable image width with 1024px limit for image height.
								?></a>

								<?php if ( ! empty( $post->post_excerpt ) ) : ?>
								<div class="entry-caption">
									<?php the_excerpt(); ?>
								</div>
								<?php endif; ?>
							</div><!-- .attachment -->

						</div><!-- .entry-attachment -->
						<div class="entry-description">
							<?php the_content(); ?>
							<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>Pages:</span>', 'after' => '</div>' ) ); ?>
						</div><!-- .entry-description -->
            <div class="clear"></div>
            <div class="tags">
              <?php the_tags('Post Tagged with ',', ',''); ?>
            </div>
            <div class="clear"></div>
			<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . 'Pages:' . '</span>', 'after' => '</div>' ) ); ?>
            <?php endwhile;?>
          <nav id="nav-single">
				<span class="nav-previous"><?php previous_image_link( false, '&larr; Previous Image' ); ?></span>
				<span class="nav-next"><?php next_image_link( false, 'Next Image &rarr;' ); ?></span>
            </span> </nav>
          </li>
          <!-- End the Loop. -->          
        </ul>
      </div>
      <div class="hrline"></div>
      <!--End Blog Post-->
      <div class="clear"></div>
      <div class="social_link">
        <p>If you enjoyed this article please consider sharing it!</p>
      </div>
      <div class="social_logo"> <a title="Tweet this!" href="http://twitter.com/home/?status=<?php the_title(); ?> : <?php the_permalink(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/twitter-share.png" alt="twitter" title="twitter"/></a> <a title="Share on StumbleUpon!" href="http://www.stumbleupon.com/submit?url=<?php the_permalink(); ?>&amp;amp;title=<?php the_title(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/stumbleupon-share.png" alt="upon" title="upon"/></a> <a title="Share on Facebook" href="http://www.facebook.com/sharer.php?u=<?php the_permalink();?>&amp;amp;t=<?php the_title(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/facebook-share.png" alt="facebook" title="facebook"/></a> <a title="Digg This!" href="http://digg.com/submit?phase=2&amp;amp;url=<?php the_permalink(); ?>&amp;amp;title=<?php the_title(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/digg-share.png" alt="digg" title="digg"/></a> </div>
      <div class="clear"></div>
      <!--Start Comment Section-->
      <div class="comment_section">
        <!--Start Comment list-->
        <?php comments_template( '', true ); ?>
        <!--End Comment Form-->
      </div>
      <!--End comment Section-->
    </div>
  </div>
  <?php get_sidebar(); ?>
</div>
<div class="clear"></div>
<!--End Content Grid-->
</div>
<!--End Container Div-->
<?php get_footer(); ?>
