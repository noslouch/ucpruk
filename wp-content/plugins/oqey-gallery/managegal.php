<?php
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'managegal.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!');
global $wpdb, $current_user;
   $oqey_galls = $wpdb->prefix . "oqey_gallery";
   $oqey_images = $wpdb->prefix . "oqey_images";
   $oqey_music = $wpdb->prefix . "oqey_music";
   $oqey_music_rel = $wpdb->prefix . "oqey_music_rel";

$oqeyImagesRoot = get_option('siteurl').'/wp-content/oqey_gallery/galleries/'.oqey_getBlogFolder($wpdb->blogid);
?>

<form>
<input type="hidden" id="trget" value=""/>
<input type="hidden" id="Qtw" value=""/>
<input type="hidden" id="Qth" value=""/>
<input type="hidden" id="Qttw" value=""/>
<input type="hidden" id="Qtth" value=""/>
<input type="hidden" id="imgid" value=""/>
<input type="hidden" id="coef" value=""/>
<input type="hidden" id="multiplicator" value=""/>
</form>

<script type="text/javascript">
function refreshPage(){
    var id = jQuery('#currentGalID').val(); 
    getGalleryDetails(id); 
}

function clearUp(){ setTimeout(function () {  jQuery('#messages').fadeOut(function(){ jQuery("#messages").html("&nbsp;"); }); }, 2000); }

function deleteImage(id){  //move to trash single image
jQuery.post( ajaxurl, { action:"oQeyImageToTrash", delimgid: id },
            function(data){
			jQuery("#messages").hide().html('<p class="updated fade"><?php _e('Image was moved to trash.', 'oqey-gallery'); ?>&nbsp;<a href="#undo" id="undoimage"><?php _e('undo', 'oqey-gallery'); ?><\/a> &nbsp;<\/p>').fadeIn("slow");
            jQuery('#img_li_'+id).hide(1000);
            
			jQuery("#undoimage").click(function(){            
			jQuery.post( ajaxurl, { action:"oQeyImageFromTrash", undoimgid: id },
              function(r){
                
                jQuery("#messages").hide().html('<p class="updated fade">' + r + '<\/p>').fadeIn("slow");
			    jQuery('#img_li_'+id).show(1000);
			  });								
			});	
            	
      });
}


/*DELETE VIDEO*/
function deleteVideo(id){  //move to trash single image
jQuery.post( ajaxurl, { action:"oQeyVideoFromGalleryDelete", id: id },
            function(data){
			jQuery("#messages").hide().html('<p class="updated fade"> <?php _e('Video file was removed from this gallery.', 'oqey-gallery'); ?> &nbsp;<\/p>').fadeIn("slow");
            jQuery('#img_li_'+id).hide(1000);
      });
}

function showImage(id, src){  //alert(decodeURIComponent(folder));  

        var i = decodeURIComponent(src);
		var img = '<img src="' + i + '" class="previewimg" style="max-height:600px; max-width:900px;" />'; 
		var $dialog = jQuery('<div id="popupdisplay"><\/div>').html(img).dialog({
			width: 900,
			height: 635,
			maxWidth: 900,
			maxHeight: 635,
			resizable: false,
			autoOpen: false,
			title: 'Preview image',
			modal: true,
			draggable: false		
		});			
		$dialog.dialog('open'); 
}

/*begin cropper*/
var xQ, yQ, wQ, hQ, Qtw, Qth, Qttw, Qtth, coef;
var status = 'start';

function showCropImage(imgid, tw, th, ttw, tth, src, src2, multiplicator){  //alert(decodeURIComponent(folder));  

        var d = new Date();
        
        var Qtw = tw; 
        var Qth = th;
        var Qttw = ttw; 
        var Qtth = tth;
        var coef = Qtw/Qth;
        
        jQuery("#Qtw").attr("value", Qtw); 
        jQuery("#Qth").attr("value", Qth); 
        jQuery("#Qttw").attr("value", ttw); 
        jQuery("#Qtth").attr("value", tth); 
        jQuery("#imgid").attr("value", imgid);
        jQuery("#coef").attr("value", coef);
        jQuery("#multiplicator").attr("value", multiplicator);
                
        status = 'start';
        
        var i = decodeURIComponent(src);
        var i2 = decodeURIComponent(src2);
        var id = Math.floor(Math.random()*7777777);

		var img = '<img src="' + i + '" height="'+Qth+'" width="'+Qtw+'" id="cropbox' + id + '" style="background-color: transparent;" \/>'; 
        var img2 = '<img src="' + i + '" id="preview' + id + '" style="display:none;" \/>'; 
        var img3 = '<img src="' + i2 + '?' + d.getTime() + '" id="actual' + id + '" \/>';  
        var bb = '<input type="button" name="update" value="update" onclick="saveNewThumb()" class="button-secondary" style="float:left; margin-left:4px;"/><p id="oqeyr' + id + '" style="float:left; margin-left:4px; width:100px;"><\/p>';       
        var x = jQuery("#trget").attr("value", id);   
        var c = '<table width="660" height="320" border="0"><tr><td rowspan="2" width="500" align="center" valign="top" >' + img + '<\/td><td width="150" height="150" align="center" valign="top"><div style="width:150px;height:100px;overflow:hidden;">' + img2 + img3 + '<\/div><\/td><\/tr><tr><td align="left" valign="top">'+ bb + '<\/td><\/tr><\/table>';
        
        
		var $dialog = jQuery('<div><\/div>').html(c).dialog({
			width: 670,
			height: 360,
			maxWidth: 700,
			maxHeight: 400,
			resizable: false,
			autoOpen: false,
			title: 'Crop image',
			modal: true,
			draggable: false		
		});			
		$dialog.dialog('open'); 
		/*
		$("#dialog-selector" ).dialog({
          open: function(event, ui) { $('#cropbox').Jcrop(); }
        }); */

        setTimeout(function(){ 
        
		 jQuery('#cropbox' + id).Jcrop({
					onChange: showPreview,
					onSelect: showPreview,
					aspectRatio: 1.5
				}); 
        }, 3000 ); 	               
}

   function showPreview(coords){
			 
 		     idl = jQuery("#trget").val();
             Qttw = 150; //jQuery("#Qttw").val();
             Qtth = 100;//jQuery("#Qtth").val();
             Qtw = jQuery("#Qtw").val();
             Qth = jQuery("#Qth").val();
             imgid = jQuery("#imgid").val();
             coef = jQuery("#coef").val();
             
        if (status != 'edit') {
			jQuery('#actual'+idl).hide();
			jQuery('#preview'+idl).show();
			status = 'edit';	
		}
             
				if (parseInt(coords.w) > 0){
				    
					var rx = (Qttw / coords.w);
					var ry = (Qtth / coords.h);

					jQuery('#preview'+idl).css({
						width: Math.round(rx * Qtw) + 'px',
						height: Math.round(ry * Qth) + 'px',
						marginLeft: '-' + Math.round(rx * coords.x) + 'px',
						marginTop: '-' + Math.round(ry * coords.y) + 'px'
					});
                    
                    xQ = coords.x;
		            yQ = coords.y;
		            wQ = coords.w;
		            hQ = coords.h;
                }
}

function saveNewThumb(){
    
    	if ( (wQ == 0) || (hQ == 0) || (wQ == undefined) || (hQ == undefined) ) {
			alert("<?php _e('Select the area for the new thumbnail', 'oqey-gallery'); ?>");
			return false;			
		}
        
        var multiplicator = jQuery("#multiplicator").val();
		
		jQuery.ajax({
		  url: ajaxurl,
		  type : "POST",
		  data:  {x: xQ, y: yQ, w: wQ, h: hQ, Qttw: Qttw, Qtth:Qtth, action: 'createoQeyNewThumb', imgid: imgid, coef:coef, multiplicator: multiplicator },
		  cache: false,
		  success: function(data){
				var d = new Date();
				newUrl = jQuery("#img_li_"+imgid + " .img_thumbs").attr("src") + "?" + d.getTime();
				jQuery("#img_li_"+imgid + " .img_thumbs").attr("src" , newUrl);
					
					jQuery('#oqeyr' + idl).html("<?php _e('Updated', 'oqey-gallery') ?>");
					jQuery('#oqeyr' + idl).css({'display':'block'});
					setTimeout(function(){ jQuery('#oqeyr' + idl).fadeOut('slow'); }, 1500);
			},
		  error: function() {
		  		    jQuery('#oqeyr' + idl).html("<?php _e('Error', 'oqey-gallery') ?>");
					jQuery('#oqeyr' + idl).css({'display':'block'});
					setTimeout(function(){ jQuery('#oqeyr' + idl).fadeOut('slow'); }, 1500);
		    }
		});
}
/*end cropper*/


function updateThumbs(){ //get the last uploaded image
//under development
}

function preloadImages(img){
var images=img.split(",");
jQuery.loadImages(images,function(){});
}

function ajaxUploader(id){
		var btnUpload=jQuery('#upload');
		var status=jQuery('#status');
		new AjaxUpload(btnUpload, {
			action: '<?php echo oQeyPluginUrl(); ?>/bcupload.php?&id=' + id,
			name: 'Filedata',
			onSubmit: function(file, ext){
				 if (! (ext && /^(jpg|png|jpeg)$/.test(ext))){ 
                    // extension is not allowed 
					status.text('<?php _e('Only JPG and PNG files are allowed', 'oqey-gallery'); ?>');
					return false;
				}
				status.text('Uploading...');
			},
			onComplete: function(file, response){
				status.text('');
				status.text(response);
				refreshPage();
		}
	});
}

function viewAll(){ 
  if (jQuery("#gallscontent > li").is(":hidden") ) {
      jQuery("#gallscontent > li").fadeIn(2000);
  }  
  jQuery(".allgalls").html("&nbsp;");
}

function getGalleryDetails(id){	
	    
            jQuery('#gallerytable').fadeOut("slow");
            jQuery("#createfrompostbox").fadeOut("slow");
			jQuery('#galleryarea').fadeIn("slow");
			jQuery('#currentGalID').attr('value', id);			
        
        jQuery.post( ajaxurl, { action : "oQeyNewGalleryID", newgallid: id, wpnonce: '<?php echo wp_create_nonce('oqey-upload'); ?>' },
        function(data){
	        var data = eval('(' + data + ')');
            jQuery('#content').hide().html(decodeURIComponent(data.response)).fadeIn("slow");
			jQuery('#titlul_b').show();
			jQuery('#titlul').show().html("<span style='float:left; margin-right:3px;'>Gallery title: <\/span> <span title='<?php _e('Double-click to rename...', 'oqey-gallery'); ?>' class='dblclick' id='gall_id_"+ id +"'>" + decodeURIComponent(data.titlul) + "<\/span>");
						
        if(data.folderexist!=''){
			  jQuery('#error').html(decodeURIComponent(data.folderexist));
        }
			
        jQuery("#user").change(function () {
            var usrid = jQuery("#user option:selected").val();
        
        jQuery.post( ajaxurl, { action: "oQeyAddUserID", usrid: usrid, gid: id },function(data){ } );
		
        });
			
        jQuery("#view_all_galleries").click(function(){
			window.location = "<?php echo admin_url('admin.php?page=oQeyGalleries'); ?>";										 
		 });
			
			var playerVersion = swfobject.getFlashPlayerVersion();
		    if(playerVersion.major<8){	
			ajaxUploader(data.noflashinfo);
			}
			jQuery.post( ajaxurl, { action: "oQeyGetAllImages", id: data.galid },
            function(data){
			var data = eval('(' + data + ')');

            jQuery('#gallery_content').hide().html(decodeURIComponent(data.allimages)).fadeIn("slow");
 		    jQuery("#sortablegalls").selectable("disable");
    
    /*
    jQuery(function() {
		var allimgs = jQuery('#sortablegalls').sortable('serialize'); 
        alert(allimgs);
	    
        jQuery.post( ajaxurl, { action: "oQeyOrderAllImages", orderallimgs: allimgs, galleryid: id }, function(data){ alert(data); });
        
    	jQuery( "#sortablegalls" ).disableSelection();
        
            
	});
    */
            
    jQuery("#sortablegalls").sortable({
    create: function(event, ui) { 
        var allimgs = jQuery('#sortablegalls').sortable('serialize'); 
        jQuery.post( ajaxurl, { action: "oQeyOrderAllImages", orderallimgs: allimgs, galleryid: id }, function(data){});
        },
    update: function(){	//onupdate update the image order		
	
    		var allimgs = jQuery('#sortablegalls').sortable('serialize');						
	
    jQuery.post( ajaxurl, { action: "oQeyOrderAllImages", orderallimgs: allimgs, galleryid: id },
            function(data){});
            
       }   
    });
 
    jQuery(".dblclick").editable( ajaxurl, { 
            indicator : '<?php _e('Updating...', 'oqey-gallery'); ?>',
            tooltip   : "<?php _e('Double-click to rename...', 'oqey-gallery'); ?>",
            event     : "dblclick",
            style  : "inherit",
	        width : "670px",
	        height : "15px",
            submitdata : function ( value, settings ) { return { "action": 'oQeyEditGalleryTitle' }; }
   });
 
//preloadImages(decodeURIComponent(data.allimgpreload));
		

jQuery(".styled").click(function(){ 	
if (jQuery(this).attr('checked')){ jQuery(this).parent().parent().css('background-color', '#C0CFEF'); }else{ jQuery(this).parent().parent().css('background-color', '#F8F8F8'); }
        var names = [];
        jQuery('#sortablegalls input:checked').each(function() {
            names.push(this.id);
        });
});

jQuery("#oqeymusic").click(function(){ 

  if (jQuery("#musicpostbox").is(":hidden")) {
      jQuery("#musicpostbox").slideDown("slow");
	  
	  var id = jQuery('#currentGalID').val();	
	  jQuery.post( ajaxurl, { action: "oQeyOrderAndSelectMusic", music_gall_id: id },
            function(data){  			
			jQuery('#musiclist').hide().html(data).fadeIn("slow"); 
			jQuery("#sortablemuzon").sortable({ 
 	         revert: true 
 		    });
			
			jQuery("#savemusic").click(function(){
            var selectedmusic = jQuery('#musicselect').serializeArray();
			var id = jQuery('#currentGalID').val();	
            jQuery.post( ajaxurl, { action:"oQeySaveMusicOrder", selectedmusic: selectedmusic, mgalleryid: id },
            function(data){
                jQuery("#messages").hide().html('<p class="updated fade">' + data + '<\/p>').fadeIn("slow"); 
                clearUp();  
                }
			);

           });
		});

    } else {
      jQuery("#musicpostbox").slideUp(500);
	  setTimeout(function () {  jQuery('#musiclist').html('<div class="obis"><?php _e('Loading content...', 'oqey-gallery'); ?><\/div>');  }, 1000);	  
    }
});


jQuery("#oqeyskin").click(function(){ 

  if (jQuery("#skinpostbox").is(":hidden")) {
      jQuery("#skinpostbox").slideDown("slow");
	  
	  var id = jQuery('#currentGalID').val();	
	  jQuery.post( ajaxurl, { action: "oQeyGetAllSkins", skin_gall_id: id },
            function(data){  			
			
			jQuery('#skinlist').hide().html(data).fadeIn("slow"); 

			jQuery(".activate_skin").click(function(){			 
			 var skinid = jQuery(this).attr("id");			 
			jQuery.post( ajaxurl, { action:"oQeySetNewSkin", skinid: skinid, skin_gallery_id: id },
            function(data){ jQuery("#messages").hide().html('<p class="updated fade">' + data + '<\/p>').fadeIn("slow"); clearUp();
			jQuery("#skinpostbox").slideUp(500);
	        setTimeout(function () {  jQuery('#skinlist').html('<div class="obis"><?php _e('Loading content...', 'oqey-gallery'); ?><\/div>');  }, 1000);
			}
			);
        });
    });

    } else {
      jQuery("#skinpostbox").slideUp(500);
	  setTimeout(function () {  jQuery('#skinlist').html('<div class="obis"><?php _e('Loading content...', 'oqey-gallery'); ?><\/div>');  }, 1000);	  
    }

});


/*ADD VIDEO FILES TO GALLERY*/
  jQuery("#addvideofile img").click(function(){ 
    
    //var id = jQuery(this).parent(".videoimg").attr("id");
    
    var id = jQuery('#currentGalID').val();
    
          jQuery.ajax({
		  url: ajaxurl,
		  type : "POST",
		  data:  {action: 'oQeyGetAllVideoFiles', galid: id},
		  cache: false,
		  success: function(data){

		    	jQuery("#video_content").html(data);
                jQuery("#oqeyvideomanagement").dialog({ width: 900, height: 600, resizable: false, autoOpen: true, title: 'Add video', modal: true, draggable: false });
 
            },
		    error: function() {
	  		    jQuery("#save").html('<div class="updated fade" id="message"><p><?php _e('There is an error, please try again.', 'oqey-gallery'); ?><\/p><\/div>');
		    }
		});    
    });


/*END VIDEO ADD*/

jQuery("#watermark-settings img").click(function(){ //watermark management
   
   if (jQuery("#watermarkpostbox").is(":hidden")){ 
    
    jQuery("#watermarkpostbox").slideDown("slow");
	  
	  var id = jQuery('#currentGalID').val();	
        
	  jQuery.post( ajaxurl, { action: "oQeyGetWatermarkSettings", id: id },
          function(data){  			
			
          jQuery('#watermarklist').hide().html(data).fadeIn("slow"); 

            jQuery("#oqey_wtm input").change(function(){
                
               if(jQuery(this).val()!="0d"){ 
                 jQuery("#customtw").prop("checked", true);
               }
                
                //jQuery("#customtw").prop("checked", true);
                //jQuery(".myCheckbox").prop("checked", false);

            
              var id = jQuery('#currentGalID').val();
              var data = jQuery("#oqey_wtm").serialize();
		 
			  jQuery.post( ajaxurl, { action:"oQeySaveWatermarkSettings", id: id, data: data },
                function(data){});            
              });
     });

    }else{
        
      jQuery("#watermarkpostbox").slideUp("slow");
	  setTimeout(function () {  jQuery('#watermarklist').html('<div class="obis"><?php _e('Loading content...', 'oqey-gallery'); ?><\/div>');  }, 1000);	  
    
    }
    
});


jQuery("#selectall").click(function(){ 
    
     if (jQuery(this).attr('checked')){ 
         
         jQuery('#sortablegalls input').attr('checked', true); 
         jQuery('#seelectmessage').text('<?php _e('clear all', 'oqey-gallery'); ?>');
         jQuery("#sortablegalls li").css('background-color', '#C0CFEF'); 
    
    }else{
         
         jQuery('#sortablegalls input').attr('checked', false); 
         jQuery('#seelectmessage').text('<?php _e('select all', 'oqey-gallery'); ?>');
         jQuery("#sortablegalls li").css('background-color', '#F8F8F8'); 
         
    }	
});

jQuery("#doapply").click(function(){ 
var id = jQuery('#dolist').val();

if(id==3){

jQuery('#sortablegalls input').attr('checked', false); 
jQuery('#seelectmessage').text('select all');
jQuery("#sortablegalls li").css('background-color', '#F8F8F8'); 
jQuery('#selectall').attr('checked', false); 

var gid = jQuery('#currentGalID').val();

var allimgs = jQuery('#sortablegalls').sortable('serialize');			
jQuery.post( ajaxurl, { action: "oQeyOrderAllImages", orderallimgs: allimgs, galleryid: gid, imgreverse: "yes" },
function(data){
	getGalleryDetails(gid);
	});	
	
}

if(id==2){
        var names = [];
        jQuery('#sortablegalls input:checked').each(function() {
            
		   if ( jQuery(this).parent().parent().is(':visible')){
             
              names.push(this.value);		
              	
		   }
		
        	jQuery(this).parent().parent().hide("slow");	
        
        });
		
        jQuery.post( ajaxurl, { action: "oQeyImagesToTrash", imgalltotrash: encodeURIComponent(names) },
        function(data){
		if(names.length>0){
		jQuery("#messages").hide().html(decodeURIComponent('<p class="updated fade">' + data + ' <a href="#undo" id="undoallimages"><?php _e('undo', 'oqey-gallery'); ?><\/a><\/p>')).fadeIn("slow"); 

			jQuery("#undoallimages").click(function(){
			jQuery.post( ajaxurl, { action: "oQeyImagesFromTrash", imgallfromtrash: encodeURIComponent(names) },
            function(r){

			var data = eval('(' + r + ')');			
			jQuery.each(data.imgallfromtrash, function(i, val) {
            jQuery("#img_li_" + val).css('background-color', '#F8F8F8');
			jQuery("#selected_" + val).attr('checked', false); 
			jQuery("#img_li_" + val).show("slow");
            });

			jQuery("#messages").hide().html('<p class="updated fade"><?php _e('All selected images was restored.', 'oqey-gallery'); ?><\/p>').fadeIn("slow");
			jQuery('#selectall').attr('checked', false); 
            jQuery('#seelectmessage').text('select all');
			
			clearUp(); 	
			});	
			});
		}else{
		jQuery("#messages").hide().html(decodeURIComponent('<p class="updated fade"><?php _e('Please select an image', 'oqey-gallery'); ?><\/p>')).fadeIn("slow"); 
		clearUp(); 
		}		
		
        });
       }
       });

        });		
    });	
}

function deleteGallery(id){ 
            jQuery.post( ajaxurl, { action:"oQeyGalleryToTrash", movetotrashgall: id },
            function(data){
            jQuery('#row_' + id).fadeOut("slow");					
			jQuery("#messages").hide().html('<p class="updated fade">' + data + ' <a href="#undo" id="undogallery"><?php _e('undo', 'oqey-gallery'); ?><\/a><\/p>').fadeIn("slow");
			
			jQuery("#undogallery").click(function(){
			jQuery.post( ajaxurl, { action: "oQeyGalleryFromTrash", undogallid: id },
            function(r){
			var data = eval('(' + r + ')');		
			jQuery("#messages").hide().html('<p class="updated fade">' + decodeURIComponent(data.mesaj) + '<\/p>').fadeIn("slow");
			jQuery('#row_'+id).fadeIn("slow");
			});			
			});	
         });
}

function openPreview(){
jQuery("#dialog").dialog( {autoOpen: false});
}

function hoverGallery(){
jQuery("#sortable tr").hover(
  function () {
  jQuery(this).children(".lasttd").children(".hiddenm").addClass("visiblem"); 
  },
  function () {
  jQuery(this).children(".lasttd").children(".hiddenm").removeClass("visiblem").addClass("hiddenm");
  }
);
}
</script>

<input type="hidden" value="" id="currentGalID" name="currentGalID"  />
<div class="wrap">
    <h2 style="width: 930px;"><?php _e('Manage galleries', 'oqey-gallery'); ?>
    <div style="margin-left:250px; float:right; width: 200px; height: 20px;">
     <div id="fb-root"></div>
     <div class="fb-like" data-href="http://www.facebook.com/oqeysites" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="tahoma"></div>
     <div class="fb-send" data-href="http://oqeysites.com"></div>
    </div>
    </h2>
  <div id="error">&nbsp;</div>
</div>

<!--<img src="<?php //echo oQeyPluginUrl().'/images/'; ?>upperbanddress.jpg" width="950" height="120" /><br /><br />-->
<div class="postbox" style="height:50px; width:900px;">
    <div style="margin-right:2px; padding-top:3px; float:right;">
    <a href="<?php echo admin_url('admin.php?page=oQeySkins&showskins=yes'); ?>"><img src="<?php echo oQeyPluginUrl().'/images/'; ?>getmoreskinsbgn.png" width="250" height="48" /></a>    
    </div>

</div>


<div class="postbox" style="width:900px;">
<div id="creator">
<?php _e('Create a new gallery:', 'oqey-gallery'); ?>
  <input name="newtitle" id="newtitle" /> 
  <input type="button" name="creategall" id="creategall" value="<?php _e('Create', 'oqey-gallery'); ?>"/>
</div>
<div id="messages" style=" float:left;">&nbsp;</div>
<div id="magic">
       <a href="#create" id="createfromothers">
          <img src='<?php echo oQeyPluginUrl().'/images/wizard.png'; ?>' height="25" width="25" alt="Import from existing galleries" title="<?php _e('Import from existing galleries', 'oqey-gallery'); ?>"/>
       </a>
</div>
<br class="clear" />
</div>

<div class="postbox" id="createfrompostbox">
   <div id="importlist"><?php _e('Loading content...', 'oqey-gallery'); ?></div>
</div>

<div class="postbox" style="width:900px; display:none;" id="titlul_b">
   <div id="titlul"><div class="obis"><?php _e('Loading content...', 'oqey-gallery'); ?></div></div>
</div>

<div class="postbox" style="width:900px;">
   <div id="content"><div class="obis"><?php _e('Loading content...', 'oqey-gallery'); ?></div></div>
</div>

<div class="postbox" id="watermarkpostbox">
   <div id="watermarklist"><div class="obis"><?php _e('Loading content...', 'oqey-gallery'); ?></div></div>
</div>

<div class="postbox" id="musicpostbox">
   <div id="musiclist"><div class="obis"><?php _e('Loading content...', 'oqey-gallery'); ?>.</div></div>
</div>

<div class="postbox" id="skinpostbox">
   <div id="skinlist"><div class="obis"><?php _e('Loading content...', 'oqey-gallery'); ?></div></div>
</div>

<div class="galleriesbox" id="galleryarea">
   <div id="gallery_content"><div class="obis"><?php _e('Loading content...', 'oqey-gallery'); ?></div></div>
<br class="clear" />
</div>

<a href="http://www.oqeysites.com" target="_blank"><img src="<?php echo oQeyPluginUrl().'/images/'; ?>galleries_banner.png"/></a>

<script type="text/javascript">
jQuery(document).ready(function($){
    
jQuery.loadImages([ '<?php echo oQeyPluginUrl().'/images/preview_button.png'; ?>', 
                    '<?php echo oQeyPluginUrl().'/images/remove_button.png'; ?>', 
					'<?php echo oQeyPluginUrl().'/images/edit_button.png'; ?>', 
					'<?php echo oQeyPluginUrl().'/images/remove_button.png'; ?>', 
					'<?php echo oQeyPluginUrl().'/images/settings_small_button.png'; ?>', 
					'<?php echo oQeyPluginUrl().'/images/remove_small_button.png'; ?>', 
					'<?php echo oQeyPluginUrl().'/images/skin_button.png'; ?>', 
					'<?php echo oQeyPluginUrl().'/images/music_button.png'; ?>', 
					'<?php echo oQeyPluginUrl().'/images/ui-icons_222222_256x240.png'; ?>', 
					'<?php echo oQeyPluginUrl().'/images/ui-icons_454545_256x240.png'; ?>', 
					'<?php echo oQeyPluginUrl().'/images/ui-icons_888888_256x240.png'; ?>' ],function(){});

/*loading galleries*/ //oQeyGetAllGalleries	
jQuery.post( ajaxurl, {action: 'oQeyGetAllGalleries', allgalls: "yes" }, function(data){
    
    jQuery('#content').hide().html(data).fadeIn("slow");

		jQuery('.preview-gallery').click(function(){
		  
		var id = jQuery(this).attr("id");
		var playerVersion = swfobject.getFlashPlayerVersion();
		if(playerVersion.major<8){	
		var flash = "no";
	    }else{
		var flash = "yes";
		}
		
jQuery.post( ajaxurl, { action: 'oQeyGetPreviewGallery', previewid: id, flash: flash  }, function(r){   
    
		var $dialog = jQuery('<div style="height:590px; width:896px; position:relative; display:block;"><\/div>').html(r).dialog({
			width: 896,
			maxWidth: 896,
			maxHeight: 590,
			resizable: false,
			autoOpen: false,
			title: '<?php _e('Preview gallery', 'oqey-gallery'); ?>',
			modal: true,
			draggable: false	
		});	
		
		$dialog.dialog('open'); 
		});
		return false;
	});
   
    jQuery('.dblclick').editable( ajaxurl, {
            indicator : '<?php _e('Updating...', 'oqey-gallery'); ?>',
            tooltip   : "<?php _e('Double-click to rename...', 'oqey-gallery'); ?>",
            event     : "dblclick",
	        width : "500px",
	        height : "15px",
            submitdata : function ( value, settings ) { return { "action": 'oQeyEditGalleryTitle' }; }
    });

   hoverGallery();
});

jQuery('#newtitle').keypress(function(e) {
        if(e.which == 13) {
            jQuery('#creategall').trigger("click");
        }
    });

/*Create new gallery*/
jQuery("#creategall").click(function(){ 
var newtitle = jQuery("#newtitle").val();

jQuery.post( ajaxurl, { action:"oQeyNewGallery", newtitle: newtitle },
 function(html){
    
         var data = eval('(' + html + ')');

         if(data.response=="Created"){	
			jQuery(".nogallery").hide();
            jQuery("#messages").hide().html(decodeURIComponent('<p class="updated fade">' + data.response + '<\/p>')).fadeIn("slow"); 
			jQuery('#newtitle').attr('value', '');
			clearDiv(); 
			getGalleryDetails(data.galid);
         }else{			
			jQuery("#messages").hide().html(decodeURIComponent('<p class="error fade">' + data.response + '<\/p>')).fadeIn("slow");  
			clearDiv(); 
         }
   });   
});
/*END new gallery*/

/*import from others galleries*/
jQuery("#createfromothers").click(function(){ 

  if(jQuery("#createfrompostbox").is(":hidden")) {
      jQuery("#createfrompostbox").slideDown("slow");
	  	
	  jQuery.post( ajaxurl, { action: "oQeyCheckForOthersGalleries", info: "all" },
            function(data){  	
            
	  		jQuery('#importlist').hide().html(data).fadeIn("slow"); 
            
            jQuery('#othersgalls').change(function() {
                
               jQuery('#otgallstitle').remove();
                
               if(jQuery(this).val()!=0){
               
                      jQuery.post( ajaxurl, { action: "oQeyCheckForOthersGalleries", info: jQuery(this).val() },
                        function(data){ 
                            jQuery('#importlist').append(data); 
                                                        
                            jQuery('#otgallstitle').change(function() {
                                
                                if(jQuery(this).val()==0){
                                     jQuery('#importnewgall').hide();
                                }else{
                                     jQuery('#importnewgall').show();
                                
                            //create new gallery from others existed
                            jQuery("#importnewgall").click(function(){      
                            jQuery.post( ajaxurl, { action:"oQeyCheckForOthersGalleries", info: "nextgencreate", gid: jQuery("#otgallstitle").val() },
                               function(html){
                                 var data = eval('(' + html + ')');

                                if(data.response=="Created"){	
			                         jQuery(".nogallery").hide();
                                     jQuery("#messages").hide().html(decodeURIComponent('<p class="updated fade">' + data.response + '<\/p>')).fadeIn("slow"); 
			                         //jQuery('#newtitle').attr('value', '');
			                         clearDiv(); 
 			                         getGalleryDetails(data.galid);
                                  }else{			
			                         jQuery("#messages").hide().html(decodeURIComponent('<p class="error fade">' + data.response + '<\/p>')).fadeIn("slow");  
			                         clearDiv(); 
                             }
                             
                            }); 
                           });
                          }
                         });
                        });
                      
               }else{
                jQuery('#otgallstitle').remove();
                jQuery('#importnewgall').remove();
               }
                                              
            });
                        
	  	});
      
      }else{
        
      jQuery("#createfrompostbox").slideUp(500);
	  setTimeout(function (){ jQuery('#importlist').html('<?php _e('Loading content...', 'oqey-gallery'); ?>');  }, 1000);	  
      
    }
});
/*END import*/



function clearDiv(){ setTimeout(function () {  jQuery('#messages').fadeOut(function(){ jQuery("#messages").html("&nbsp;"); }); }, 2000); } 

	jQuery( "#detailsimg" ).dialog({
			width: 600,
			height: 400,
			maxWidth: 600,
			maxHeight: 400,
			resizable: false,
			autoOpen: false,
			modal: true,
			title: "Details",
			draggable: false
		});
		
jQuery("#updatedetails").click(function() {            
   //var alldata = jQuery("#updateform").serialize();
   if(jQuery("#updateform #splash").is(':checked')){ var splash="on"; }else{ var splash= ""; } 
   if(jQuery("#updateform #splashexclusive").is(':checked')){ var splashexclusive="on"; }else{ var splashexclusive= ""; }
   var  imgid = encodeURIComponent( jQuery("#updateform #imgid").val() );
   var  galid = encodeURIComponent( jQuery("#updateform #galid").val() );
   var  alt = encodeURIComponent( jQuery("#updateform #alt").val() );
   var  comments = encodeURIComponent( jQuery("#updateform #comments").val() );
   var  oqey_image_link = encodeURIComponent( jQuery("#updateform #oqey_image_link").val() );
  
   jQuery.ajax({
   type: "POST",
   url:  ajaxurl,
   data: {action: "oQeyUpdateImageDetails", splash:splash, splashexclusive:splashexclusive, imgid: imgid, galid:galid, alt:alt, comments:comments, oqey_image_link:oqey_image_link },
   success: function(r){
    
    var data = eval('(' + r + ')');
    
	//var id = jQuery("#sortablegalls .imgsel").attr("id");
    jQuery("#detailsimg").dialog('close');
    
	if(data.splash=="yes"){ 	
    if( jQuery("#sortablegalls li").hasClass("imgsel") ){ 
        jQuery("#sortablegalls li").removeClass("imgsel"); 
        }
    
        jQuery("#sortablegalls li").css("border", "thin solid #C1C1C1");
	    jQuery("#img_li_" + data.id).addClass('imgsel'); 
	    jQuery("#img_li_" + data.id).css("border", "#7A82DE thin solid"); 
	
	}else{	
	    jQuery("#img_li_" + data.id).removeClass('imgsel'); 
		jQuery("#img_li_" + data.id).css("border", "#C1C1C1 thin solid");
		
	}
   }
 });
});
});
	
function showSettings(id){
     
            jQuery.post( ajaxurl, { action :"oQeyImageDetails", imagedetails: id },
            function(data){
                
				var data = eval('(' + data + ')');				
				var galid = jQuery('#currentGalID').val();
                jQuery('#detailsimg #imgid').val(id);
				jQuery('#detailsimg #galid').val(galid);

                if(data.type=="video"){

                    jQuery('#detailsimg #p_splash').hide();
                    jQuery('#detailsimg #p_splashexclusive').hide()
                    jQuery('#detailsimg #alt').css("height", "80");
                    jQuery('#detailsimg #comments').css("height", "100");
                    
                }else{
                    
                    jQuery('#detailsimg #p_splash').show();
                    jQuery('#detailsimg #p_splashexclusive').show()
                    jQuery('#detailsimg #splash').attr('checked', false); 
                    jQuery('#detailsimg #splashexclusive').attr('checked', false); 
                    jQuery('#detailsimg #alt').css("height", "60");
                    jQuery('#detailsimg #comments').css("height", "75");
                    
                }
                
                if(data.type!="video"){
				
                   if(data.splash=="on"){ 
                    
                       jQuery('#detailsimg #splash').attr('checked', true);
                   
                   }else{ 
                       
                       jQuery('#detailsimg #splash').attr('checked', false); 
                   
                   }
				
                   if(data.splashexclusive=="on"){ 
                    
                      jQuery('#detailsimg #splashexclusive').attr('checked', true); 
                   
                   }else{ 
                      
                      jQuery('#detailsimg #splashexclusive').attr('checked', false); 
                   
                   }
                   
                }					 
				
                   jQuery('#detailsimg #comments').val(decodeURIComponent(data.comments));
				   jQuery('#detailsimg #alt').val(decodeURIComponent(data.alt));									
			   	   jQuery('#detailsimg #oqey_image_link').val(decodeURIComponent(data.link));
                   jQuery( "#detailsimg" ).dialog( "open" );
            
            });

}

/*Add selected video*/
function SaveVideoImage(){
    
   var data = jQuery("#videoslist").serialize();
   var galid = jQuery('#currentGalID').val();
    
   jQuery.ajax({
   type: "POST",
   url:  ajaxurl,
   data: {action: "oQeyAddVideoToGallery", data: data, galid: galid },
   success: function(r){

            getGalleryDetails(galid);
            jQuery("#oqeyvideomanagement").dialog('close');
   
   }
 });

}
</script>

<div id="detailsimg" style="display:none;">

  <form id="updateform" name="updateform" action="#null">

    <input type="hidden" id="imgid" name="imgid"/>
    <input type="hidden" id="galid" name="galid"/>
    
    <p id="p_splash"><input name="splash" id="splash" type="checkbox" class="splash"/> &nbsp; <?php _e('Set this photo as a gallery splash.', 'oqey-gallery'); ?></p>
	<p id="p_splashexclusive"><input name="splashexclusive" id="splashexclusive" class="splashexclusive" type="checkbox"/> &nbsp; <?php _e('Make it splash exclusive.', 'oqey-gallery'); ?></p>    
	<p>
	<?php _e('Description', 'oqey-gallery'); ?> (alt):<br/>
	<textarea name="alt" id="alt" class="alt" style="height:60px; width:550px;"></textarea>
	</p>
    
	<p>
	<?php _e('Comments', 'oqey-gallery'); ?> :<br/>
	<textarea name="comments" id="comments" class="comments" style="height:75px; width:550px;"></textarea>
	</p>
    
    <p>Link <small>(ex: http://oqeysites.com), * <?php _e('Note: will work with supported commercial skins only.', 'oqey-gallery'); ?></small><br />
    <input type="text" name="oqey_image_link" id="oqey_image_link" style="width:550px;" value="" /></p>
	<p>
	<input type="button" name="updatedetails" id="updatedetails" value="save details" />
	</p>
  
  </form>

</div>

<div id="oqeyvideomanagement" style="display:none; margin:10px;">
<div style="overflow-y: auto; overflow-x: none; height:483px; border:#999 thin solid; padding:5px; background: #ccc; border-radius:3px;" id="video_content"></div>
<div style="margin:10px; text-align:center; vertical-align: middle;">
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="oqey_license" />
<input type="button" class="button-primary" style="width:100px; margin-top:5px;" value="<?php _e('Add video', 'oqey-gallery'); ?>" onclick="SaveVideoImage(); return false;" />
</div>
</div>