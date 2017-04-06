<?php
/**
* The sidebar containing the main widget area
* @package WordPress
* @subpackage Amazing_Shop_Lite
* @since 1.0
* @version 1.0
*/
?>

<aside id="sidebar">

	<?php
	//if there are widgets, show them, otherwise do fallback content
	//don't forget to call register_sidebar() in functions.php
	if( ! dynamic_sidebar('Blog Sidebar') ){
		?>

		<section id="categories" class="widget">
			<h3 class="widget-title"> Categories </h3>
			<ul>

				<?php
				//show the 15 most common categories, in a flat list
				wp_list_categories( array(
					'depth' 	   => -1,
					'title_li' 	 => '',
					'number' 	   => 15,
					'orderby' 	 => 'count', //order by number of posts
					'order'		   => 'DESC', //high numbers first
					'show_count' => true,
				) ); ?>

			</ul>
		</section>
		<section id="archives" class="widget">
			<h3 class="widget-title"> Monthly Archives </h3>
			<ul>

				<?php //list of all archives, by month
				wp_get_archives( array(
					'limit' => 10,
				) ); ?>

			</ul>
		</section>
		<section id="archives" class="widget">
			<h3 class="widget-title"> Yearly Archives </h3>
			<ul>

				<?php //list of all archives, by year
				wp_get_archives( array(
					'type'  => 'yearly',
					'limit' => 10,
				) ); ?>

			</ul>
		</section>
		<section id="tags" class="widget">
			<h3 class="widget-title"> Tags </h3>

			<?php
			wp_tag_cloud( array(
				// 'format' 	=> 'list',
				'smallest' => .8,
				'largest'  => 1.1,
				'unit' 		 => 'em',
				'number'	 => 20,
			) );
			?>

		</section>
		<section id="meta" class="widget">
			<h3 class="widget-title"> Meta </h3>
			<ul>
				<li><a href="#">Site Admin</a></li>
				<li><a href="#">Log out</a> </li>
			</ul>
		</section>

		<?php }//end if there are no widgets ?>

	</aside>
	<!-- end #sidebar -->
