<?php

/**

 * The Header for our theme.

 *

 * Displays all of the <head> section and everything up till <div id="main">

 *

 * @package WordPress

 * @subpackage Colorway

 * @since Colorway 1.0

 */

?>

<!DOCTYPE html>

<html <?php language_attributes(); ?>>

<head>

<meta charset="<?php bloginfo( 'charset' ); ?>" />

<title>

<?php

	/*

	 * Print the <title> tag based on what is being viewed.

	 */

	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.

	bloginfo( 'name' );

	// Add the blog description for the home/front page.

	$site_description = get_bloginfo( 'description', 'display' );

	if ( $site_description && ( is_home() || is_front_page() ) )

		echo " | $site_description";

	// Add a page number if necessary:

	if ( $paged >= 2 || $page >= 2 )

		echo ' | ' . sprintf( 'Page %s', max( $paged, $page ) );

?>

</title>

<link rel="profile" href="http://gmpg.org/xfn/11" />

<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />

<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<?php

	/* We add some JavaScript to pages with the comment form

	 * to support sites with threaded comments (when in use).

	 */

	if ( is_singular() && get_option( 'thread_comments' ) )

		wp_enqueue_script( 'comment-reply' );

	/* Always have wp_head() just before the closing </head>

	 * tag of your theme, or you will break many plugins, which

	 * generally use this hook to add elements to <head> such

	 * as styles, scripts, and meta tags.

	 */

	wp_head();

?>

<!--[if gte IE 9]>

	<script type="text/javascript">

		Cufon.set('engine', 'canvas');

	</script>

<![endif]-->


<link href='http://fonts.googleapis.com/css?family=Francois+One' rel='stylesheet' type='text/css'>



<?php 

if(is_home() || is_front_page())

{?>

<style type="text/css">

body { background-image:url(http://ucpruk.org/wp-content/uploads/2012/01/home-background1.jpg);background-color:#084180; }

</style>

<?php } else { ?>

<style type="text/css">

body { background-image:url(http://ucpruk.org/wp-content/uploads/2012/01/page-background.jpg);background-color:#eae9e9; }

</style>

<?php } ?>

</head>



<body <?php body_class($class); ?>> 

<!--Start Container Div-->

<div class="container_24 container">

<!--Start Header Grid-->

<div class="grid_24 header">

  <div class="logo"> <a href="<?php echo home_url(); ?>"><img src="<?php if ( inkthemes_get_option('colorway_logo') !='' ) {?><?php echo inkthemes_get_option('colorway_logo'); ?><?php } else {?><?php echo get_template_directory_uri(); ?>/images/logo.png<?php }?>" alt="<?php bloginfo('name'); ?>" /></a> </div>

  <div id="naviuser"><a href="http://ucpruk.org/" class="usaflag"><img src="<?php echo get_template_directory_uri(); ?>/images/usaflag.gif" /></a><a href="http://ucpruk.org/ruk" class="indoflag"><img src="<?php echo get_template_directory_uri(); ?>/images/indoflag.gif" /></a><a href="http://ucpruk.org/contact-us/" class="users" style="padding-left:15px;border-left:2px solid #fff;">Contact Us</a><a href="http://ucpruk.org/login/" class="users">Log In</a></div>

  <!--Start MenuBar-->

  <div class="menu-bar">

    <div id="MainNav">

      <?php inkthemes_nav(); ?>

    </div>

  </div>

  <!--End MenuBar-->

</div>

<div class="clear"></div>

<!--End Header Grid-->
