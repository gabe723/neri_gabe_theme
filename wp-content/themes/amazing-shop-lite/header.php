<?php
/**
* The header for our theme
* This is the template that displays all of the <head> section and everything up until <div id="content">
* @package WordPress
* @subpackage Amazing_Shop_Lite
* @since 1.0
* @version 1.0
*/
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Lato:400,400i,700|Playfair+Display:400,900">
	<link rel="stylesheet" type="text/css" href="<?php echo get_stylesheet_uri(); ?>">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php wp_head(); //hook. REQUIRED for plugins and admin bar to work ?>
</head>

<body <?php body_class(); ?>>
	<div id="page" class="site">
		<header role="banner" id="header">
			<div class="header-bar">

				<?php
				//custom-logo activated in functions.php
				if( function_exists('the_custom_logo') ){
					if( has_custom_logo() ){
						the_custom_logo();
					}else{
						//title of the site
						?>
						<h1 class="site-title">
							<a href="<?php echo home_url(); ?>">
								<?php bloginfo( 'name' ); ?>
							</a>
						</h1>
						<?php
					}
				}
				?>

				<h2><?php bloginfo( 'description' ); ?></h2>

				<?php wp_nav_menu( array(
					'theme_location' => 'main_menu',
					'container' 		 => 'nav', //div, nav or false
					'menu_class' 		 => 'menu', //ul class="menu"
				) ); ?>

				<?php get_search_form(); ?>
			</div>
		</header><!-- #masthead -->
		<div class="site-content-contain">
			<div id="content" class="site-content">
