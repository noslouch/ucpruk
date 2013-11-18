/*------------------------------------------------------------------------------
The relation fields require a lot of Javascript to handle the Ajax functionality
that goes into the Thickbox.

Note that the cctm_upload function overrides WP's send_to_editor() function!!!

The 'html' bit has something like this when you click "Insert into Post" 
(but NOT if you click "Save all Changes"):

In WP 3.2:
<a href="http://cctm:8888/sub/?attachment_id=603" rel="attachment wp-att-603"><img src="http://cctm:8888/sub/wp-content/uploads/2011/11/Photo-on-2011-07-14-at-23.01-300x225.jpg" alt="" title="Photo on 2011-07-14 at 23.01" width="300" height="225" class="alignnone size-medium wp-image-603" /></a>

In WP 3.3, they changed it... the Media Browser now returns something like this:
<a href="http://cctm:8888/sub/wp-content/uploads/2011/11/IMG_0378.jpg"><img src="http://cctm:8888/sub/wp-content/uploads/2011/11/IMG_0378.jpg" alt="" title="LA Sunset" class="alignnone size-full wp-image-773" /></a>

Or simply:
<a href='http://cctm:8888/sub/wp-content/uploads/2011/11/Blank-W9.pdf'>Blank-W9</a>

When finished, the function redefines the send_to_editor() function back to what
it was before (i.e. I copied the definition from wp-admin/js/media-upload.dev.js
and I feed that back into the DOM).
------------------------------------------------------------------------------*/

// Global storage for the fieldname we're uploading a file for.
var cctm_fieldname;
var append_or_replace = 'append';

//------------------------------------------------------------------------------
//! FUNCTIONS
//------------------------------------------------------------------------------

/*------------------------------------------------------------------------------
This pops WP's media uploader
http://www.webmaster-source.com/2010/01/08/using-the-wordpress-uploader-in-your-plugin-or-theme/

TODO: Redo this to use our own uploader that doesn't suck...
Apparently, nobody at WP every considered the possiblity that the uploaders
would ever be used for anything other than to select a featured post.  So we have
to override the send_to_editor() function so we can alter the behavior so it 
inserts into our custom field instead of to the destinations hard-coded by WP.

In this function, I restore the the original WP function.
------------------------------------------------------------------------------*/
function cctm_upload(fieldname, upload_type) {
	// Override the send_to_editor() function from wp-admin/js/media-upload.js
	window.send_to_editor = function(html) {
	
		// alert(html); // see what on earth WP is sending back to the post...
		var attachment_guid; 
		
		var matches = html.match(/href=['|"](.*?)['|"]/);
		if (matches != null) {
    		attachment_guid = matches[1];
    	}
    	
		var data = {
		        "action" : 'get_selected_posts',
		        "fieldname" : cctm_fieldname, // Read from global scope
		        "guid": attachment_guid,
		        "get_selected_posts_nonce" : cctm.ajax_nonce
		    };
	
		jQuery.post(
		    cctm.ajax_url,
		    data,
		    function( response ) {
		    	// Write the response to the div
		    	if (append_or_replace == 'append') {
			    	jQuery('#cctm_instance_wrapper_'+cctm_fieldname).append(response);
		    	}
		    	else {
		    		jQuery('#cctm_instance_wrapper_'+cctm_fieldname).html(response);
		    	}
				
		    }
		);
		
		tb_remove();
		
		// Restore the function back to normal (copied from ./wp-admin/js/media-upload.dev.js)
		window.send_to_editor = function(h) {
			        var ed, mce = typeof(tinymce) != 'undefined', qt = typeof(QTags) != 'undefined';
			
			        if ( !wpActiveEditor ) {
			                if ( mce && tinymce.activeEditor ) {
			                        ed = tinymce.activeEditor;
			                        wpActiveEditor = ed.id;
			                } else if ( !qt ) {
			                        return false;
			                }
			        } else if ( mce ) {
			                if ( tinymce.activeEditor && (tinymce.activeEditor.id == 'mce_fullscreen' || tinymce.activeEditor.id == 'wp_mce_fullscreen') )
			                        ed = tinymce.activeEditor;
			                else
			                        ed = tinymce.get(wpActiveEditor);
			        }
			
			        if ( ed && !ed.isHidden() ) {
			                // restore caret position on IE
			                if ( tinymce.isIE && ed.windowManager.insertimagebookmark )
			                        ed.selection.moveToBookmark(ed.windowManager.insertimagebookmark);
			
			                if ( h.indexOf('[caption') === 0 ) {
			                        if ( ed.plugins.wpeditimage )
			                                h = ed.plugins.wpeditimage._do_shcode(h);
			                } else if ( h.indexOf('[gallery') === 0 ) {
			                        if ( ed.plugins.wpgallery )
			                                h = ed.plugins.wpgallery._do_gallery(h);
			                } else if ( h.indexOf('[embed') === 0 ) {
			                        if ( ed.plugins.wordpress )
			                                h = ed.plugins.wordpress._setEmbed(h);
			                }
			
			                ed.execCommand('mceInsertContent', false, h);
			        } else if ( qt ) {
			                QTags.insertContent(h);
			        } else {
			                document.getElementById(wpActiveEditor).value += h;
			        }
			
			        try{tb_remove();}catch(e){};
			}
		// end of function restoration
	}

	cctm_fieldname = fieldname; // pass this to global scope
	append_or_replace = upload_type; // pass this to global scope
	
	tb_show('', 'media-upload.php?post_id=0&amp;type=file&amp;TB_iframe=true');
	return false;
}

/*------------------------------------------------------------------------------
Used for flipping through pages of thickbox'd search results.
------------------------------------------------------------------------------*/
function change_page(page_number) {

	// It's easier to read it from a hidden field than it is to pass it to this function
	var fieldname = jQuery('#fieldname').val();

	jQuery('#page_number').val(page_number); // store the value so it can be serialized

	var data = {
	        "action" : 'get_posts',
	        "fieldname" : fieldname,
	        "get_posts_nonce" : cctm.ajax_nonce
	    };
	    
	data.search_parameters = jQuery('#select_posts_form').serialize();

	jQuery.post(
	    cctm.ajax_url,
	    data,
	    function( response ) {
	    	// Write the response to the div
			jQuery('#cctm_thickbox_content').html(response);
	    }
	);
	return false;
}



/*------------------------------------------------------------------------------
Remove the associated image, media, or relation item.  This means the hidden 
field that stores the actual value must be set to null and the preview hmtl
must be cleared.
@param 	string	target_id is the hidden field id that needs to be nulled
@param	string	target_html is the id of the div whose html needs to be cleared
------------------------------------------------------------------------------*/
function remove_relation( target_id, target_html ) {
	jQuery('#'+target_id).val('');
	jQuery('#'+target_html).html('');	
}


/*------------------------------------------------------------------------------
Add the selected posts to the parent post and close the thickbox.
------------------------------------------------------------------------------*/
function save_and_close() {
	send_selected_posts_to_wp();
	tb_remove();
	return false;	
}

/*------------------------------------------------------------------------------
Shows a search form from the "edit custom field" definition.
We send along the fieldname and fieldtype to allow for customizations.
On new definitions, the behavior defaults to the fieldtype because we don't 
yet have a fieldname.

@param	fieldname	string	name of the field
@param	fieldtyp	string	type of field (e.g. relation, image)
------------------------------------------------------------------------------*/
function search_form_display(fieldname,fieldtype) {
	var search_parameters = jQuery('#search_parameters').val();
	//alert(search_parameters);
	var data = {
	        "action" : 'get_search_form',
	        "fieldname" : fieldname,
	        "fieldtype" : fieldtype,
	        "search_parameters" : search_parameters,
	        "get_search_form_nonce" : cctm.ajax_nonce
	    };
	    
	// data.search_parameters = jQuery('#select_posts_form').serialize();
	
	jQuery.post(
	    cctm.ajax_url,
	    data,
	    function( response ) {
	    	// Write the response to the div
			jQuery('#cctm_thickbox').html(response);	

			var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
			W = W - 80;
			H = H - 124;
			// then thickbox the div
			tb_show('', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=cctm_thickbox' );			


	    }
	);
}

/*------------------------------------------------------------------------------
Save the Search Parameters Form to a hidden field in the field definition page
This sends the search a parameters back to WP (i.e. back to the custom field
definition).

See also search_form_display(), which popped the search_form to begin with.

@param	string	form_id: identifies which form we will serialize.
------------------------------------------------------------------------------*/
function search_parameters_save(form_id) {
	var search_parameters = jQuery('#'+form_id).serialize();
	//alert(search_parameters);
	jQuery('#search_parameters').val(search_parameters);
	tb_remove();
	alert('Search parameters saved.');
}



/*------------------------------------------------------------------------------
Adds posts checked in the thickbox to the parent post. (used by relation fields
with "is-repeatable" checked).  This does the magic thusly:

	1. Get all the checked posts and read their post_id's
	2. Hide that post from the current search form (so you can't select it twice)
	3. Generate html for the preview of these post_id's (via ajax)
	4. Append (not overwrite) that data to the post/page

Similar to the send_single_post_to_wp() function.
------------------------------------------------------------------------------*/
function send_selected_posts_to_wp() {
	// It's easier to read it from a hidden field than it is to pass it to this function
	var fieldname = jQuery('#fieldname').val();

	var post_ids = new Array();
	jQuery('#cctm_thickbox_content input:checked').each(function() {
		var post_id = jQuery(this).val();
		post_ids.push(post_id);
		// Remove the selection from the visible form
		jQuery('#cctm_tr_multi_select_'+post_id).remove();
	});

	var data = {
	        "action" : 'get_selected_posts',
	        "fieldname" : fieldname,
	        "post_id": post_ids,
	        "get_selected_posts_nonce" : cctm.ajax_nonce
	    };

	jQuery.post(
	    cctm.ajax_url,
	    data,
	    function( response ) {
	    	//alert('cctm_instance_wrapper_'+fieldname);
	    	// Write the response to the div
			jQuery('#cctm_instance_wrapper_'+fieldname).append(response);
			
	    }
	);
	
	return false;
}


/*------------------------------------------------------------------------------
Inside the thickbox, select a post and send it back to WordPress.
Similar to the send_selected_posts_to_wp() function.
@param	integer	post_id is the ID of the attachment that has been selected
------------------------------------------------------------------------------*/
function send_single_post_to_wp( post_id ) {
	// It's easier to read it from a hidden field than it is to pass it to this function
	var fieldname = jQuery('#fieldname').val();
	//console.log('here...' + fieldname);
	var data = {
	        "action" : 'get_selected_posts',
	        "fieldname" : fieldname,
	        "post_id": post_id,
	        "get_selected_posts_nonce" : cctm.ajax_nonce
	    };

	jQuery.post(
	    cctm.ajax_url,
	    data,
	    function( response ) {
	    	//alert('cctm_instance_wrapper_'+fieldname);
	    	// Write the response to the div
			jQuery('#cctm_instance_wrapper_'+fieldname).html(response);
			
	    }
	);

	tb_remove();
	jQuery('#default_value').val(post_id); //<-- used when setting default values in field defs
	return false;
}

/*------------------------------------------------------------------------------
Refining a search
Similar to thickbox_reset_search(), but this appends to the previous search params.
E.g. type a search term, press the "Filter" button.  Any current values on the 
search form are read and preserved.

@param	string	form_id: the form which contains the additional search parameters
------------------------------------------------------------------------------*/
function thickbox_refine_search() {
	// It's easier to read it from a hidden field than it is to pass it to this function
	var fieldname = jQuery('#fieldname').val();
	
	jQuery('#page_number').val('0');
	
	var data = 
		{
	        "action" : 'get_posts',
	        "fieldname" : fieldname,
	        "get_posts_nonce" : cctm.ajax_nonce
	    };
	// This is how we maintain our existing parameters.
	data.search_parameters = jQuery('#select_posts_form').serialize();

	jQuery.post(
	    cctm.ajax_url,
	    data,
	    function( response ) {
	    	// Write the response to the div
			jQuery('#cctm_thickbox_content').html(response);
			
	    }
	);	
	
}


/*------------------------------------------------------------------------------
Reset search -- back to the original results
@param	string	form_id: the form which contains the additional search parameters
------------------------------------------------------------------------------*/
function thickbox_reset_search() {
	// It's easier to read it from a hidden field than it is to pass it to this function
	var fieldname = jQuery('#fieldname').val();
	
	jQuery('#page_number').val('0');
	
	var data = 
		{
	        "action" : 'get_posts',
	        "fieldname" : fieldname,
	        "get_posts_nonce" : cctm.ajax_nonce
	    };
	data.search_parameters = '';
	
	jQuery.post(
	    cctm.ajax_url,
	    data,
	    function( response ) {
	    	// Write the response to the div
			jQuery('#cctm_thickbox_content').html(response);
			
	    }
	);
}

/*------------------------------------------------------------------------------
This is the generic CCTM thickbox showing selectable search results: 
i.e. the "Post Selector".  It allows user to select one or many posts for use 
in a relation field (image, media).

If omit_existing_values is passed as true, then the post-selector pop-up
will not display any posts that have already been selected. This creates 
a behavior similar to those web forms where you can move items from one
list to another.  We use it for the multi-select fields where we don't 
want the user adding the same post over and over again.

@param	string css_field_id	field id, e.g. cctm_pics
@param	boolean omit_existing_values
------------------------------------------------------------------------------*/
function thickbox_results(css_field_id, omit_existing_values) {

	var existing_values = new Array();
	if (omit_existing_values) {
		jQuery('#cctm_instance_wrapper_'+css_field_id +' :input').each(function() {
			existing_values.push(jQuery(this).val());
		});
	}
	
	// Optional fieldtype: present in the field definitions
	var fieldtype = jQuery('#fieldtype').val();
	
	jQuery.post(
	    cctm.ajax_url,
	    {
	        "action" : 'get_posts',
	        "fieldname" : css_field_id,
	        "fieldtype" : fieldtype,
	        "exclude" : existing_values,
	        "wrap_thickbox": 1,
	        "get_posts_nonce" : cctm.ajax_nonce
	    },
	    function( response ) {
	    	// Write the response to the div
			jQuery('#target_'+css_field_id).html(response);
			
			var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
			W = W - 80;
			H = H - 84;
			// then thickbox the div
			tb_show('', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=target_'+css_field_id );			
	    }
	);	
}

/*------------------------------------------------------------------------------
Fired when a column header is clicked.  
@param	string	sort_column the column we want to sort by.
------------------------------------------------------------------------------*/
function thickbox_sort_results(sort_column) {
		// It's easier to read it from a hidden field than it is to pass it to this function
	var fieldname = jQuery('#fieldname').val();
	var order = jQuery('#order').val();
	var orderby = jQuery('#orderby').val(); 
	
	// Toggle order if we're already sortying by the 'orderby' column
	if (orderby == sort_column){
		if (order == 'DESC') {
			jQuery('#order').val('ASC');
		}
		else {
			jQuery('#order').val('DESC');
		}
	}
	
	jQuery('#orderby').val(sort_column);
	jQuery('#page_number').val('0'); // go back to first page when we resort
	var data = 
		{
	        "action" : 'get_posts',
	        "fieldname" : fieldname,
	        "get_posts_nonce" : cctm.ajax_nonce
	    };

	data.search_parameters = jQuery('#select_posts_form').serialize();
	
	jQuery.post(
	    cctm.ajax_url,
	    data,
	    function( response ) {
	    	// Write the response to the div
			jQuery('#cctm_thickbox_content').html(response);
			
	    }
	);	
}