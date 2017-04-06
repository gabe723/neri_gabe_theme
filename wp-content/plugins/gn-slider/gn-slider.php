<?php
/*
Plugin Name: GN Slider
Description: A simple JS demo
Author: Gabe Neri
Version: 0.1
License: GPLv3
*/

/**
* Register the "slides" post type
*/
add_action( 'init', 'gn_slide_cpt' );
function gn_slide_cpt(){
  register_post_type( 'slide', array(
    'public'              => true,
    'exclude_from_search' => true,
    'menu_icon'           => 'dashicons-images-alt2',
    'supports'            => array( 'title', 'editor', 'thumbnail' ),
    'labels'              => array(
      'name'          => 'Slides',
      'singular_name' => 'Slide',
      'add_new_item'  => 'Add New Slide',
      'not_found'     => 'No Slides Found',
    ),
  ) );
                   //name       w     h    crop?
  add_image_size( 'wide_slide', 1100, 350, true );
}

add_filter( 'manage_posts_columns', 'posts_columns' );

/**
* Display the featured image in the admin panel so we can see the slide images! *This works on ALL post types
* @link https://wisdmlabs.com/blog/add-featured-image-column-admin-dashboard/
*/
add_action( 'manage_posts_custom_column', 'posts_custom_columns' );
function posts_columns($defaults){
  $defaults['wdm_post_thumbs'] = __( 'Featured Image' ); //name of the column
  return $defaults;
}
function posts_custom_columns($column_name){
  if( $column_name === 'wdm_post_thumbs' ){
    echo the_post_thumbnail( array(100,100) ); //size of the thumbnail
  }
}


/**
* DISPLAY HTML
* @link http://responsiveslides.com/
*/
function gn_slider(){
  //custom query to get up to 5 slides
  $slides = new WP_Query( array(
    'post_type'     => 'slide',
    'post_per_page' => 5,
  ) );

  if( $slides->have_posts() ){
    ?>
    <div id="gn-slider">
      <ul class="slides">
        <?php while( $slides->have_posts() ){
          $slides->the_post();
          ?>
          <li>
            <?php the_post_thumbnail( 'wide_slide' ); ?>
            <div class="slide-info">
              <h2><?php the_title(); ?></h2>
              <?php the_content(); ?>
            </div>
          </li>
          <?php } //end while ?>
        </ul>
      </div>
      <?php
    } //end if
  } //end gn_slider()


  /**
  * ATTACH STYLES AND SCRIPTS
  */
  add_action( 'wp_enqueue_scripts', 'gn_scripts' );
  function gn_scripts(){
    //CSS url (if you're working in a theme, use get_stylesheet_directory_uri instead of plugins_url)
    $css = plugins_url( 'slider.css', __FILE__ );
    wp_enqueue_style( 'gn_slider_css', $css );

    //ATTACH jQuery
    wp_enqueue_script( 'jquery' );

    //ATTACH responsive-slides.js
    $rs = plugins_url( 'responsive-slides.js', __FILE__ );
    wp_enqueue_script( 'responsive-slides', $rs );

    //ATTACH custom script
    $custom_script = plugins_url( 'activate-slider.js', __FILE__ );
    wp_enqueue_script( 'gn_slider_js', $custom_script );
  }
