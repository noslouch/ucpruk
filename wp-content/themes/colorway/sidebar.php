<div class="grid_8 omega">
     <div class="sidebar">

	<?php if (function_exists('dynamic_sidebar') && dynamic_sidebar('mail_box')) : else : ?>
		
		<?php endif; ?>

<?php  if ( ! dynamic_sidebar( 'primary-widget-area' ) ) : ?>         
<?php  endif; // end primary widget area ?>
          


           
        <?php
	// A second sidebar for widgets, just because.
	if ( is_active_sidebar( 'secondary-widget-area' ) ) : ?>
          <?php dynamic_sidebar( 'secondary-widget-area' ); ?>
          <?php endif; ?>
     </div>
</div>
