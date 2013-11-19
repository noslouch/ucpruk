<?php
/*
Template Name: Column Top Level Landing Page
*/
?>
<?php
function your_thumbnail_caption($html, $post_id, $post_thumbnail_id, $size, $attr)
{
$attachment =& get_post($post_thumbnail_id);

if ($attachment->post_excerpt || $attachment->post_content) {
$html .= '<p class="thumbcaption">';
if ($attachment->post_excerpt) {
$html .= '<span class="captitle">'.$attachment->post_excerpt.'</span> ';
}
$html .= $attachment->post_content.'</p>';
}

return $html;
}

add_action('post_thumbnail_html', 'your_thumbnail_caption', null, 5);
?>
<?php get_header(); ?>
<!--Start Content Grid-->
<div class="grid_24 content">
  <div class="content-wrapper">
  	<div class="photo-container">
    <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
    <?php the_post_thumbnail(array(690,310));?>
    </div>
    <div class="sponsorwheelchair short">
    	<!-- <p>Give someone the freedom of mobility by donating a wheelchair today!</p> -->
  		<a href="http://205.186.144.247/ucpruk.org/html/how-to-give/"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif"></a>
  	</div>
    <div class="clear">
   </div>
    <div class="pagecontent">
     	<div class="editable-area">
    	
    		

			<h2><?php the_title(); ?></h2>
		
		
			<div class="container_24 grid_4 landing first">
				<h3><a href="<?php print_custom_field('col1_link');?>"><?php print_custom_field('col1_title'); ?></a></h3>
				<p><a href="<?php print_custom_field('col1_link:to_link_href');?>"><?php print_custom_field('landingpage_thumb1'); ?></a></p>
				<p><?php print_custom_field('sectionsummaryone'); ?></p>
			</div>
			
			
			<div class="container_24 grid_4 landing">
				<h3><a href="<?php print_custom_field('col2_link');?>"><?php print_custom_field('col2_title'); ?></a></h3>
				<p><a href="<?php print_custom_field('col2_link:to_link_href');?>"><?php print_custom_field('landingpage_thumb2'); ?></a></p>
				<p><?php print_custom_field('sectionsummarytwo'); ?></p>
			</div>
		
			<div class="container_24 grid_4 landing">
				<h3><a href="<?php print_custom_field('col3_link');?>"><?php print_custom_field('col3_title'); ?></a></h3>
				<p><a href="<?php print_custom_field('col3_link:to_link_href');?>"><?php print_custom_field('landingpage_thumb3'); ?></a></p>
				<p><?php print_custom_field('sectionsummarythree'); ?></p>
			</div>
		
			<div class="container_24 grid_4 landing">
				<h3><a href="<?php print_custom_field('col4_link');?>"><?php print_custom_field('col4_title'); ?></a></h3>
				<p><a href="<?php print_custom_field('col4_link:to_link_href');?>"><?php print_custom_field('landingpage_thumb4'); ?></a></p>
				<p><?php print_custom_field('sectionsummaryfour'); ?></p>
			</div>
		
		</div>
		<?php endwhile; ?>
    	<?php endif; ?>
    	<?php get_sidebar(); ?>
  </div>
  </div>
</div>
<div class="clear"></div>
<!--End Content Grid-->
</div>
<!--End Container Div-->
<?php get_footer(); ?>
