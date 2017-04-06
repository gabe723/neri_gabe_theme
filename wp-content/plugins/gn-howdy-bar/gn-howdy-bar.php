<?php
/*
Plugin Name: MMC Howdy Bar
Description: Our First plugin, just for practice
Author: Melissa Cabral
License: GPLv3
Version: 0.1
*/

/**
 * HTML markup for the bar
 */
function mmc_howdy_html(){
  ?>
  <div class="howdy-bar">
    This is the Howdy Bar
    <a href="#" class="howdy-button">Click Me!</a>

    <span class="howdy-dismiss">×</span>
  </div>
  <?php
}
add_action( 'wp_footer', 'mmc_howdy_html' );

/**
 * Attach a stylesheet & a JS file
 */
function mmc_howdy_scripts(){
  //get the url to the stylesheet
  $stylesheet = plugins_url( 'css/howdy.css', __FILE__ );
  //tell WP to put it on the page
  wp_enqueue_style( 'howdy-css', $stylesheet );

  //load jquery
  wp_enqueue_script('jquery');

  //get the url of the JS file
  $script = plugins_url( 'js/howdy.js', __FILE__ );
  //tell WP to put it on the page
  wp_enqueue_script( 'howdy-js', $script, array('jquery'), '0.1', true );
}
add_action( 'wp_enqueue_scripts', 'mmc_howdy_scripts' );
