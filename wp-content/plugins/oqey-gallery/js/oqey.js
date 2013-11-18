    var oqeyname=1;
	var firstplay = true;
	function checkActivePlayer(newname) {
		if (!firstplay) {
        	getFlashMovie("oqeygallery" + oqeyname).sendIDToFlash(newname);
			oqeyname = newname;
		} else {
			oqeyname = newname;
			firstplay = false;
		}
	}
    
	function getFlashMovie(movieName) {
		var isIE = navigator.appName.indexOf("Microsoft") != -1;   return (isIE) ? window[movieName] : document[movieName];
	}

function oqey_e(pv, nr, t, optouch, incolums, pfv, allimages ){
        
        var t = t.replace(/\[/g, '<');
		var t = t.replace(/\]/g, '>');

   if( ( pv.major>8 && pfv=="on") || pv.major<8 || pfv=="on" ){	
		var res = oqeyurldecode(t);		
		jQuery("#oqey_image_div" + nr).show();
        jQuery("#flash_gal_" + nr).hide();
        jQuery("#oqeygallery" + nr).hide();        
		jQuery("#image" + nr).show().html(res);
      
   if(incolums=="off"){		
	jQuery('#image'+ nr + '').cycle({
		timeout: 0,
		fx: 'scrollHorz',
		next: '#next' + nr + '',
		prev: '#prev'+ nr + '' 
	});
   
   if(optouch=="on"){
	jQuery('#image' + nr + '').touchwipe({
 		wipeLeft: function() {
 	 		jQuery('#image'+ nr + '').cycle('next');
 		},
 		wipeRight: function() {
 	 		jQuery('#image'+ nr + '').cycle('prev');
 		},		
		wipeUp: function() {},
		wipeDown: function(){}
	});
   }
		
  }else{ jQuery(".gall_links").hide(); }
  
 }else{    
    jQuery("#flash_gal_" + nr).show();
    jQuery("#oqey_image_div" + nr).hide();        
 }
 
}

/*widget js*/
function oqey_e_w(pv, nr, t, v){
	
	    var t = t.replace(/\[/g, '<');
		var t = t.replace(/\]/g, '>');

   if(pv.major<8){	
		var res = oqeyurldecode(t);		
		jQuery("#oqey_image_div" + nr).show();
        jQuery("#flash_gal_" + nr).hide();
		jQuery("#image" + nr).show().html(res);
		
	jQuery('#image'+ nr + '').cycle({
		fx:    'fade', 
        timeout:  v 
	});
    
 }else{    
    jQuery("#flash_gal_" + nr).show();
    jQuery("#oqey_image_div" + nr).hide();        
 }
}

function oqeyurldecode(t){
	var r = decodeURIComponent( t.replace(/\+/g, '%20') );
	return r;
}