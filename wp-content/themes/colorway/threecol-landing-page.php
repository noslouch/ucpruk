<?php
/*
Template Name: Column Top Level Landing Page 3 Columns 2 Rows
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
  		<a href="http://ucpruk.org/how-to-give/"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif"></a>
  	</div>
    <div class="clear">
   </div>
    <div class="pagecontent">
     	<div class="editable-area threecol">
    	
    		

			<h2><?php the_title(); ?></h2>
		
		
			<div class="container_24 grid_4 landing first">
				<h3><a href="<?php print_custom_field('col1_link_row1');?>"><?php print_custom_field('col1_row1_title'); ?></a></h3>
				<p><a href="<?php print_custom_field('col1_link_row1:to_link_href');?>"><?php print_custom_field('landingpage_thumb1_row1'); ?></a></p>
				<p><?php print_custom_field('sectionsummaryonerowone'); ?></p>
			</div>
			
			
			<div class="container_24 grid_4 landing">
				<h3><a href="<?php print_custom_field('col2_link_row1');?>"><?php print_custom_field('col2_row1_title'); ?></a></h3>
				<p><a href="<?php print_custom_field('col2_link_row1:to_link_href');?>"><?php print_custom_field('landingpage_thumb2'); ?></a></p>
				<p><?php print_custom_field('sectionsummarytworowone'); ?></p>
			</div>
		
			<div class="container_24 grid_4 landing">
				<h3><a href="<?php print_custom_field('col3_link_row1');?>"><?php print_custom_field('col3_title_row1'); ?></a></h3>
				<p><a href="<?php print_custom_field('col3_link_row1:to_link_href');?>"><?php print_custom_field('landingpage_thumb3_row1'); ?></a></p>
				<p><?php print_custom_field('sectionsummarythreerowone'); ?></p>
			</div>
		
			
			<div class="container_24 grid_4 landing first">
				<h3><a href="<?php print_custom_field('col1_link_row2');?>"><?php print_custom_field('col1_row1_title_copy'); ?></a></h3>
				<p><a href="<?php print_custom_field('col1_link_row2:to_link_href');?>"><?php print_custom_field('landingpage_thumb1_row2'); ?></a></p>
				<p><?php print_custom_field('sectionsummaryonerowtwo'); ?></p>
			</div>
			
			
			<div class="container_24 grid_4 landing">
				<h3><a href="<?php print_custom_field('col2_link_row2');?>"><?php print_custom_field('col2_row2_title'); ?></a></h3>
				<p><a href="<?php print_custom_field('col2_link_row2:to_link_href');?>"><?php print_custom_field('landingpage_thumb2_row2'); ?></a></p>
				<p><?php print_custom_field('sectionsummarytworowtwo'); ?></p>
			</div>
		
			<div class="container_24 grid_4 landing">
				<h3><a href="<?php print_custom_field('col3_link_row2');?>"><?php print_custom_field('col3_title_row2'); ?></a></h3>
				<p><a href="<?php print_custom_field('col3_link_row2:to_link_href');?>"><?php print_custom_field('landingpage_thumb3_row2'); ?></a></p>
				<p><?php print_custom_field('sectionsummarythreerowtwo'); ?></p>
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
