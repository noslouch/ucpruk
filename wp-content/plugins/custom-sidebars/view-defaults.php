<div class="themes-php">
<div class="wrap">

<?php include('view-tabs.php'); ?>

<?php //var_dump($categories);?>

<div id="defaultsidebarspage">

<form action="themes.php?page=customsidebars&p=defaults" method="post">

<div id="poststuff" class="defaultscontainer">
<h2><?php _e('Default sidebars for posts','custom-sidebars'); ?></h2>
<div id ="defaultsforposts" class="stuffbox">
<p><?php _e('These replacements will be applied to every single post that matches a certain post type or category.','custom-sidebars'); ?></p>
<p><?php _e('The sidebars by categories work in a hierarchycal way, if a post belongs to a parent and a child category it will show the child category sidebars if they are defined, otherwise it will show the parent ones. If no category sidebar for post are defined, the post will show the post post-type sidebar. If none of those sidebars are defined, the theme default sidebar is shown.','custom-sidebars'); ?></p>

<div class="cscolright">

<?php /***************************************
category_posts_{$id_category}_{$id_modifiable} : Posts by category
*********************************************/?>

<div class="defaultsSelector">
<h3 class="csh3title"><?php _e('By category','custom-sidebars'); ?></h3>
<?php if(!empty($categories)): foreach($categories as $c): if($c->cat_ID != 1):?>
	<div id="category-page-<?php echo $c->id; ?>" class="postbox closed" >
		<div class="handlediv" title="Haz clic para cambiar"><br /></div>
		<h3 class='hndle'><span><?php _e($c->name); ?></span></h3>
		
		<div class="inside">
		<?php if(!empty($modifiable)): foreach($modifiable as $m): $sb_name = $allsidebars[$m]['name'];?>
			<p><?php echo $sb_name; ?>: 
				<select name="category_posts_<?php echo $c->cat_ID; ?>_<?php echo $m;?>">
					<option value=""></option>
				<?php foreach($allsidebars as $key => $sb):?>
					<option value="<?php echo $key; ?>" <?php echo (isset($defaults['category_posts'][$c->cat_ID][$m]) && $defaults['category_posts'][$c->cat_ID][$m]==$key) ? 'selected="selected"' : ''; ?>>
						<?php echo $sb['name']; ?>
					</option>
				<?php endforeach;?>
				</select>
			</p>
		<?php endforeach;else:?>
			<p><?php _e('There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.','custom-sidebars'); ?></p>
		<?php endif;?>
		</div>
		
	</div>
	
	<?php endif;endforeach;else: ?>
		<p><?php _e('There are no categories available.','custom-sidebars'); ?></p>
	<?php endif;?>
</div>

</div>

<div class="cscolleft">

<?php /***************************************
type_posts_{$id_post_type}_{$id_modifiable} : Posts by post type
*********************************************/?>

<div class="defaultsSelector">
<h3 class="csh3title"><?php _e('By post type','custom-sidebars'); ?></h3>
<div id="posttypes-default" class="meta-box-sortables">
	<?php foreach($post_types as $pt): $post_type_object = get_post_type_object($pt);?>
	<div id="pt-<?php echo $pt; ?>" class="postbox closed" >
		<div class="handlediv" title="Haz clic para cambiar"><br /></div>
		<h3 class='hndle'><span><?php _e($post_type_object->label); ?></span></h3>
		
		<div class="inside">
		<?php if(!empty($modifiable)): foreach($modifiable as $m): $sb_name = $allsidebars[$m]['name'];?>
			<p><?php echo $sb_name; ?>: 
				<select name="type_posts_<?php echo $pt;?>_<?php echo $m;?>">
					<option value=""></option>
				<?php foreach($allsidebars as $key => $sb):?>
					<option value="<?php echo $key; ?>" <?php echo (isset($defaults['post_type_posts'][$pt][$m]) && $defaults['post_type_posts'][$pt][$m]==$key) ? 'selected="selected"' : ''; ?>>
						<?php echo $sb['name']; ?>
					</option>
				<?php endforeach;?>
				</select>
			</p>
		<?php endforeach;else:?>
			<p><?php _e('There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.','custom-sidebars'); ?></p>
		<?php endif;?>
		</div>
		
	</div>
	
	<?php endforeach; ?>
</div> 
</div>
</div>

<p class="submit"><input type="submit" class="button-primary" name="update-defaults-posts" value="<?php _e('Save Changes','custom-sidebars'); ?>" /></p>
</div>


<h2><?php _e('Default sidebars for pages','custom-sidebars'); ?></h2>
<div id ="defaultsforpages" class="stuffbox">
<p><?php _e('You can define specific sidebars for the different Wordpress pages. Sidebars for lists of posts pages work in the same hierarchycal way than the one for single posts.','custom-sidebars'); ?></p>

<div class="cscolright">

<?php /***************************************
category_page_{$id_category}_{$id_modifiable} : Category list page
*********************************************/?>

<div class="defaultsSelector">
  
<h3 class="csh3title"><?php _e('Category posts list','custom-sidebars'); ?></h3>
<?php if(!empty($categories)): foreach($categories as $c): if($c->cat_ID != 1):?>
	<div id="category-page-<?php echo $c->id; ?>" class="postbox closed" >
		<div class="handlediv" title="Haz clic para cambiar"><br /></div>
		<h3 class='hndle'><span><?php _e($c->name); ?></span></h3>
		
		<div class="inside">
		<?php if(!empty($modifiable)): foreach($modifiable as $m): $sb_name = $allsidebars[$m]['name'];?>
			<p><?php echo $sb_name; ?>: 
				<select name="category_page_<?php echo $c->cat_ID; ?>_<?php echo $m;?>">
					<option value=""></option>
				<?php foreach($allsidebars as $key => $sb):?>
					<option value="<?php echo $key; ?>" <?php echo (isset($defaults['category_pages'][$c->cat_ID][$m]) && $defaults['category_pages'][$c->cat_ID][$m]==$key) ? 'selected="selected"' : ''; ?>>
						<?php echo $sb['name']; ?>
					</option>
				<?php endforeach;?>
				</select>
			</p>
		<?php endforeach;else:?>
			<p><?php _e('There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.','custom-sidebars'); ?></p>
		<?php endif;?>
		</div>
		
	</div>
	
	<?php endif;endforeach;else: ?>
		<p><?php _e('There are no categories available.','custom-sidebars'); ?></p>
	<?php endif;?>
</div>

<?php /***************************************
tag_page_{$id_modifiable} : Post by tag list page
*********************************************/?>

<div class="defaultsSelector">

<h3 class="csh3title"><?php _e('Tag pages','custom-sidebars'); ?></h3>
<?php if(!empty($modifiable)): foreach($modifiable as $m): $sb_name = $allsidebars[$m]['name'];?>
			<p><?php echo $sb_name; ?>: 
				<select name="tag_page_<?php echo $m;?>">
					<option value=""></option>
				<?php foreach($allsidebars as $key => $sb):?>
					<option value="<?php echo $key; ?>" <?php echo (isset($defaults['tags'][$m]) && $defaults['tags'][$m]==$key) ? 'selected="selected"' : ''; ?>>
						<?php echo $sb['name']; ?>
					</option>
				<?php endforeach;?>
				</select>
			</p>
		<?php endforeach;else:?>
			<p><?php _e('There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.','custom-sidebars'); ?></p>
		<?php endif;?>
</div>

</div>

<div class="cscolleft">

<?php /***************************************
type_page_{$id_post_type}_{$id_modifiable} : Posts by post type list page
*********************************************/?>

<div class="defaultsSelector">

<h3 class="csh3title"><?php _e('Post-type posts list','custom-sidebars'); ?></h3>
<div id="posttypelist-default" class="meta-box-sortables">
	<?php foreach($post_types as $pt): $post_type_object = get_post_type_object($pt);?>
	<div id="pt-<?php echo $pt; ?>" class="postbox closed" >
		<div class="handlediv" title="Haz clic para cambiar"><br /></div>
		<h3 class='hndle'><span><?php _e($post_type_object->label); ?></span></h3>
		
		<div class="inside">
		<?php if(!empty($modifiable)): foreach($modifiable as $m): $sb_name = $allsidebars[$m]['name'];?>
			<p><?php echo $sb_name; ?>: 
				<select name="type_page_<?php echo $pt;?>_<?php echo $m;?>">
					<option value=""></option>
				<?php foreach($allsidebars as $key => $sb):?>
					<option value="<?php echo $key; ?>" <?php echo (isset($defaults['post_type_pages'][$pt][$m]) && $defaults['post_type_pages'][$pt][$m]==$key) ? 'selected="selected"' : ''; ?>>
						<?php echo $sb['name']; ?>
					</option>
				<?php endforeach;?>
				</select>
			</p>
		<?php endforeach;else:?>
			<p><?php _e('There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.','custom-sidebars'); ?></p>
		<?php endif;?>
		</div>
		
	</div>
	
	<?php endforeach; ?>
</div>

</div>

<h3 class="csh3title"><?php _e('Blog page','custom-sidebars'); ?></h3>

<?php /***************************************
blog_page_{$id_modifiable} : Main blog page
*********************************************/?>

<div class="defaultsSelector">

<?php if(!empty($modifiable)): foreach($modifiable as $m): $sb_name = $allsidebars[$m]['name'];?>
			<p><?php echo $sb_name; ?>: 
				<select name="blog_page_<?php echo $m;?>">
					<option value=""></option>
				<?php foreach($allsidebars as $key => $sb):?>
					<option value="<?php echo $key; ?>" <?php echo (isset($defaults['blog'][$m]) && $defaults['blog'][$m]==$key) ? 'selected="selected"' : ''; ?>>
						<?php echo $sb['name']; ?>
					</option>
				<?php endforeach;?>
				</select>
			</p>
		<?php endforeach;else:?>
			<p><?php _e('There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.','custom-sidebars'); ?></p>
		<?php endif;?>
		
</div>


<?php /***************************************
authors_page_{$id_modifiable} : Any author page
*********************************************/?>

<div class="defaultsSelector">

<h3 class="csh3title"><?php _e('Author pages','custom-sidebars'); ?></h3>
<?php if(!empty($modifiable)): foreach($modifiable as $m): $sb_name = $allsidebars[$m]['name'];?>
			<p><?php echo $sb_name; ?>: 
				<select name="authors_page_<?php echo $m;?>">
					<option value=""></option>
				<?php foreach($allsidebars as $key => $sb):?>
					<option value="<?php echo $key; ?>" <?php echo (isset($defaults['authors'][$m]) && $defaults['authors'][$m]==$key) ? 'selected="selected"' : ''; ?>>
						<?php echo $sb['name']; ?>
					</option>
				<?php endforeach;?>
				</select>
			</p>
		<?php endforeach;else:?>
			<p><?php _e('There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.','custom-sidebars'); ?></p>
		<?php endif;?>

</div>

</div>

<p class="submit"><input type="submit" class="button-primary" name="update-defaults-pages" value="<?php _e('Save Changes','custom-sidebars'); ?>" /></p>
</div>

</div>

</form>

</div>

<?php include('view-footer.php'); ?>

</div>
</div>