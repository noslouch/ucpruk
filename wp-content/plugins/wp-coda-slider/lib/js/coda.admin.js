/**
 * Javascript for WP Coda Slider Options Page
 * @package WP Coda Slider
 * @subpackage admin.js
 *
 */

!function ($) {

    $(function () {

        var theDiv = $('#section-custom_slider_meta');
        var theSelect = '#slider_meta';
        var theVal = $('' + theSelect + ' option:selected').val();
        var arr = ['template', 'id-only' ];

        if ( -1 == $.inArray(theVal, arr) )
            theDiv.hide();

        $(theSelect).change(function() {
            var value = $('' + theSelect + ' option:selected').val();
            console.log(value);
            if( -1 === $.inArray(value, arr )) {
                theDiv.slideUp();
            } else {
                theDiv.slideDown();
            }
        })

    });

}(jQuery);