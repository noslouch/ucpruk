<?php
/*
Plugin Name: Custom sidebars
Plugin URI: http://marquex.es/541/custom-sidebars-plugin-v0-8
Description: Allows to create your own widgetized areas and custom sidebars, and select what sidebars to use for each post or page.
Version: 0.8.2
Author: Javier Marquez
Author URI: http://marquex.es
*/

if(!class_exists('CustomSidebars')):

class CustomSidebars{
	
	var $message = '';
	var $message_class = '';
	
	//The name of the option that stores the info of the new bars.
	var $option_name = "cs_sidebars";
	//The name of the option that stores which bars are replaceable, and the default
	//replacements. The value is stored in $this->options
	var $option_modifiable = "cs_modifiable";
	
	
	var $sidebar_prefix = 'cs-';
	var $postmeta_key = '_cs_replacements';
	var $cap_required = 'switch_themes';
	var $ignore_post_types = array('attachment', 'revision', 'nav_menu_item', 'pt-widget');
	var $options = array();
	
	var $replaceable_sidebars = array();
	var $replacements = array();
	var $replacements_todo;
	
	function CustomSidebars(){
		$this->retrieveOptions();
		$this->replaceable_sidebars = $this->getModifiableSidebars();
		$this->replacements_todo = sizeof($this->replaceable_sidebars);
		foreach($this->replaceable_sidebars as $sb)
			$this->replacements[$sb] = FALSE;
	}
	
	function retrieveOptions(){
		$this->options = get_option($this->option_modifiable);
	}
	
	function getCustomSidebars(){
		$sidebars = get_option($this->option_name);
		if($sidebars)
			return $sidebars;
		return array();
	}
	
	function getThemeSidebars($include_custom_sidebars = FALSE){
		
		global $wp_registered_sidebars;		
		$allsidebars = $wp_registered_sidebars;
		ksort($allsidebars);
		if($include_custom_sidebars)
			return $allsidebars;
		
		$themesidebars = array();
		foreach($allsidebars as $key => $sb){
			if(substr($key, 0, 3) != $this->sidebar_prefix)
				$themesidebars[$key] = $sb;
		}
		
		return $themesidebars;
	}
	
	function registerCustomSidebars(){
		$sb = $this->getCustomSidebars();
		if(!empty($sb)){
			foreach($sb as $sidebar){
				register_sidebar($sidebar);
			}
		}
	}
	
	function replaceSidebars(){
		global $_wp_sidebars_widgets, $post, $wp_registered_sidebars, $wp_registered_widgets;
		
		$original_widgets = $_wp_sidebars_widgets;
		
		$updated = FALSE;
		
		$replaceables = $this->replaceable_sidebars;
		$defaults = $this->getDefaultReplacements();
		
		do_action('cs_predetermineReplacements');
		
		$this->determineReplacements($defaults);
		
		foreach($this->replacements as $sb_name => $replacement_info){
			if($replacement_info){
				list($replacement, $replacement_type, $extra_index) = $replacement_info;
				if($this->checkAndFixSidebar($sb, $replacement, $replacement_type, $extra_index)){
					if(sizeof($original_widgets[$replacement]) == 0){ //No widgets on custom bar, show nothing
						$wp_registered_widgets['csemptywidget'] = $this->getEmptyWidget();
						$_wp_sidebars_widgets[$sb_name] = array('csemptywidget');
					}
					else{
						$_wp_sidebars_widgets[$sb_name] = $original_widgets[$replacement];
						//replace before/after widget/title?
						$sidebar_for_replacing = $wp_registered_sidebars[$replacement];
						if($this->replace_before_after_widget($sidebar_for_replacing))
							$wp_registered_sidebars[$sb_name] = $sidebar_for_replacing;
					}
				}
			}
		}
	}

	function determineReplacements($defaults){
		//posts
		if(is_single()){
			//print_r("Single");
			//Post sidebar
			global $post;
			$replacements = get_post_meta($post->ID, $this->postmeta_key, TRUE);
			foreach($this->replaceable_sidebars as $sidebar){
				if(is_array($replacements) && !empty($replacements[$sidebar])){
					$this->replacements[$sidebar] = array($replacements[$sidebar], 'particular', -1);
					$this->replacements_todo--;
				}
			}
				
			//Category sidebar
			global $sidebar_category;
			if($this->replacements_todo > 0){
				$categories = $this->getSortedCategories();
				$i = 0;
				while($this->replacements_todo > 0 && $i < sizeof($categories)){
					foreach($this->replaceable_sidebars as $sidebar){
						if(!$this->replacements[$sidebar] && !empty($defaults['category_posts'][$categories[$i]->cat_ID][$sidebar])){
							$this->replacements[$sidebar] = array($defaults['category_posts'][$categories[$i]->cat_ID][$sidebar], 
																	'category_posts', 
																	$sidebar_category);
							$this->replacements_todo--;
						}
					}
					$i++;
				}
			}
			//Post-type sidebar
			if($this->replacements_todo > 0){
				$post_type = get_post_type($post);
				foreach($this->replaceable_sidebars as $sidebar){
				if(isset($defaults['post_type_posts'][$post_type]) && isset($defaults['post_type_posts'][$post_type][$sidebar]))
					$this->replacements[$sidebar] = array($defaults['post_type_posts'][$post_type][$sidebar], 'defaults', $post_type);
					$this->replacements_todo--;
				}
			}
			return;
		}
		//Category archive
		if(is_category()){
			global $sidebar_category, $wp_query;
			$category_object = $wp_query->get_queried_object();
			$current_category = $category_object->term_id;
			while($current_category != 0 && $this->replacements_todo > 0){
				foreach($this->replaceable_sidebars as $sidebar){
					if(!$this->replacements[$sidebar] && !empty($defaults['category_pages'][$current_category][$sidebar])){
						$this->replacements[$sidebar] = array($defaults['category_pages'][$current_category][$sidebar], 'category_pages', $current_category);
						$this->replacements_todo--;
					}
				}
				$current_category = $category_object->category_parent;
				if($current_category != 0)
					$category_object = get_category($current_category);
			}	
			return;
		}
		
		//post type archive
		if(!is_category() && !is_singular() && get_post_type()!='post'){
			$post_type = get_post_type();
			foreach($this->replaceable_sidebars as $sidebar){
				if(isset($defaults['post_type_pages'][$post_type]) && isset($defaults['post_type_pages'][$post_type][$sidebar])){
					$this->replacements[$sidebar] = array($defaults['post_type_pages'][$post_type][$sidebar], 'post_type_pages', $post_type);
					$this->replacements_todo--;
				}
			}
			return;
		}
		//Page sidebar
		if(is_page()){
			global $post;
			$replacements = get_post_meta($post->ID, $this->postmeta_key, TRUE);
			foreach($this->replaceable_sidebars as $sidebar){
				if(is_array($replacements) && !empty($replacements[$sidebar])){
					$this->replacements[$sidebar] = array($replacements[$sidebar], 'particular', -1);
					$this->replacements_todo--;
				}
			}
						
			//Page Post-type sidebar
			if($this->replacements_todo > 0){
				$post_type = get_post_type($post);
				foreach($this->replaceable_sidebars as $sidebar){
				if(!$this->replacements[$sidebar] && isset($defaults['post_type_posts'][$post_type]) && isset($defaults['post_type_posts'][$post_type][$sidebar]))
					$this->replacements[$sidebar] = array($defaults['post_type_posts'][$post_type][$sidebar], 'defaults', $post_type);
					$this->replacements_todo--;
				}
			}
			return;
		}
		
		if(is_home()){
			foreach($this->replaceable_sidebars as $sidebar){
				if(! empty($defaults['blog'][$sidebar]))
					$this->replacements[$sidebar] = array($defaults['blog'][$sidebar], 'blog', -1);
			}
			return;
		}
		
		if(is_tag()){
			foreach($this->replaceable_sidebars as $sidebar){
				if(! empty($defaults['tags'][$sidebar]))
					$this->replacements[$sidebar] = array($defaults['tags'][$sidebar], 'tags', -1);
			}
			return;
		}
		
		if(is_author()){
			foreach($this->replaceable_sidebars as $sidebar){
				if(! empty($defaults['authors'][$sidebar]))
					$this->replacements[$sidebar] = array($defaults['authors'][$sidebar], 'authors', -1);
			}
			return;
		}
	}
	
	function checkAndFixSidebar($sidebar, $replacement, $method, $extra_index){
		global $wp_registered_sidebars;
		
		
		if(isset($wp_registered_sidebars[$replacement]))
			return true;
		
		if($method == 'particular'){
			global $post;
			$sidebars = get_post_meta($post->ID, $this->postmeta_key, TRUE);
			if($sidebars && isset($sidebars[$sidebar])){
				unset($sidebars[$sidebar]);
				update_post_meta($post->ID, $this->postmeta_key, $sidebars);	
			}
		}
		else{
			if(isset($this->options[$method])){
				if($extra_index != -1 && isset($this->options[$method][$extra_index]) && isset($this->options[$method][$extra_index][$sidebar])){
					unset($this->options[$method][$extra_index][$sidebar]);
					update_option($this->option_modifiable, $this->options);
				}
				if($extra_index == 1 && isset($this->options[$method]) && isset($this->options[$method][$sidebar])){
					unset($this->options[$method][$sidebar]);
					update_option($this->option_modifiable, $this->options);				
				}
			}
		}
		
		return false;
	}
	
	function replace_before_after_widget($sidebar){
		return (trim($sidebar['before_widget']) != '' OR
			trim($sidebar['after_widget']) != '' OR
			trim($sidebar['before_title']) != '' OR
			trim($sidebar['after_title']) != '');
	}
	
	function deleteSidebar(){
		if(! current_user_can($this->cap_required) )
			return new WP_Error('cscantdelete', __('You do not have permission to delete sidebars','custom-sidebars'));
			
		if (! wp_verify_nonce($_REQUEST['_n'], 'custom-sidebars-delete') ) die('Security check stop your request.'); 
		
		$newsidebars = array();
		$deleted = FALSE;
		
		$custom = $this->getCustomSidebars();
		
		if(!empty($custom)){
		
		foreach($custom as $sb){
			if($sb['id']!=$_GET['delete'])
				$newsidebars[] = $sb;
			else
				$deleted = TRUE;
		}
		}//endif custom
		
		//update option
		update_option( $this->option_name, $newsidebars );

		$this->refreshSidebarsWidgets();
		
		if($deleted)
			$this->setMessage(sprintf(__('The sidebar "%s" has been deleted.','custom-sidebars'), $_GET['delete']));
		else
			$this->setError(sprintf(__('There was not any sidebar called "%s" and it could not been deleted.','custom-sidebars'), $_GET['delete']));
	}
	
	function createPage(){
		
		//$this->refreshSidebarsWidgets();
		if(!empty($_POST)){
			if(isset($_POST['create-sidebars'])){
				check_admin_referer('custom-sidebars-new');
				$this->storeSidebar();
			}
			else if(isset($_POST['update-sidebar'])){
				check_admin_referer('custom-sidebars-update');
				$this->updateSidebar();
			}		
			else if(isset($_POST['update-modifiable']))
				$this->updateModifiable();
			else if(isset($_POST['update-defaults-posts']) OR isset($_POST['update-defaults-pages'])){
				$this->storeDefaults();
			
			}
				
			else if(isset($_POST['reset-sidebars']))
				$this->resetSidebars();			
				
			$this->retrieveOptions();
		}
		else if(!empty($_GET['delete'])){
			$this->deleteSidebar();
			$this->retrieveOptions();			
		}
		else if(!empty($_GET['p'])){
			if($_GET['p']=='edit' && !empty($_GET['id'])){
				$customsidebars = $this->getCustomSidebars();
				if(! $sb = $this->getSidebar($_GET['id'], $customsidebars))
					return new WP_Error('cscantdelete', __('You do not have permission to delete sidebars','custom-sidebars'));
				include('view-edit.php');
				return;	
			}
		}
		
		$customsidebars = $this->getCustomSidebars();
		$themesidebars = $this->getThemeSidebars();
		$allsidebars = $this->getThemeSidebars(TRUE);
		$defaults = $this->getDefaultReplacements();
		$modifiable = $this->replaceable_sidebars;
		$post_types = $this->getPostTypes();
		
		$deletenonce = wp_create_nonce('custom-sidebars-delete');
		
		//var_dump($defaults);
		
		//Form
		if(!empty($_GET['p'])){
			if($_GET['p']=='defaults'){
				$categories = get_categories(array('hide_empty' => 0));
				if(sizeof($categories)==1 && $categories[0]->cat_ID == 1)
					unset($categories[0]);
					
				include('view-defaults.php');
			}
			else if($_GET['p']=='edit')
				include('view-edit.php');
			else
				include('view.php');	
				
		}
		else		
			include('view.php');		
	}
	
	function addSubMenus(){
		$page = add_submenu_page('themes.php', __('Custom sidebars','custom-sidebars'), __('Custom sidebars','custom-sidebars'), $this->cap_required, 'customsidebars', array($this, 'createPage'));
		
        add_action('admin_print_scripts-' . $page, array($this, 'addScripts'));
	}
	
	function addScripts(){
		wp_enqueue_script('post');
		echo '<link type="text/css" rel="stylesheet" href="'. plugins_url('/cs_style.css', __FILE__) .'" />';
	}
	
	function addMetaBox(){
		global $post;
		$post_type = get_post_type($post);
		if($post_type && !(array_search($post_type, $this->ignore_post_types))){
			$post_type_object = get_post_type_object($post_type);
			if($post_type_object->publicly_queryable || $post_type_object->public)
				add_meta_box('customsidebars-mb', 'Sidebars', array($this,'printMetabox'), $post_type, 'side');
		}
	}
	
	function printMetabox(){
		global $post, $wp_registered_sidebars;
		
		$replacements = $this->getReplacements($post->ID);
			
		//$available = array_merge(array(''), $this->getThemeSidebars(TRUE));
		$available = $wp_registered_sidebars;
		ksort($available);
		$sidebars = $this->replaceable_sidebars;
		$selected = array();
		if(!empty($sidebars)){
			foreach($sidebars as $s){
				if(isset($replacements[$s]))
					$selected[$s] = $replacements[$s];
				else
					$selected[$s] = '';
			}
		}
		
		include('metabox.php');
	}
	
	function loadTextDomain(){
		$dir = basename(dirname(__FILE__))."/lang";
		load_plugin_textdomain( 'custom-sidebars', 'wp-content/plugins/'.$dir, $dir);
	}
	
	function getReplacements($postid){
		$replacements = get_post_meta($postid, $this->postmeta_key, TRUE);
		if($replacements == '')
			$replacements = array();
		else
			$replacements = $replacements;
		return $replacements;
	}
	
	function getModifiableSidebars(){
		if( $modifiable = $this->options ) //get_option($this->option_modifiable) )
			return $modifiable['modifiable'];
		return array(); 
	}
	
	function getDefaultReplacements(){
		if( $defaults = $this->options ){//get_option($this->option_modifiable) )
			$defaults['post_type_posts'] = $defaults['defaults'];
			unset($defaults['modifiable']);
			unset($defaults['defaults']);
			return $defaults;
		}
		return array(); 
	}
	
	function updateModifiable(){
		check_admin_referer('custom-sidebars-options', 'options_wpnonce');
		$options = $this->options ? $this->options : array();
		
		//Modifiable bars
		if(isset($_POST['modifiable']) && is_array($_POST['modifiable']))
			$options['modifiable'] = $_POST['modifiable'];

		
		if($this->options !== FALSE)
			update_option($this->option_modifiable, $options);
		else
			add_option($this->option_modifiable, $options);
			
		$this->setMessage(__('The custom sidebars settings has been updated successfully.','custom-sidebars'));
	}
	
	function storeDefaults(){
		
		$options = $this->options;
		$modifiable = $this->replaceable_sidebars;
		
		//Post-types posts and lists. Posts data are called default in order to keep backwards compatibility;
		
		$options['defaults'] = array();
		$options['post_type_pages'] = array();
		
		foreach($this->getPostTypes() as $pt){
			if(!empty($modifiable)){
				foreach($modifiable as $m){
					if(isset($_POST["type_posts_{$pt}_$m"]) && $_POST["type_posts_{$pt}_$m"]!=''){
						if(! isset($options['defaults'][$pt]))
							$options['defaults'][$pt] = array();
						
						$options['defaults'][$pt][$m] = $_POST["type_posts_{$pt}_$m"];
					}
					
					if(isset($_POST["type_page_{$pt}_$m"]) && $_POST["type_page_{$pt}_$m"]!=''){
						if(! isset($options['post_type_pages'][$pt]))
							$options['post_type_pages'][$pt] = array();
						
						$options['post_type_pages'][$pt][$m] = $_POST["type_page_{$pt}_$m"];
					}
				}
			}
		}
		
		
		//Category posts and post lists.
		
		$options['category_posts'] = array();
		$options['category_pages'] = array();
		$categories = get_categories(array('hide_empty' => 0));
		foreach($categories as $c){
			if(!empty($modifiable)){
				foreach($modifiable as $m){
					$catid = $c->cat_ID;
					if(isset($_POST["category_posts_{$catid}_$m"]) && $_POST["category_posts_{$catid}_$m"]!=''){
						if(! isset($options['category_posts'][$catid]))
							$options['category_posts'][$catid] = array();
						
						$options['category_posts'][$catid][$m] = $_POST["category_posts_{$catid}_$m"];
					}
					
					if(isset($_POST["category_page_{$catid}_$m"]) && $_POST["category_page_{$catid}_$m"]!=''){
						if(! isset($options['category_pages'][$catid]))
							$options['category_pages'][$catid] = array();
						
						$options['category_pages'][$catid][$m] = $_POST["category_page_{$catid}_$m"];
					}
				}
			}
		}
		
		// Blog page
		
		$options['blog'] = array();
		if(!empty($modifiable)){
			foreach($modifiable as $m){
				if(isset($_POST["blog_page_$m"]) && $_POST["blog_page_$m"]!=''){
					if(! isset($options['blog']))
						$options['blog'] = array();
					
					$options['blog'][$m] = $_POST["blog_page_$m"];
				}
			}
		}
		
		// Tag page
		
		$options['tags'] = array();
		if(!empty($modifiable)){
			foreach($modifiable as $m){
				if(isset($_POST["tag_page_$m"]) && $_POST["tag_page_$m"]!=''){
					if(! isset($options['tags']))
						$options['tags'] = array();
					
					$options['tags'][$m] = $_POST["tag_page_$m"];
				}
			}
		}
		
		// Author page
		
		$options['authors'] = array();
		if(!empty($modifiable)){
			foreach($modifiable as $m){
				if(isset($_POST["authors_page_$m"]) && $_POST["authors_page_$m"]!=''){
					if(! isset($options['authors']))
						$options['authors'] = array();
					
					$options['authors'][$m] = $_POST["authors_page_$m"];
				}
			}
		}
		
		
		//Store defaults
		if($this->options !== FALSE)
			update_option($this->option_modifiable, $options);
		else{
			$options['modifiable'] = array();
			add_option($this->option_modifiable, $options);
		}
			
		$this->setMessage(__('The default sidebars have been updated successfully.','custom-sidebars'));
		
	}
	
	function storeReplacements( $post_id ){
		if(! current_user_can($this->cap_required))
			return;
		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
		// to do anything (Copied and pasted from wordpress add_metabox_tutorial)
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_id;
			
		global $action;
		
		//Get sure we are editing the post normaly, if we are bulk editing or quick editing, 
		//no sidebars data is recieved and the sidebars would be deleted.
		if($action != 'editpost')
			return $post_id;
			
		// make sure meta is added to the post, not a revision
		if ( $the_post = wp_is_post_revision($post_id) )
			$post_id = $the_post;
		
		$sidebars = $this->replaceable_sidebars;
		$data = array();
		if(!empty($sidebars)){
		foreach($sidebars as $s){
			if(isset($_POST["cs_replacement_$s"])){
				$it = $_POST["cs_replacement_$s"];
				if(!empty($it) && $it!='')
					$data[$s] = $it;
			}
		}
		}//endif sidebars
		$old_data = get_post_meta($post_id, $this->postmeta_key, TRUE);
		if($old_data == ''){
			if(!empty($data))
				add_post_meta($post_id, $this->postmeta_key, $data, TRUE);
		}
		else{
			if(!empty($data))
				update_post_meta($post_id, $this->postmeta_key, $data);
			else
				delete_post_meta($post_id, $this->postmeta_key);
		}
	}
	
	function storeSidebar(){
		$name = trim($_POST['sidebar_name']);
		$description = trim($_POST['sidebar_description']);
		if(empty($name) OR empty($description))
			$this->setError(__('You have to fill all the fields to create a new sidebar.','custom-sidebars'));
		else{
			$id = $this->sidebar_prefix . sanitize_title_with_dashes($name);
			$sidebars = get_option($this->option_name, FALSE);
			if($sidebars !== FALSE){
				$sidebars = $sidebars;
				if(! $this->getSidebar($id,$sidebars) ){
					//Create a new sidebar
					$sidebars[] = array(
						'name' => __( $name ,'custom-sidebars'),
						'id' => $id,
						'description' => __( $description ,'custom-sidebars'),
						'before_widget' => '', //all these fields are not needed, theme ones will be used
						'after_widget' => '',
						'before_title' => '',
						'after_title' => '',
						) ;
						
					
					//update option
					update_option( $this->option_name, $sidebars );
					
					/*
					//Let's store it also in the sidebar-widgets
					$sidebars2 = get_option('sidebars_widgets');
					if(array_search($id, array_keys($sidebars2))===FALSE){
						$sidebars2[$id] = array(); 
					}
					
					update_option('sidebars_widgets', $sidebars2); */
						
					$this->refreshSidebarsWidgets();
					
					
					$this->setMessage( __('The sidebar has been created successfully.','custom-sidebars'));
					
					
				}
				else
					$this->setError(__('There is already a sidebar registered with that name, please choose a different one.','custom-sidebars'));
			}
			else{
				$id = $this->sidebar_prefix . sanitize_title_with_dashes($name);
				$sidebars= array(array(
						'name' => __( $name ,'custom-sidebars'),
						'id' => $id,
						'description' => __( $description ,'custom-sidebars'),
						'before_widget' => '',
						'after_widget' => '',
						'before_title' => '',
						'after_title' => '',
						) );
				add_option($this->option_name, $sidebars);
				
			/*	//Let's store it also in the sidebar-widgets
				$sidebars2 = get_option('sidebars_widgets');
				if(array_search($id, array_keys($sidebars2))===FALSE){
					$sidebars2[$id] = array(); 
				}
				
				update_option('sidebars_widgets', $sidebars2); */
				
				$this->refreshSidebarsWidgets();
				
				$this->setMessage( __('The sidebar has been created successfully.','custom-sidebars'));					
			}
		}
	}
	
	function updateSidebar(){
		$id = trim($_POST['cs_id']);
		$name = trim($_POST['sidebar_name']);
		$description = trim($_POST['sidebar_description']);
		$before_widget = trim($_POST['cs_before_widget']);
		$after_widget = trim($_POST['cs_after_widget']);
		$before_title = trim($_POST['cs_before_title']);
		$after_title = trim($_POST['cs_after_title']);
		
		$sidebars = $this->getCustomSidebars();
		
		//Check the id		
		$url = parse_url($_POST['_wp_http_referer']);
		
		if(isset($url['query'])){
			parse_str($url['query'], $args);
			if($args['id'] != $id)
				return new WP_Error(__('The operation is not secure and it cannot be completed.','custom-sidebars'));
		}
		else
			return new WP_Error(__('The operation is not secure and it cannot be completed.','custom-sidebars'));
		
		
		$newsidebars = array();
		foreach($sidebars as $sb){
			if($sb['id'] != $id)
				$newsidebars[] = $sb;
			else
				$newsidebars[] = array(
						'name' => __( $name ,'custom-sidebars'),
						'id' => $id,
						'description' => __( $description ,'custom-sidebars'),
						'before_widget' =>  __( $before_widget ,'custom-sidebars'),
						'after_widget' => __( $after_widget ,'custom-sidebars'),
						'before_title' =>  __( $before_title ,'custom-sidebars'),
						'after_title' =>  __( $after_title ,'custom-sidebars'),
						) ;
		}
		
		//update option
		update_option( $this->option_name, $newsidebars );
		$this->refreshSidebarsWidgets();
		
		$this->setMessage( sprintf(__('The sidebar "%s" has been updated successfully.','custom-sidebars'), $id ));
	}
	
	function createCustomSidebar(){
		echo '<div class="widget-liquid-left" style="text-align:right"><a href="themes.php?page=customsidebars" class="button">' . __('Create a new sidebar','custom-sidebars') . '</a></div>';
	}
	
	function getSidebar($id, $sidebars){
		$sidebar = false;
		$nsidebars = sizeof($sidebars);
		$i = 0;
		while(! $sidebar && $i<$nsidebars){
			if($sidebars[$i]['id'] == $id)
				$sidebar = $sidebars[$i];
			$i++;
		}
		return $sidebar;
	}
	
	function message($echo = TRUE){
		$message = '';
		if(!empty($this->message))
			$message = '<div id="message" class="' . $this->message_class . '"><p><strong>' . $this->message . '</strong></p></div>';
		
		if($echo)
			echo $message;
		else
			return $message;		
	}
	
	function setMessage($text){
		$this->message = $text;
		$this->message_class = 'updated';
	}
	
	function setError($text){
		$this->message = $text;
		$this->message_class = 'error';
	}
	
	function getPostTypes(){
		$pt = get_post_types();
		$ptok = array();
		
		foreach($pt as $t){
			if(array_search($t, $this->ignore_post_types) === FALSE)
				$ptok[] = $t;
		}
		
		return $ptok; 
	}
	
	function getEmptyWidget(){
		return array(
			'name' => 'CS Empty Widget',
			'id' => 'csemptywidget',
			'callback' => array(new CustomSidebarsEmptyPlugin(), 'display_callback'),
			'params' => array(array('number' => 2)),
			'classname' => 'CustomSidebarsEmptyPlugin',
			'description' => 'CS dummy widget'
		);
	}
	
	function refreshSidebarsWidgets(){
		$widgetized_sidebars = get_option('sidebars_widgets');
		$delete_widgetized_sidebars = array();
		$cs_sidebars = get_option($this->option_name);
		
		foreach($widgetized_sidebars as $id => $bar){
			if(substr($id,0,3)=='cs-'){
				$found = FALSE;
				foreach($cs_sidebars as $csbar){
					if($csbar['id'] == $id)
						$found = TRUE;
				}
				if(! $found)
					$delete_widgetized_sidebars[] = $id;
			}
		}
		
		
		foreach($cs_sidebars as $cs){
			if(array_search($cs['id'], array_keys($widgetized_sidebars))===FALSE){
				$widgetized_sidebars[$cs['id']] = array(); 
			}
		}
		
		foreach($delete_widgetized_sidebars as $id){
			unset($widgetized_sidebars[$id]);
		}
		
		update_option('sidebars_widgets', $widgetized_sidebars);
		
	}
	
	function resetSidebars(){
		if(! current_user_can($this->cap_required) )
			return new WP_Error('cscantdelete', __('You do not have permission to delete sidebars','custom-sidebars'));
			
		if (! wp_verify_nonce($_REQUEST['reset-n'], 'custom-sidebars-delete') ) die('Security check stopped your request.'); 
		
		delete_option($this->option_modifiable);
		delete_option($this->option_name);
		
		$widgetized_sidebars = get_option('sidebars_widgets');	
		$delete_widgetized_sidebars = array();	
		foreach($widgetized_sidebars as $id => $bar){
			if(substr($id,0,3)=='cs-'){
				$found = FALSE;
				if(empty($cs_sidebars))
					$found = TRUE;
				else{
					foreach($cs_sidebars as $csbar){
						if($csbar['id'] == $id)
							$found = TRUE;
					}
				}
				if(! $found)
					$delete_widgetized_sidebars[] = $id;
			}
		}
		
		foreach($delete_widgetized_sidebars as $id){
			unset($widgetized_sidebars[$id]);
		}
		
		update_option('sidebars_widgets', $widgetized_sidebars);
		
		$this->setMessage( __('The Custom Sidebars data has been removed successfully,','custom-sidebars'));	
	}
	
	function getSortedCategories(){
		$unorderedcats = get_the_category();
		usort($unorderedcats, array($this, 'cmpCatLevel'));
		return $unorderedcats;
	}
	
	function cmpCatLevel($cat1, $cat2){
		$l1 = $this->getCategoryLevel($cat1->cat_ID);
		$l2 = $this->getCategoryLevel($cat2->cat_ID);
		if($l1 == $l2)
			return strcasecmp($cat1->name, $cat1->name);
		else 
			return $l1 < $l2 ? 1 : -1;
	}
	
	function getCategoryLevel($catid){
		if($catid == 0)
			return 0;
		
		$cat = &get_category($catid);
		return 1 + $this->getCategoryLevel($cat->category_parent);
	}
}
endif; //exists class


if(!isset($plugin_sidebars)){
	$plugin_sidebars = new CustomSidebars();	
	add_action( 'widgets_init', array($plugin_sidebars,'registerCustomSidebars') );
	add_action( 'widgets_admin_page', array($plugin_sidebars,'createCustomSidebar'));
	add_action( 'admin_menu', array($plugin_sidebars,'addSubMenus'));
	add_action( 'wp_head', array($plugin_sidebars,'replaceSidebars'));
	add_action('add_meta_boxes',  array($plugin_sidebars,'addMetaBox'));
	add_action( 'save_post', array($plugin_sidebars,'storeReplacements'));
	add_action( 'init', array($plugin_sidebars,'loadTextDomain'));
	
}

if(! class_exists('CustomSidebarsEmptyPlugin')){
class CustomSidebarsEmptyPlugin extends WP_Widget {
	function CustomSidebarsEmptyPlugin() {
		parent::WP_Widget(false, $name = 'CustomSidebarsEmptyPlugin');
	}
	function form($instance) {
		//Nothing, just a dummy plugin to display nothing
	}
	function update($new_instance, $old_instance) {
		//Nothing, just a dummy plugin to display nothing
	}
	function widget($args, $instance) {		
		echo '';
	}
} //end class
} //end if class exists