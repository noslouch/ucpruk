// JavaScript Document
jQuery(window).load(function(){
jQuery('#slides').slides({
	autoHeight: true,
	effect: 'fade',
	container: 'slides_container',
																				play: 6000,
	slideSpeed: 600,
	fadeSpeed: 350,
	generateNextPrev: true,
	generatePagination: true,
	crossfade: true
});
	jQuery( '#slides .pagination' ).wrap( '<div id="slider_pag" />' );
	jQuery( '#slides #slider_pag' ).wrap( '<div id="slider_nav" />' );
});