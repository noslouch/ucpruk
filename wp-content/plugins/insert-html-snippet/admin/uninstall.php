<?php 

function xyz_ihs_uninstall(){

global $wpdb;

delete_option("xyz_ihs_limit");

/* table delete*/
$wpdb->query("DROP TABLE ".$wpdb->prefix."xyz_ihs_short_code");


}
register_uninstall_hook(XYZ_INSERT_HTML_PLUGIN_FILE,'xyz_ihs_uninstall');
?>