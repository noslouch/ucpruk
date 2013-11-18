/*------------------------------------------------------------------------------
This is called by the TinyMCE button click.  Make sure this function name 
matched the one in editor_plugin.js!
------------------------------------------------------------------------------*/
function show_summarize_posts() {
	// Make us a place for the thickbox
	jQuery('body').append('<div id="summarize_posts_thickbox"></div>');

	// Prepare the AJAX query
	var data = {
	        "action" : 'list_snippets',
	        "list_snippets_nonce" : summarize_posts.ajax_nonce
	    };
	    
	
	jQuery.post(
	    summarize_posts.ajax_url,
	    data,
	    function( response ) {
	    	// Write the response to the div
			jQuery('#summarize_posts_thickbox').html(response);

			var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
			W = W - 80;
			H = H - 84;
			// then thickbox the div
			tb_show('', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=summarize_posts_thickbox' );			
	    }
	);
	
}


/*------------------------------------------------------------------------------
Pastes the shortcode back into WP.
Copied from wp-admin/media-upload.js send_to_editor() function -- I couldn't 
find where that JS is queued up, so I just copied this one function.
------------------------------------------------------------------------------*/
function insert_shortcode(h) {
	var ed;

	if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.activeEditor ) && !ed.isHidden() ) {
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

	} else if ( typeof edInsertContent == 'function' ) {
		edInsertContent(edCanvas, h);
	} else {
		jQuery( edCanvas ).val( jQuery( edCanvas ).val() + h );
	}

	tb_remove();
}


/*------------------------------------------------------------------------------
When the summarize posts thickbox submits, this is what takes the submission 
and converts it into a shortcode.
------------------------------------------------------------------------------*/
function generate_shortcode() {
	alert('here.....');
}