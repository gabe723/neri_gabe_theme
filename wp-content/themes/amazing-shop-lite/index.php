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
				<article class="post">
					<h2 class="entry-title">
						<a href="SINGLE_POST_URL">
							<?php the_title(); ?>
						</a>
					</h2>
					<div class="entry-content">
					</div>
					<div class="postmeta">
						<span class="author">by: USERNAME </span>
						<span class="date"> DATE </span>
						<span class="num-comments"> X COMMENTS </span>
						<span class="categories">
							<a href="#" title="View all posts in Updates" >
								<?php the_category(); ?>
							</a>
						</span>
						<span class="tags">TAG, TAG, TAG</span>
					</div>
					<!-- end .postmeta -->
				</article>
				<!-- end .post -->

				<?php
			} //end while

			platty_pagination();

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
