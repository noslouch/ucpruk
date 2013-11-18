<style>
[+css+]
</style>
<p>Dynamically list posts according to the criteria below. <a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/SearchParameters"><img src="'.CCTM_URL .'/images/question-mark.gif" width="16" height="16" /></a></p>
<form id="search_parameters_form" class="[+form_name+]">
	[+content+]


	<div id="filter_on_field_target">
	</div>
	
	<select id="field_selector">
		<option value="post_title">Post Title</option>
		<option value="post_author">Author ID</option>
		[+custom_fields+]
	</select>
	<span class="button" onclick="javascript:generate_field_filter('field_selector','filter_on_field_target');">Add Filter</span>
	<br/>
	<hr/>
	<br/>
	<span class="button" onclick="javascript:generate_shortcode('search_parameters_form');">Generate Shortcode</span>
	<span class="button" onclick="javascript:tb_remove();">Cancel</span>
</form>