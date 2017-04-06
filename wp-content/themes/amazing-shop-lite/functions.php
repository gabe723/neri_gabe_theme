<?php
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
function amazing_shop_lite_ex_length(){
	//short excerpt on search results
	if( is_search() ){
		return 20; //words
	}else{
		return 75; //words
	}
}
add_filter( 'excerpt_length', 'amazing_shop_lite_ex_length'  );


function amazing_shop_lite_readmore(){
	return '<br><a href="' . get_permalink() . '" class="read-more" title="Keep Reading this post">Read More</a>';
}
add_filter( 'excerpt_more', 'amazing_shop_lite_readmore' );

/**
* Create two menu locations. Display them with wp_nav_menu() in your templates
*/
function amazing_shop_lite_menus(){
	register_nav_menus( array(
		'main_menu' 	=> 'Main Navigation',
		'social_menu' 	=> 'Social Media',
	) );
}
add_action( 'init', 'amazing_shop_lite_menus' );

/**
* Helper function to handle pagination. Call in any template file.
*/
function amazing_shop_lite_pagination(){
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
function amazing_shop_lite_widget_areas(){
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
add_action( 'widgets_init', 'amazing_shop_lite_widget_areas' );

/**
* Improve UX of replying to comments
*/
function amazing_shop_lite_comments_reply(){
	wp_enqueue_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'amazing_shop_lite_comments_reply' );

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
* A helper function to output the product's price. Call amazing_shop_lite_price() anywhere in the loop to use.
* Note: 'price' is a custom field.
* @return mixed. Displays HTML for the price tag.
*/
function amazing_shop_lite_price(){
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
	* A helper function to output the product's size. Call amazing_shop_lite_size() anywhere in the loop to use.
	* Note: 'size' is a custom field.
	* @return mixed. Displays HTML for the size tag.
	*/
	function amazing_shop_lite_size(){
		global $post;
		$size = get_post_meta( $post->ID, 'size', true );
		if ($size){
			?>
			<span class="size">
				Size: <?php echo $size; ?>
			</span>
			<?php }//end if size
		}

		/**
		* Customization API additions - custom colors, fonts, layouts, etc.
		*/
		add_action( 'customize_register', 'platty_customizer' );
		function platty_customizer( $wp_customize ){
			//register all sections, settings and controls here:

			//"accent color"
			$wp_customize->add_setting( 'accent_color', array(
				'default' => 'dodgerblue',
			) );
			//user interface for accent color
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
			'accent_color_control', array(
				'label'    => 'Accent Color',
				'section'  => 'colors', //this one is built in
				'settings' => 'accent_color', //added above
			) ) );

			//Layout options
			//create new section labeled "Layout"
			$wp_customize->add_section( 'platty_layout', array(
				'title'      => 'Layout',
				'capability' => 'edit_theme_options',
				'priority'   => 40,
			) );

			$wp_customize->add_setting( 'header_size', array(
				'default' => 'large',
			) );
			$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'header_size_control', array(
				'label'    => 'Header Height',
				'section'  => 'platty_layout',
				'settings' => 'header_size',
				'type'     => 'radio',
				'choices'  => array(
					'small'       => 'Small',
					'medium'      => 'Medium',
					'large'       => 'Large',
				),
			) ) );

			//Second Custom Logo
			$wp_customize->add_setting( 'secondary_logo' );

			$wp_customize->add_control( new WP_Customize_Cropped_Image_Control( $wp_customize,
			'secondary_logo_control', array(
				'label'    => 'Secondary Logo',
				'section'  => 'title_tagline', //built in "site identity" section
				'settings' => 'secondary_logo',
			) ) );
		} //end platty_customizer

		/**
		* Customized CSS - displays the customizer changes
		*/
		add_action( 'wp_head', 'platty_custom_css' );
		function platty_custom_css(){
			switch ( get_theme_mod('header_size') ){
				case 'small':
				$size = '20vh';
				break;

				case 'medium':
				$size = '30vh';
				break;

				default:
				$size = '40vh';
				break;
			} //end switch
			?>
			<style type='text/css'>
			#header .custom-logo-link{
				background-color: <?php echo get_theme_mod( 'accent_color' ); ?>;
			}
			#header{
				border-color: <?php echo get_theme_mod( 'accent_color' ); ?>;
			}
			@media screen and (min-width:700px){
				#header{
					min-height: <?php echo $size; ?>;
				}
			}
			</style>
			<?php
		}

		/**
		* Helper function to show custom secondary logo
		*/
		function amazing_shop_lite_logo(){
			$logo = get_theme_mod( 'secondary_logo' );
			if( $logo ){
				echo wp_get_attachment_image( $logo, 'thumbnail', array(
					'class' => 'secondary-logo',
				) );
			}
		}

//unhook WooCommerce wrappers
// remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
// remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);











		//no close php
