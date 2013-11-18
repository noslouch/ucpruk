<?php

/*

/**

 * The main front page file.

 *

 * This is the most generic template file in a WordPress theme

 * and one of the two required files for a theme (the other being style.css).

 * It is used to display a page when nothing more specific matches a query. 

 * E.g., it puts together the home page when no home.php file exists.

 * Learn more: http://codex.wordpress.org/Template_Hierarchy

 *

 * @package WordPress

 * @subpackage Colorway

 * @since Colorway 1.0

 */

?>

<?php get_header(); ?>

<!--Start Slider-->

<div class="grid_24 slider">

  <div class="slider-container">

   <?php if (function_exists('nivoslider4wp_show')) { nivoslider4wp_show(); } ?>

    <!-- end nivoslider -->

  </div>
	<a href="http://www.ucpwfh.org/mu/rukeng/how-to-give/">
  <div class="sponsorwheelchair">

    <p>Give someone the freedom of mobility by donating a wheelchair today!</p>

  <img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif">

  </div></a>

</div>

<div class="clear"></div>

<!--End Slider-->
<!--Start Content Grid-->
<div class="grid_24 content">
    <div class="content-wrapper">
        <div  id="content">
            <div class="columns">
                <div class="one_fourth"> <a href="<?php echo inkthemes_get_option('inkthemes_link1'); ?>" class="bigthumbs">
                        <?php if (inkthemes_get_option('inkthemes_fimg1') != '') { ?>
                            <img src="<?php echo inkthemes_get_option('inkthemes_fimg1'); ?>"/>
                        <?php } else { ?>
                            <img src="<?php echo get_template_directory_uri(); ?>/images/1.jpg"/>
                        <?php } ?>
                    </a>
                   
                    <?php if (inkthemes_get_option('inkthemes_feature1') != '') { ?>
                        <p><?php echo inkthemes_get_option('inkthemes_feature1'); ?></p>
                    <?php } else { ?>
                        <p><?php _e('This Colorway Wordpress Theme gives you the easiness of building your site without any coding skills required.','colorway'); ?></p>
                    <?php } ?>
                </div>
                <div class="one_fourth middle"> <a href="<?php echo inkthemes_get_option('inkthemes_link2'); ?>" class="bigthumbs">
                        <?php if (inkthemes_get_option('inkthemes_fimg2') != '') { ?>
                            <img src="<?php echo inkthemes_get_option('inkthemes_fimg2'); ?>"/>
                        <?php } else { ?>
                            <img src="<?php echo get_template_directory_uri(); ?>/images/2.jpg"/>
                        <?php } ?>
                    </a>
                    
                    <?php if (inkthemes_get_option('inkthemes_feature2') != '') { ?>
                        <p><?php echo inkthemes_get_option('inkthemes_feature2'); ?></p>
                    <?php } else { ?>
                        <p><?php _e('The Colorway Wordpress Theme is highly optimized for Speed. So that your website opens faster than any similar themes.','colorway'); ?></p>
                    <?php } ?>
                </div>
                <div class="one_fourth last"> <a href="<?php echo inkthemes_get_option('inkthemes_link3'); ?>" class="bigthumbs">
                        <?php if (inkthemes_get_option('inkthemes_fimg3') != '') { ?>
                            <img src="<?php echo inkthemes_get_option('inkthemes_fimg3'); ?>"/>
                        <?php } else { ?>
                            <img src="<?php echo get_template_directory_uri(); ?>/images/3.jpg"/>
                        <?php } ?>
                    </a>
                    
                    <?php if (inkthemes_get_option('inkthemes_feature3') != '') { ?>
                        <p><?php echo inkthemes_get_option('inkthemes_feature3'); ?></p>
                    <?php } else { ?>
                        <p><?php _e('Visitors to the Website are very highly desirable. With the SEO Optimized Themes, You get more traffic from Google.','colorway'); ?></p>
                    <?php } ?>
                </div>
                <div class="one_fourth connect"> 
<?php if (function_exists('dynamic_sidebar') && dynamic_sidebar('mail_box')) : else : ?>
			<p><strong>Widget Ready</strong></p>
			<p>This mail_box is widget ready! Add one in the admin panel.</p>
		<?php endif; ?>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <?php if (inkthemes_get_option('inkthemes_testimonial') != '') { ?>
            <blockquote><?php echo inkthemes_get_option('inkthemes_testimonial'); ?></blockquote>
        <?php } else { ?>
            <blockquote><?php _e('Theme from InkThemes.com are based on P3+ Technology, giving high speed, easiness to built &amp; power of SEO for lending trustworthiness and experience to a customer. The Themes are really one of the best we saw everywhere.<br />
                - Neeraj Agarwal','colorway'); ?></blockquote>
        <?php } ?>
    </div>
</div>
<div class="clear"></div>
<!--End Content Grid-->
</div>
<!--End Container Div-->
<?php get_footer(); ?>