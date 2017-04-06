<?php
/**
* The main template file
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

get_header(); ?>
<div class="wrapper">
	<main id="content">
		<?php
		if( have_posts() ){
			while( have_posts() ){
				the_post();
				?>
				<article <?php post_class(); ?>>
					<h2 class="entry-title">
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
					</h2>

					<?php
					//use "$wp_query" when in the main loop
					if ( $wp_query->current_post == 0 ) {
						the_post_thumbnail( 'large' );
					}else {
						the_post_thumbnail( 'thumbnail' );
					}
					?>

					<div class="entry-content">
						<?php
						if( is_singular() ){
							//single post, page, attachment, etc
							the_content();
							wp_link_pages( array(
								'before' => '<div class="pagination">Pages:',
								'after'	=> '</div>',
								'pagelink' => '<span>%</span>',
								'next_or_number' => 'next',
							) );
						}else{
							//not singular : archives, blog, search results
							the_excerpt();
						}
						?>
					</div>

					<div class="postmeta">
						<span class="author">by: <?php the_author(); ?> </span>
						<span class="date"> <?php the_date(); ?> </span>
						<span class="num-comments"> (<?php comments_number(); ?>) Comments </span>
						<span class="categories">
							<a href="#" title="View all posts in Updates" >
								<?php the_category(); ?>
							</a>
						</span>
						<span class="tags"><?php the_tags(); ?></span>
					</div>
					<!-- end .postmeta -->
				</article>
				<!-- end .post -->

				<?php
			} //end while

			amazing_shop_lite_pagination();

			comments_template( '/comments.php', true ); //include comments.php or WP default

		}//end if there are posts
		else{
			echo 'Sorry, no posts to show';
		}
		?>

	</main>
	<!-- end #content -->

	<?php get_sidebar(); ?>
</div>
<!-- end .wrapper -->

<?php get_footer(); ?>
