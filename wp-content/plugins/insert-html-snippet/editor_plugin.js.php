<?php 
	require_once('../../../wp-load.php');
	require_once('../../../wp-admin/includes/admin.php');
	do_action('admin_init');
	
	if ( ! is_user_logged_in() )
		die('You must be logged in to access this script.');
	
	if(!isset($shortcodesXYZEH))
		$shortcodesXYZEH = new XYZ_Insert_Html_TinyMCESelector();
	
	global $wpdb;
// 	$ordered_sct = array_keys($shortcode_tags);
// 	sort($ordered_sct);

	$ordered_sct = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."xyz_ihs_short_code WHERE status='1'  ORDER BY id DESC" );
?>

(function() {
	//******* Load plugin specific language pack

	tinymce.create('tinymce.plugins.<?php echo $shortcodesXYZEH->buttonName; ?>', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			if(n=='<?php echo $shortcodesXYZEH->buttonName; ?>'){
                var mlb = cm.createListBox('<?php echo $shortcodesXYZEH->buttonName; ?>List', {
                     title : 'HTML Snippets',
                     onselect : function(v) { //Option value as parameter
                     if(v != ''){
	                     	if(tinyMCE.activeEditor.selection.getContent() != ''){
	                         	tinyMCE.activeEditor.selection.setContent('[' + v + ']' + tinyMCE.activeEditor.selection.getContent() + '[/' + v + ']');
	                        }
	                        else{
	                        	tinyMCE.activeEditor.selection.setContent('[' + v + ']');
	                        }
                        }
                     }
                });

                // Add some values to the list box
                <?php foreach($ordered_sct as $sct):?>
                	mlb.add('<?php echo $sct->title;?>', '<?php echo 'xyz-ihs snippet="'.$sct->title.'"';?>');
				<?php endforeach;?>

                // Return the new listbox instance
                return mlb;
             }
             
             return null;
		},

		
	});

	// Register plugin
	tinymce.PluginManager.add('<?php echo $shortcodesXYZEH->buttonName; ?>', tinymce.plugins.<?php echo $shortcodesXYZEH->buttonName; ?>);
})();
