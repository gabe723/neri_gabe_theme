<?php
/**
* The template for displaying the footer
* Contains the closing of the #content div and all content after.
* @package WordPress
* @subpackage Amazing_Shop_Lite
* @since 1.0
* @version 1.0
*/
?>

<footer id="colophon" class="cf">

	<?php amazing_shop_lite_logo(); ?>
	<?php dynamic_sidebar( 'Footer Area' ); //registered in functions.php ?>

</footer>
<!-- #colophon -->
</div>
<!-- #site-content -->
</div>
<!-- .site-content-contain -->

<?php wp_footer(); //hook. REQUIRED for plugins and admin bar to work ?>

</div>
<!-- #page -->
</body>
</html>
