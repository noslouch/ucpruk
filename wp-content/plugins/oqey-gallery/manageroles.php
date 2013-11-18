<?php
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'manageroles.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!');

oqey_update_roles();

function oqey_update_roles(){

if (isset($_POST['update_roles'])){	

	check_admin_referer('oqey_add_roles');

	oqey_set_roles($_POST['oQeySettings'],"oQeySettings");
	oqey_set_roles($_POST['oQeyGalleries'],"oQeyGalleries");
	oqey_set_roles($_POST['oQeyVideo'],"oQeyVideo");
	oqey_set_roles($_POST['oQeySkins'],"oQeySkins");
	oqey_set_roles($_POST['oQeyMusic'],"oQeyMusic");
	oqey_set_roles($_POST['oQeyTrash'],"oQeyTrash");
	
	$mesaj = '<div style="width:903px; margin-bottom:10px;" class="updated fade">'.__('Roles updated.', 'oqey-gallery').'</div>';
}
?>
   <div class="wrap">  
    <h2 style="width: 830px;"><?php _e('Roles', 'oqey-gallery'); ?>
    <div style="margin-left:250px; float:right; width: 200px; height: 20px;">
     <div id="fb-root"></div>
     <div class="fb-like" data-href="http://www.facebook.com/oqeysites" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="tahoma"></div>
     <div class="fb-send" data-href="http://oqeysites.com"></div>
    </div>
    </h2>
    
    <?php echo $mesaj; ?>
	<p><?php _e('Check the lowest level role which you consider should be given access to the following management privileges. Standard WordPress roles are supported.', 'oqey-gallery'); ?> <br />
   </div>

	<form name="oqey_roles" id="oqey_roles" method="POST" accept-charset="utf-8" >
		<?php wp_nonce_field('oqey_add_roles') ?>
			<table class="form-table"> 
			<tr valign="top"> 
				<th scope="row"><?php _e('oQey Settings', 'oqey-gallery'); ?>:</th> 
				<td><label for="tinymce"><select name="oQeySettings" id="oQeySettings"><?php wp_dropdown_roles( oqey_get_role('oQeySettings') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Manage Galleries', 'oqey-gallery'); ?>:</th> 
				<td><label for="add_gallery"><select name="oQeyGalleries" id="oQeyGalleries"><?php wp_dropdown_roles( oqey_get_role('oQeyGalleries') ); ?></select></label></td>
			</tr>
            <tr valign="top"> 
				<th scope="row"><?php _e('Manage Video', 'oqey-gallery'); ?>:</th> 
				<td><label for="add_video"><select name="oQeyVideo" id="oQeyVideo"><?php wp_dropdown_roles( oqey_get_role('oQeyVideo') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Manage Skins', 'oqey-gallery'); ?>:</th> 
				<td><label for="manage_gallery"><select name="oQeySkins" id="oQeySkins"><?php wp_dropdown_roles( oqey_get_role('oQeySkins') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Manage Music', 'oqey-gallery'); ?>:</th> 
				<td><label for="manage_others"><select name="oQeyMusic" id="oQeyMusic"><?php wp_dropdown_roles( oqey_get_role('oQeyMusic') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Manage Trash', 'oqey-gallery'); ?>:</th> 
				<td><label for="manage_tags"><select name="oQeyTrash" id="oQeyTrash"><?php wp_dropdown_roles( oqey_get_role('oQeyTrash') ); ?></select></label></td>
			</tr>
			</table>
			<div class="submit"><input type="submit" class="button-primary" name= "update_roles" value="<?php _e('Update roles', 'oqey-gallery') ;?>"/></div>
	</form>
	
<?php 

}

function oqey_get_sorted_roles() {
	// This function returns all roles, sorted by user level (lowest to highest)
	global $wp_roles;
	$roles = $wp_roles->role_objects;
	$sorted = array();
	
	if( class_exists('RoleManager') ) {
		foreach( $roles as $role_key => $role_name ) {
			$role = get_role($role_key);
			if( empty($role) ) continue;
			$role_user_level = array_reduce(array_keys($role->capabilities), array('WP_User', 'level_reduction'), 0);
			$sorted[$role_user_level] = $role;
		}
		$sorted = array_values($sorted);
	} else {
		$role_order = array("subscriber", "contributor", "author", "editor", "administrator");
		foreach($role_order as $role_key) {
			$sorted[$role_key] = get_role($role_key);
		}
	}
	return $sorted;
}

function oqey_get_role($capability){
	// This function return the lowest roles which has the capabilities
	$check_order = oqey_get_sorted_roles();

	$args = array_slice(func_get_args(), 1);
	$args = array_merge(array($capability), $args);

	foreach ($check_order as $check_role) {
		if ( empty($check_role) )
			return false;
			
		if (call_user_func_array(array(&$check_role, 'has_cap'), $args))
			return $check_role->name;
	}
	return false;
}

function oqey_set_roles($lowest_role, $capability){
	// This function set or remove the $capability
	$check_order = oqey_get_sorted_roles();

	$add_capability = false;
	
	foreach ($check_order as $the_role) {
		$role = $the_role->name;

		if ( $lowest_role == $role )
			$add_capability = true;
		
		// If you rename the roles, then please use a role manager plugin
		
		if ( empty($the_role) )
			continue;
			
		$add_capability ? $the_role->add_cap($capability) : $the_role->remove_cap($capability) ;
	}
}
?>