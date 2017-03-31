<?php
/**
 * Amazing Shop Lite functions and definitions
 * @package WordPress
 * @subpackage Amazing_Shop_Lite
 * @since 1.0
 */
 
//turn on sleeping features
//featured image support:
add_theme_support('post-thumbnails');

add_theme_support( 'post-formats', array( 'quote', 'link', 'audio', 'video', 'image',
'gallery', 'aside', 'status' ) );

add_theme_support( 'custom-background' );

//don't forget to show the header image in the header.php file
add_theme_support( 'custom-header', array(
	'width' 		=> 960,
	'height' 		=> 700,
) );

//don't forget the_custom_logo() to display it in your theme
add_theme_support( 'custom-logo', array(
	'width' 		=> 180,
	'height' 		=> 50,
) );

//better RSS feed links. a must-have if you use the blog
add_theme_support( 'automatic-feed-links' );

//improve the markup of WordPress generated code
add_theme_support( 'html5', array('search-form', 'comment-list', 'comment-form',
'gallery', 'caption', ) );

//improve title tag for SEO. Remove <title> from header.php
add_theme_support( 'title-tag' );

//auto embed max width
if ( ! isset( $content_width ) ) $content_width = 735; //measurement is in px

//editor-style.css
add_editor_style();

/**
* Make the excerpts better - customize the number of words and change [...]
* @see https://developer.wordpress.org/reference/functions/the_excerpt/
*/
function platty_ex_length(){
	//short excerpt on search results
	if( is_search() ){
		return 20; //words
	}else{
		return 75; //words
	}
}
add_filter( 'excerpt_length', 'platty_ex_length'  );


function platty_readmore(){
	return '<br><a href="' . get_permalink() . '" class="read-more" title="Keep Reading this post">Read More</a>';
}
add_filter( 'excerpt_more', 'platty_readmore' );

/**
* Create two menu locations. Display them with wp_nav_menu() in your templates
*/
function platty_menus(){
	register_nav_menus( array(
		'main_menu' 	=> 'Main Navigation',
		'social_menu' 	=> 'Social Media',
	) );
}
add_action( 'init', 'platty_menus' );

/**
* Helper function to handle pagination. Call in any template file.
*/
function platty_pagination(){
	if( ! is_singular() ){
		//archive pagination
		if( function_exists('the_posts_pagination') ){
			the_posts_pagination();
		}else{
			echo '<div class="pagination">';
			next_posts_link( '&larr; Older Posts' );
			previous_posts_link( 'Newer Posts &rarr;' );
			echo '</div>';
		}
	}else{
		//single pagination
		echo '<div class="pagination">';
		previous_post_link( '%link', '&larr; %title' );  //one older post
		next_post_link( '%link', '%title &rarr;' );		//one newer post
		echo '</div>';
	}
}

/**
* Register Widget Areas (Dynamic Sidebars)
* Call dynamic_sidebar() in your templates to display them
*/
function platty_widget_areas(){
	register_sidebar( array(
		'name' 			    => 'Blog Sidebar',
		'id'			      => 'blog-sidebar',
		'description' 	=> 'Appears next to blog and archive content',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'	=> '</section>',
		'before_title'	=> '<h3 class="widget-title">',
		'after_title' 	=> '</h3>',
	) );
	register_sidebar( array(
		'name' 			    => 'Footer Area',
		'id'			      => 'footer-area',
		'description' 	=> 'Appears at the bottom of every page',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'	=> '</section>',
		'before_title'	=> '<h3 class="widget-title">',
		'after_title' 	=> '</h3>',
	) );
	register_sidebar( array(
		'name' 			    => 'Home Area',
		'id'			      => 'home-area',
		'description' 	=> 'Appears in the middle of the home page',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'	=> '</section>',
		'before_title'	=> '<h3 class="widget-title">',
		'after_title' 	=> '</h3>',
	) );
}
add_action( 'widgets_init', 'platty_widget_areas' );

/**
* Improve UX of replying to comments
*/
function platty_comments_reply(){
	wp_enqueue_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'platty_comments_reply' );

/**
* Fix the comments number issue (remove trackbacks and pingbacks from comment count)
*/
add_filter('get_comments_number', 'comment_count', 0);
function comment_count( $count ) {
	if ( ! is_admin() ) {
		global $id;
		$comments_by_type = &separate_comments(get_comments('status=approve&post_id=' . $id));
		return count($comments_by_type['comment']);
	} else {
		return $count;
	}
}

/**
* A helper function to output the product's price. Call platty_price() anywhere in the loop to use.
* Note: 'price' is a custom field.
* @return mixed. Displays HTML for the price tag.
*/
function platty_price(){
	global $post;
	$price = get_post_meta( $post->ID, 'price', true );
	if ($price){
		?>
		<span class="price">
			<?php echo $price; ?>
		</span>
		<?php }//end if price
	}

	/**
	* A helper function to output the product's size. Call platty_size() anywhere in the loop to use.
	* Note: 'size' is a custom field.
	* @return mixed. Displays HTML for the size tag.
	*/
	function platty_size(){
		global $post;
		$size = get_post_meta( $post->ID, 'size', true );
		if ($size){
			?>
			<span class="size">
				Size: <?php echo $size; ?>
			</span>
			<?php }//end if size
		}













	//no close php
