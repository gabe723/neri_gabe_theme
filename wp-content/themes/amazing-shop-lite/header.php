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
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<div id="page" class="site">
		<header role="banner" id="header">
			<div class="header-bar">
				<h1 class="site-title"><a href="path/to/frontpage">SITE NAME</a></h1>
				<h2>SITE DESCRIPTION</h2>
				<nav>
					<ul class="nav">
						<li><a href="#">Home</a></li>
						<li><a href="#">About</a></li>
						<li><a href="#">Services</a>
							<ul>
								<li><a href="">dropdown1</a></li>
								<li><a href="">dropdown2</a></li>
								<li><a href="">dropdown3</a></li>
							</ul>
						</li>
						<li><a href="#">Gallery</a></li>
						<li><a href="#">Blog</a></li>
					</ul>
				</nav>

				<form method="get" action="#">
					<label>Search for:</label>
					<input type="text" />
					<input type="submit" value="Search" />
				</form>
			</div>
		</header><!-- #masthead -->
		<div class="site-content-contain">
			<div id="content" class="site-content">
