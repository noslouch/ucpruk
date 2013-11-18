<!--Start footer navigation-->
<div class="container_24 footer-navi">
  <div class="grid_24">
    <div class="navigation">
      <ul>
        <li>Copyright &copy; <?php echo date("Y") ?> <?php echo get_bloginfo('name'); ?> 
          <?php bloginfo('description'); ?> - All Rights Reserved
          </li>
      </ul>
      <!-- 
<div class="right-navi">
        <?php if ( inkthemes_get_option('colorway_twitter') !='' ) {?>
        <a href="<?php echo inkthemes_get_option('colorway_twitter'); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/twitter-icon.png" alt="twitter" title="Twitter"/></a>
        <?php } else {?>
        <?php }?>
        <?php if ( inkthemes_get_option('colorway_facebook') !='' ) {?>
        <a href="<?php echo inkthemes_get_option('colorway_facebook'); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/facebook-icon.png" alt="facebook" title="facebook"/></a>
        <?php } else {?>
        <?php }?>
        <?php if ( inkthemes_get_option('colorway_rss') !='' ) {?>
        <a href="<?php echo inkthemes_get_option('colorway_rss'); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/rss-icon.png" alt="rss" title="rss"/></a>
        <?php } else {?>
        <?php }?>
        <?php if ( inkthemes_get_option('colorway_linkedin') !='' ) {?>
        <a href="<?php echo inkthemes_get_option('colorway_linkedin'); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/linked.png" alt="linkedin" title="linkedin"/></a>
        <?php } else {?>
        <?php }?>
        <?php if ( inkthemes_get_option('colorway_stumble') !='' ) {?>
        <a href="<?php echo inkthemes_get_option('colorway_stumble'); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/stumbleupon.png" alt="stumble" title="stumble"/></a>
        <?php } else {?>
        <?php }?>
        <?php if ( inkthemes_get_option('colorway_digg') !='' ) {?>
        <a href="<?php echo inkthemes_get_option('colorway_digg'); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/digg.png" alt="digg" title="digg"/></a>
        <?php } else {?>
        <?php }?>
      </div>
 -->
    </div>
  </div>
  <div class="clear"></div>
</div>
<!--End Footer navigation-->
<!--Start Footer container-->
<!--End footer container-->
<?php wp_footer(); ?>

<script>
  jQuery(function($) {    

    $('.social_logo a').tipsy();
    $('a.zoombox').zoombox();

});
</script>

</body></html>
