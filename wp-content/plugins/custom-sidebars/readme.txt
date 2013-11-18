=== Plugin Name ===
Contributors: marquex
Donate link: http://marquex.es/541/custom-sidebars-plugin-v0-8
Tags: custom sidebars, widgets, sidebars, custom, sidebar, widget, personalize
Requires at least: 3.0
Tested up to: 3.3
Stable tag: trunk

Allows to create your own widgetized areas and custom sidebars, and select what sidebars to use for each post or page.
 
== Description ==

Sometimes it is necessary to show different elements on the sidebars for some posts or pages. The themes nowadays give you some areas to put the widgets, but those areas are common for all the posts that are using the same template. NOTE: **You need to use a theme that accepts widgets to make this plugin work.** 

Custom Sidebars allows you to create all the widgetized areas you need, your own custom sidebars, configure them adding widgets, and replace the default sidebars on the posts or pages you want in just few clicks.


You can also set new default sidebars for a group of posts or pages easily, keeping the chance of changing them individually.:

*	Sidebars for all the posts that belong to a category.
*	Sidebars for all the posts that belong to a post-type.
*	Sidebars for lists of posts (those which belongs to a category, post-type or have some tag).
*	Sidebars for author pages.
*	Sidebars for the main blog page.

I also recommend the use of [Widget Entries plugin](http://wordpress.org/extend/plugins/widget-entries/) to manage lots of HTML widgets with ease for your new sidebars. This way you will boost the content manager facet of your Wordpress installation.  

Translations are welcome! I will write your name down here if you donate your translation work. Thanks very much to:

*	English - marquex
*	Spanish - marquex
*	German - [Markus Vocke, Professionelles Webdesign](http://www.web-funk.de)
*	Dutch - Herman Boswijk

== Installation ==

There are two ways of installing the plugin:

**From the [WordPress plugins page](http://wordpress.org/extend/plugins/)**

1. Download the plugin
2. Upload the `custom-sidebars` folder to your `/wp-content/plugins/` directory.
3. Active the plugin in the plugin menu panel in your administration area.

**From inside your WordPress installation, in the plugin section.**

1. Search for custom sidebars plugin
2. Download it and then active it.

Once, you have the plugin activated, you will find a new option called 'Custom Sidebars' in your Appearance menu. There you will be able to create and manage your own sidebars.

You can find some simple tutorials on the [Custom sidebars plugin web page](http://marquex.posterous.com/pages/custom-sidebars)

== Frequently Asked Questions ==

= Why there are no asked questions in this section? =

Nobody has asked anything yet. I will fill this section with real questions.

= Some howtos =

You can find some simple tutorials on the [Custom sidebars plugin web page](http://marquex.posterous.com/pages/custom-sidebars)


== Screenshots ==

1. screenshot-1.png The plugin options page. Placed in the appearance menu, you can create, edit or delete sidebars there, set the replaceable sidebars and reset the sidebars data. 
2. screenshot-2.png The new sidebars created by the plugin, can be customized in the Widgets menu.
3. screenshot-3.png A new box is added to the post and page edit forms, where you can set your custom sidebars up.
4. screenshot-4.png Default sidebars page, here you will be able to assign sidebars to all the post that belongs to a category or a post-type. Also author, tags and main blog pages sidebars can be defined here.
5. screenshot-5.png The sidebar sb1 has replace the sidebar footer 1 in the front-end.

== Changelog ==
= 0.8.2 =
* 	Fixed: Problems with spanish translation
*	Added: Dutch and German language files
* 	Fixed: Some css issues with WP3.3

= 0.8.1 =
*	Fixed: You can assign sidebars to your pages again.

= 0.8 =
*	Fixed: Category hierarchy is now handled properly by the custom sidebars plugin.
*	Added: Sidebars can be set for every custom post type post individually.
*	Improved the way it replace the sidebars.
*	Improved some text and messages in the back-end.

= 0.7.1 =
* 	Fixed: Now the plugin works with themes like Thesis that don't use the the_header hook. Changed the hook where execute the replacement code to wp_head.
*	Fixed: When a second sidebar is replaced with the originally first sidebar, it is replaced by the first sidebar replacement instead. 

= 0.7 =
*	Fixed: Bulk and Quick editing posts and pages reset their custom sidebars.
*	Changed capability needed to switch_themes, and improved capability management.

= 0.6 =

*	New interface, more user friendly
*	Added the possibility of customize the main blog page sidebars
*	Added the sidebars by category, so now you can personalize all the post that belongs to a category easily in a hierarchycal way
*	Added the possibility of customize the authors page sidebars
*	Added the possibility of customize the tags page sidebars
*	Added, now it is possible to edit the sidebars names, as well as the pre-widget, post-widget, pre-title, post-title for a sidebar.
*	Added the possibility of customize the sidebars of posts list by category or post-type.


= 0.5 =

*	Fixed a bug that didn't allow to create new bars when every previous bars were deleted.
*	Fixed a bug introduced in v0.4 that did not allow to assign bars per post-types properly
*	Added an option to remove all the Custom Sidebars data from the database easily.

= 0.4 =

*	Empty sidebars will now be shown as empty, instead of displaying the theme's default sidebar.

= 0.3 =

*	PHP 4 Compatible (Thanks to Kay Larmer)
*	Fixed a bug introduced in v0.2 that did not allow to save the replaceable bars options

= 0.2 =

*	Improved security by adding wp_nonces to the forms.
*	Added the pt-widget post type to the ignored post types.
*	Improved i18n files.
*	Fixed screenshots for documentation.

= 0.1 =

*	Initial release

== Upgrade Notice ==

= 0.7.1 =
Now custom sidebars works with Thesis theme and some minor bugs have been solved.

= 0.7 =
This version fix a bug of v0.6 and before that reset the custom sidebars of posts and pages when they are quick edited or bulk edited, so upgrade is recommended.
This version also changes the capability for managing custom sidebars to 'switch_themes' the one that allows to see the appearance menu in the admin page. I think the plugin is more coherent this way, but anyway it is easy to modify under plugin edit.

= 0.6 =
This version adds several options for customize the sidebars by categories and replace the default blog page sidebars. Now it's possible to edit sidebar properties. Also fixes some minor bugs.



