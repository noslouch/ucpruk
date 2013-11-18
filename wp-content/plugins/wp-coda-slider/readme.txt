=== Plugin Name ===
Contributors: c3mdigital
Tags: coda slider, featured content, featured content slider, jquery, coda slider, js, no conflict, shortcode, coda, panic slider
Donate link: http://c3mdigital.com/donations/
Requires at least: 3.0
Tested up to: 3.4.1
Stable tag: 0.3.6.2

Add a jQuery Coda Slider to any WordPress post or page configured with custom metabox options, shortcode or template tag

== Description ==

WP Coda Slider adds a Coda Slider using Kevin Batdorf's new  Coda Slider jQuery plugin.  Use a shortcode, template tag or create a custom slider using the custom meta boxes added to the post edit screen.


= Metabox Options =

All options to configure individual sliders are available through custom meta boxes on all WordPress posts and pages

= Shortcode: =

The short code accepts the following arguments: id, cat, show, args.
id= a unique name for each slider that will be assigned as the div id
cat= the category containing the posts to display in the slider
show= the number of posts to show in the slider
args= the settings for the slider which can be found at https://github.com/KevinBatdorf/CodaSlider/tree/master
*Please Note:* the args in the shortcode must be wrapped with double quotation marks to work.

= Admin Options =

New for 0.3.6 is an admin options page that controls the placement of the meta boxes.  You can choose to place them on all posts, pages and custom post types or by specific post id or page template.

= Example: =

`[wpcodaslider id=myslider cat=4 show=6 args="autoSlide:true, dynamicTabs:false, autoSlide:true"]`

This would add a slider with the `<div id="myslider">` showing 6 posts from the category id of 6.

= Template Tag =

Usage:

`if ( function_exissts( 'c3m_slider' ) )
	c3m_slider( $args );`

to any of your themes templates.

you must supply the variables when you add the function to your template.

`$args = array(
	'id'    => 'your unique id',
	'cat'   => 23,  // The Category to query the posts from
	'show'  => 5,  // Number of posts to query
	'slider_args'   => array(       // Optional - defaults below
		'autoHeight'               => 'true',
		'autoSlide'                => 'false',
		'autoSlideInterval'        => '7000',
		'autoSlideStopWhenClicked' => 'true',
		'dynamicArrows'            => 'true',
		'dynamicArrowsGraphical'   => 'false',
		'dynamicArrowLeftText'     => '&#171; left',
		'dynamicArrowRightText'    => 'right &#187;',
		'dynamicTabs'              => 'true',
		'dynamicTabsAlign'         => 'center',
		'slideEaseDuration'        => '1000',
		'slideEaseFunction'        => 'easeInOutExpo'
	),
);`

`c3m_slider( $args );`

This would add a slider with the id of myslider and show 5 posts from category 81 with dynamic arrows set to false.
all the variables must be present and in the same order.


== Installation ==

1. Upload the `wp-coda-slider` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Each post and page will now contain custom options to include a slider on that post or page
4. You can also add a shortcode to any post or page you want to display your slider on. Make sure to specify the category id, slider id(each slider requires you to give it a unique id) and the number of posts to show. eg: `[wpcodaslider id=myslidername cat=4 show=3 args="autoSlide: true"]`
5. There is also a template tag function.  See Description page on how to use the template tag.
6. To use a custom override CSS file copy the experimental / alternate CSS file, coda-slider.css to your themes root directory.  Any changes made to this will not be overridden when updating the plugin

Shortcode args:

the shortcode args except any of the coda slider jQuery settings shown below.  Please note: the args must be wrapped in quotation marks to work ie:  args=" ".

For a full list of slider settings see https://github.com/KevinBatdorf/codaslider

== Frequently Asked Questions ==

= Whats new in version 0.3? =
0.3 is a complete code rewrite. You will now find a metabox on your post and page screens that give you complete control of all the slider options.  Once configured the slider will be automatically added to the page.
CSS options:  You can control the css using the options and can even include a block of custom css.

shortcodes are deprecated but will still work as before

existing template tag function has been deprecated and replaced with a new one.  The new one takes the arguments as an array.

=  What does the id argument do? =

The id argument is the div id that will be assigned to the slider.  Make sure each slider has a unique id.

= What is the cat argument? =
This is the WordPress post category the slider will pull from and display in your slider.

= Where can I use the shortcode? =
The short code will work on posts, pages, and Wordpress 3.0 custom post types.

= How can I change the style of the slider or modify the width or default CSS? =

Copy the coda-slider-3.0.css file and move it to your themes root directory and rename it to coda-slider.css.  When outputting the CSS the plugin looks for this file.  If not found the defualt css will be added.


= Where can I find all the values available for the shortcode args? =
The full description of the shortcode arguments can be found on the new github Coda Slider repository  at: https://github.com/KevinBatdorf/codaslider

= Where can I get support or help? =
Please post support questions to the wordpress.org support forums with the tag wp-coda-slider

== Screenshots ==

1. The slider and css options for each slider
2. The coda slider options

== Changelog ==

= 0.3.6.2 =
* Meta box text error fixed.
* Missing js file in 0.3.6.1
* Other bug fixes

= 0.3.6 =
* Added admin options page to show or hide the meta boxes by post type, post id or page template filename.
* New option dynamicArrowsGraphical when set to true uses graphical based arrows
* New meta box option Show Inline CSS will not output any inline css when true. ( Please make sure you are using a custom css file if true)
* Various bug fixes and improvements under the hood.

= 0.3.5 =
* Improved Shortcode usage
* Fixed javascript bug that caused the loading gif to get stuck
* Updated the Coda Slider jQuery library to use Kevin Batdorf's new Coda Slider Version
* Added the option to overide the default CSS with your own CSS file placed in your themes root directory
* Javascript and CSS only load on pages where the shortcode, template tag or custom slider are added

= 0.3.4 =
* Bug Fix - Fixed endless loop bug when adding a post to a slider that contains a slider
* Enhanced the query created by the slider - removed pagination, post meta and terms
* Bug Fix new args do a better job of overriding default args
* Better code documentation
* New Template tag function c3m_slider()
* Made plugin url path compatible with SSL

= 0.3.3.2 =
* Fixed javascript bug
* Added additional options

= 0.3.2 =
* Complete code rewrite.  All slider options are now available through a custom metabox added to each WordPress post and pages.
* jQuery easing plugin enqueued separately to avoid conflict with other plugins that use jQuery easing
* shortcodes have been deprecated.  They will still work as before but will no longer be supported
* template tag function has been deprecated.  It will still work as before but will no longer be supported.  Next version will include a new template tag function

= 0.2.5 =
* Fixes the shortcode inside a shortcode bug
* Props:  Morten Ydefeldt

= 0.2.4 =
* Updated query strings to arrays.  This won't change anything for most users.

= 0.2.3 =
* Bug fixes:
* Fixed documentation error for template tag function call Props:Bira
* Fixed path to ajax-loader.gif Props:shootingstar.co.uk
* Added direction:ltr; to css for compatability with rtl languages Props:Bira

= 0.2.2.1 =
* Add a template tag method to call the slider to use when calling posts that contain other shortcodes.

= 0.2.1 =
* Added the description for the arguments in the shortcode

= 0.2 =
* Fixed the readme file to display full description


== Upgrade Notice ==

= 0.3.6.2 =

* IMPORTANT!! 0.3.6.1 was missing an important js file please upgrade.

= 0.3.5 =
* Important!! Please upgrade to fix many bugs see changelog for more details

= 0.3.4 =
* Numerous bug fixes.  Please upgrade if your are on 0.3.3 or above!
* Stabilizes 0.3 branch

= 0.3.3.2 =
* Fixes javascript errors.  Please upgrade if you are on 0.3.3 or above!

= 0.2.2.1 =
* Upgrade to have option to use template tags or shortcodes.  Using template tags allows posts containing other shortcodes to work.

= 0.2.1 =
* Please upgrade and check the readme.txt file for a full description on using the plugin shortcode arguments

= 0.1 =
* Hey this is the first version.  No need to upgrade until a new version comes out