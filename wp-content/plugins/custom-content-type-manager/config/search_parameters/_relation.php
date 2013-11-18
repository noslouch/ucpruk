<?php
if ( ! defined('CCTM_PATH')) exit('No direct script access allowed');
/*------------------------------------------------------------------------------
Search Parameters Configuration

This file sets out configuration details for the "Search Parameters" forms
generated when you edit or create a custom field definition.  The available
search parameters here will determine what search options are displayed to the 
users in the WordPress dashboard when they are defining search criteria for 
this relation field.  

This file should ONLY populate the CCTM::$search_by array, e.g.

	CCTM::$search_by[] = 'post_type';
	CCTM::$search_by[] = 'taxonomy';
	// ... etc... 
	
For example, if you need your users to select draft posts when they select a
related post (instead of just published posts), you can make this search option
appear when you define the field's search parameters by adding the 'post_status'
parameter to the CCTM::$search_by array, e.g.

CCTM::$search_by[] = 'post_status';

The options available as parameters include any option available to the 
GetPostsQuery::get_posts() function:

http://code.google.com/p/wordpress-summarize-posts/wiki/get_posts
------------------------------------------------------------------------------*/

CCTM::$search_by[] = 'post_type';
CCTM::$search_by[] = 'taxonomy';
CCTM::$search_by[] = 'taxonomy_term';
CCTM::$search_by[] = 'post_parent';
CCTM::$search_by[] = 'meta_key';
CCTM::$search_by[] = 'meta_value';


/*EOF*/