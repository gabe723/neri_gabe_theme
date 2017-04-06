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

<footer id="colophon" role="contentinfo">

	<?php amazing_shop_lite_logo(); ?>
  <?php dynamic_sidebar( 'Footer Area' ); //registered in functions.php ?>

</footer><!-- #colophon -->
</div><!-- .site-content-contain -->
</div><!-- #page -->

<?php wp_footer(); //hook. REQUIRED for plugins and admin bar to work ?>

</body>
</html>
