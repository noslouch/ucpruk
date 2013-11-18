<p><?php _e('The following tools implement additional functionality to this plugin.', CCTM_TXTDOMAIN); ?></p>
<ul>
	<li>
		<a href="?page=cctm_tools&a=import_def"><img src="<?php print CCTM_URL; ?>/images/import.png" height="32" width="32" alt="Import"/></a>
		<a href="?page=cctm_tools&a=import_def"><?php _e('Import Definition', CCTM_TXTDOMAIN); ?></a>
		:
		<?php _e('Import a <code>.cctm.json</code> definition file to jump-start your site.',CCTM_TXTDOMAIN); ?>		 
	</li>
	<li>
		<a href="?page=cctm_tools&a=export_def"><img src="<?php print CCTM_URL; ?>/images/export.png" height="32" width="32" alt="Export"/></a>
		<a href="?page=cctm_tools&a=export_def"><?php _e('Export Definition', CCTM_TXTDOMAIN); ?></a>
		:
		<?php _e("Export your site's content-type and custom-field definitions.",CCTM_TXTDOMAIN); ?>
	</li>
	<li>
		<a href="?page=cctm_tools&a=clear_cache"><img src="<?php print CCTM_URL; ?>/images/clear_cache.png" height="32" width="32" alt="Clear cache"/></a>
		<a href="?page=cctm_tools&a=clear_cache"><?php _e('Clear Cache', CCTM_TXTDOMAIN); ?></a>
		:
		<?php _e("Clear any cached images or other files that have been generated.",CCTM_TXTDOMAIN); ?>
	</li>

	<!-- li><a href="?page=cctm_tools&a=detect_post_types"><?php _e('Detect Post Types', CCTM_TXTDOMAIN); ?></a></li -->
</ul>

<br />