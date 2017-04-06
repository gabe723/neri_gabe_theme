<?php
/*
Plugin Name: GN Login Form
Description: Customize the Login form, register form, and admin screens
Author: Gabe Neri
Version: 0.1
License: GPLv3
*/

/**
* Style the login, register, and forgot password forms with an external stylesheet
*/
function gn_login_style(){
  $style_url = plugins_url( 'login.css', __FILE__ );
  //handle,    url
  wp_enqueue_style( 'login_css', $style_url );
}
add_action( 'login_enqueue_scripts', 'gn_login_style' );

//change the "wordpress.org" link on the login logo
function gn_login_logo_link(){
  return home_url(); //any valid URL can go here
}
add_filter( 'login_headerurl', 'gn_login_logo_link' );

//change the tooltip on the login Logo
function gn_login_logo_title(){
  return 'Go back to ' . get_bloginfo( 'name' );
}
add_filter( 'login_headertitle', 'gn_login_logo_title' );

/**
* Customize the Admin Bar (tool bar)
* @see https://codex.wordpress.org/Toolbar
* @param $wp_admin_bar The global admin bar object
*/
function gn_modify_toolbar( $wp_admin_bar ){
  //Get rid of WP Logo and its dropdown menu
  $wp_admin_bar->remove_node( 'wp-logo' );
  //add custom "help" menu
  $wp_admin_bar->add_node( array(
    'id'    => 'gn-help-menu',
    'title' => 'Contact Gabe',
    'href'  => 'http://gabeneri.com',
  ) );
}
add_action( 'admin_bar_menu', 'gn_modify_toolbar', 999 );

/**
 * Remove Dashboard widgets and add our own
 */
 function gn_dashboard_widgets(){
                    //$id                      $screen      $context
   remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
   remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
   //$id, $title, $callback, $screen, $context, $priority, $callback_args
   add_meta_box( 'dashboard_gn_help', 'Helpful Resources', 'gn_dash_content', 'dashboard', 'side', 'high' );
 }
 add_action( 'admin_init', 'gn_dashboard_widgets' );

 //callback for the widget content
 function gn_dash_content(){
   echo '<div style="position:relative;height:0;padding-bottom:56.25%"><iframe src="https://www.youtube.com/embed/uwan-ofPGqQ?ecver=2" width="640" height="360" frameborder="0" style="position:absolute;width:100%;height:100%;left:0" allowfullscreen></iframe></div>';
 }
