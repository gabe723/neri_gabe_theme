<?php
/*
Plugin Name: GN Product Post Type
Description: Registers the post type and taxomonies for the shop
Author: Gabe Neri
Version: 0.1
License: GPLv3
*/

//Register the post type
add_action('init', 'gn_post_type_register');
function gn_post_type_register(){
  register_post_type( 'product', array(
    'public'        => true,
    'has_archive'   => true,
    'menu_icon'     => 'dashicons-cart',
    'menu_position' => 5,
    'rewrite'       => array('slug' => 'shop'),
    'supports'      => array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt', 'comments', 'revisions'),
    'labels'        => array(
      'name'             => 'Products',
      'singular_name'    => 'Product',
      'not_found'        => 'No Product Found',
      'add_new_item'     => 'Add New Product',
      'search_items'     => 'Search Products',
    ),
  ) );
  //Attach "brands" taxomony to our products
  register_taxonomy( 'brand', 'product', array(
    'hierarchical'      => true,
    'show_admin_column' => true,
    'labels'            => array(
      'name'                 => 'Brands',
      'singular_name'        => 'Brand',
      'add_new_item'         => 'Add New Brand',
      'not_found'            => 'No brands found.',
      'search_items'         => 'Search Brands',
    ),
  ) );
  //Attach "brands" taxomony to our products
  register_taxonomy( 'feature', 'product', array(
    'hierarchical'      => false,
    'show_admin_column' => true,
    'labels'            => array(
      'name'                 => 'Features',
      'singular_name'        => 'Feature',
      'add_new_item'         => 'Add New Feature',
      'not_found'            => 'No features found.',
      'search_items'         => 'Search Features',
    ),
  ) );
}

/**
* Flush permalinks (rewrite rules) when this plugin is activated
* Prevents 404 errors
*/
function gn_flush(){
  gn_post_type_register();
  flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'gn_flush' );
