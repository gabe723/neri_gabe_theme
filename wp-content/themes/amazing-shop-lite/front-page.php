<?php
/**
* The front page of our website
*
* This is the most generic template file in a WordPress theme
* and one of the two required files for a theme (the other being style.css).
* It is used to display a page when nothing more specific matches a query.
* E.g., it puts together the home page when no home.php file exists.
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

		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				?>

				<h1 class="page-title">
					We Have What You Need.
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
			'posts_per_page' => 12,
		) );

		if ( $products->have_posts() ) {
			?>
			<div class="featured-products">
				<h2>Featured Products</h2>
				<ul>
					<?php while( $products->have_posts() ){
						$products->the_post();
						?>
						<li>
							<a href="<?php the_permalink(); ?>">
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

		</div>
		<!-- end .wrapper -->

		<?php get_footer(); ?>
