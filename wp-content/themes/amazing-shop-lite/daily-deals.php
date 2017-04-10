<?php
/*
Template Name: Dialy Deals
*/
 
/**
* The "Daily Deals" page
*
* @link https://codex.wordpress.org/Template_Hierarchy
*
* @package WordPress
* @subpackage Amazing_Shop_Lite
* @since 1.0
* @version 1.0
*/

define( 'WP_USE_THEMES', false ); get_header();
?>
<div class="wrapper">
	<main id="content">
		<?php //check to see if the slider plugin exists before running its function
		if( function_exists( 'gn_slider' ) ){
			gn_slider();
		}
		?>

		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				?>

				<h1 class="page-title">
					Here are Today's Top Deals!
				</h1>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>

				<?php
			} // end while
		} // end if
		?>

		<?php
		//get up to 5 latest products
		$products = new WP_Query( array(
			'post_type'      => 'product',
			'posts_per_page' => 5,
		) );

		if ( $products->have_posts() ) {
			?>
			<div class="featured-products">
				<h2>Featured Products:</h2>
				<ul>
					<?php while( $products->have_posts() ){
						$products->the_post();
						?>
						<li>
							<a href="PERMALINK">
								<?php the_post_thumbnail( 'thumbnail' ); ?>
								<div class="caption">
									<h3><?php the_title(); ?></h3>
									<?php amazing_shop_lite_price(); ?>
								</div>
							</a>
						</li>
						<?php }//end while ?>
					</ul>
				</div>
				<?php }//end of custom "product" query loop ?>
			</main>
			<!-- end #content -->

			<?php get_sidebar(); ?>
		</div>
		<!-- end .wrapper -->

		<?php get_footer(); ?>
