<?php
/*
Template Name: Section Page - Custom Sidebar 1
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
  		<a href="http://205.186.144.247/ucpruk.org/html/ruk/how-to-give/"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif"></a>
  	</div>
    <div class="clear">
   </div>
    <div class="pagecontent">
     	<div class="editable-area">
    	<h2>
     	 <?php the_title(); ?>
    	</h2>
    	<?php the_content(); ?>
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
