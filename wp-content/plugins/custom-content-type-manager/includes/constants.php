<?php
/*------------------------------------------------------------------------------
These are defined in here because they have to be referenced by the AJAX
controllers as well as the main plugin. Sorry for the weirdness.

CCTM_PATH:does not contain a trailing slash, e.g.:
	/path/to/wp/html/wp-content/plugins/custom-content-type-manager
	
CCTM_URL: does not contain a trailing slash, e.g.:
	http://yoursite.com/wp-content/plugins/custom-content-type-manager
------------------------------------------------------------------------------*/
define('CCTM_PATH', dirname( dirname( __FILE__ ) ) );
define('CCTM_URL', WP_PLUGIN_URL .'/'. basename( CCTM_PATH ) );
define('CCTM_TXTDOMAIN', 'custom-content-type-mgr');

// For 3rd Party components
$upload_dir = wp_upload_dir();
define('CCTM_3P_PATH', $upload_dir['basedir'].'/cctm');
define('CCTM_3P_URL', $upload_dir['baseurl'].'/cctm');

/*EOF*/