<?php
/*
Plugin Name: Security Ninja
Plugin URI: https://wpsecurityninja.com/
Description: Check your site for <strong>security vulnerabilities</strong> and get precise suggestions for corrective actions on passwords, user accounts, file permissions, database security, version hiding, plugins, themes and other security aspects.
Author: Web factory Ltd
Version: 2.15
Author URI: http://www.webfactoryltd.com/


  Copyright 2012 - 2016  Web factory Ltd  (email : securityninja@webfactoryltd.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// this is an include only WP file
if (!defined('ABSPATH')) {
  die;
}

// constants
define('WF_SN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WF_SN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WF_SN_BASE_FILE', basename(__FILE__));
define('WF_SN_RESULTS_KEY', 'wf_sn_results');
define('WF_SN_OPTIONS_KEY', 'wf_sn_options');
define('WF_SN_POINTERS_KEY', 'wf_sn_pointers');
define('WF_SN_MAX_EXEC_SEC', 200);


require_once WF_SN_PLUGIN_DIR . 'sn-tests.php';


class wf_sn {
  static $version;
  static $skip_tests = array();
  
  
  // get plugin version from header
  static function get_plugin_version() {
    $plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');
    wf_sn::$version = $plugin_data['version'];
    
    return $plugin_data['version'];
  } // get_plugin_version

  
  // init plugin
  static function init() {
    if (!version_compare(get_bloginfo('version'), '4.0',  '>=')) {
      add_action('admin_notices', array(__CLASS__, 'min_version_notice'));
      return;
    }
    
    // does the user have enough privilages to use the plugin?
    if (is_admin() && current_user_can('administrator')) {
      // add menu item to tools
      add_action('admin_menu', array(__CLASS__, 'admin_menu'));

      // aditional links in plugin description
      add_filter('plugin_action_links_' . basename(dirname(__FILE__)) . '/' . basename(__FILE__),
                 array(__CLASS__, 'plugin_action_links'));
      add_filter('plugin_row_meta', array(__CLASS__, 'plugin_meta_links'), 10, 2);

      // enqueue scripts
      add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));

      // register endpoints
      add_action('wp_ajax_sn_run_tests', array(__CLASS__, 'run_tests'));
      add_action('wp_ajax_sn_hide_tab', array(__CLASS__, 'hide_tab'));
      add_action('wp_ajax_sn_dismiss_pointer', array(__CLASS__, 'dismiss_pointer_ajax'));

      // warn if tests were not run
      add_action('admin_notices', array(__CLASS__, 'run_tests_notice'));
      
      // promo notices
      add_action('admin_notices', array(__CLASS__, 'promo_notice'));

      // warn if Wordfence is active
      add_action('admin_notices', array(__CLASS__, 'wordfence_notice'));

      // add_markup for UI overlay
      add_action('admin_footer', array(__CLASS__, 'admin_footer'));
    } // if is_admin
  } // init

  
  // some things have to be loaded earlier
  static function plugins_loaded() {
    wf_sn::get_plugin_version();
  } // plugins_loaded
  

  // add links to plugin's description in plugins table
  static function plugin_meta_links($links, $file) {
    $support = '<a target="_blank" href="https://wordpress.org/support/plugin/security-ninja" title="Problems? We\'re here to help">Support</a>';
    $buy = '<a target="_blank" href="' . self::generate_sn_web_link('plugins_table') . '" title="Security Ninja PRO has 4 extra modules, check them out">Buy PRO</a>';

    if ($file == plugin_basename(__FILE__)) {
      $links[] = $support;
      $links[] = $buy;
    }

    return $links;
  } // plugin_meta_links


  // add settings link to plugins page
  static function plugin_action_links($links) {
    $settings_link = '<a href="tools.php?page=wf-sn" title="Security Ninja">Analyze Site</a>';
    array_unshift($links, $settings_link);

    return $links;
  } // plugin_action_links


  // test if we're on plugin's page
  static function is_plugin_page() {
    $current_screen = get_current_screen();

    if ($current_screen->id == 'tools_page_wf-sn') {
      return true;
    } else {
      return false;
    }
  } // is_plugin_page


  // hide any add-on tab
  static function hide_tab() {
    check_ajax_referer('wf_sn_hide_tab');
    
    $tab = trim(@$_POST['tab']);
    $tabs = get_transient('wf_sn_hidden_tabs');
    $tabs[] = $tab;

    set_transient('wf_sn_hidden_tabs', $tabs, DAY_IN_SECONDS * 120);

    wp_send_json_success();
  } // hide_tab


  // enqueue CSS and JS scripts on plugin's pages
  static function enqueue_scripts() {
    if (wf_sn::is_plugin_page()) {
      wp_enqueue_script('jquery-ui-tabs');
      wp_enqueue_script('jquery-ui-dialog');
      wp_enqueue_script('sn-jquery-plugins', WF_SN_PLUGIN_URL . 'js/sn-jquery-plugins.js', array(), wf_sn::$version, true);
      wp_enqueue_script('sn-js', WF_SN_PLUGIN_URL . 'js/sn-common.js', array(), wf_sn::$version, true);
      wp_enqueue_style('sn-css', WF_SN_PLUGIN_URL . 'css/sn-style.css', array(), wf_sn::$version);
      wp_enqueue_style('wp-jquery-ui-dialog');
    } // if SN page
    
    $pointers = get_option(WF_SN_POINTERS_KEY);
    if (!empty($pointers) && !wf_sn::is_plugin_page()) {
      wp_enqueue_script('wp-pointer');
      wp_enqueue_script('wf-sn-pointers', plugins_url('js/sn-pointers.js', __FILE__), array('jquery'), wf_sn::$version, true);
      wp_enqueue_style('wp-pointer');
      wp_localize_script('wp-pointer', 'wf_sn_pointers', $pointers);
    }
    
    if (wf_sn::is_plugin_page() || $pointers) {
      $js_vars = array('sn_plugin_url' => WF_SN_PLUGIN_URL,
                       'nonce_run_tests' => wp_create_nonce('wf_sn_run_tests'),
                       'nonce_refresh_update' => wp_create_nonce('wf_sn_refresh_update'),
                       'nonce_dismiss_pointer' => wp_create_nonce('wf_sn_dismiss_pointer'),
                       'nonce_hide_tab' => wp_create_nonce('wf_sn_hide_tab'));
      
      wp_localize_script('jquery', 'wf_sn', $js_vars);
    }
  } // enqueue_scripts
  
  
  // permanently dismiss pointer
  static function dismiss_pointer_ajax() {
    check_ajax_referer('wf_sn_dismiss_pointer');
    
    $pointers = get_option(WF_SN_POINTERS_KEY);
    $pointer = trim($_POST['pointer']);

    if (empty($pointers) || empty($pointers[$pointer])) {
      wp_send_json_error();
    }

    unset($pointers[$pointer]);
    update_option(WF_SN_POINTERS_KEY, $pointers);
    
    wp_send_json_success();
  } // dismiss_pointer_ajax


  // add entry to admin menu
  static function admin_menu() {
    add_management_page('Security Ninja', 'Security Ninja', 'manage_options', 'wf-sn', array(__CLASS__, 'main_page'));
  } // admin_menu


  // display warning if test were never run
  static function run_tests_notice() {
    if (!wf_sn::is_plugin_page()) {
      return;
    }
    
    $tests = get_option(WF_SN_RESULTS_KEY, array());

    // temporarily disabled
    if (0 && empty($tests['last_run'])) {
      echo '<div class="notice notice-error"><p>Security Ninja <strong>tests were never run.</strong> Click "Analyze Site" to run them now and analyze your site for security vulnerabilities.</p></div>';
    } elseif (!empty($tests['last_run']) && (current_time('timestamp') - DAY_IN_SECONDS * 30) > $tests['last_run']) {
      echo '<div class="notice notice-error"><p>Security Ninja <strong>tests were not run for more than 30 days.</strong> It\'s advisable to run them once in a while. Click "Analyze Site" to run them now and analyze your site for security vulnerabilities.</p></div>';
    }
  } // run_tests_notice


  // display warning if Wordfence plugin is active
  static function wordfence_notice() {
    if (!wf_sn::is_plugin_page()) {
      return;
    }
    
    if (defined('WORDFENCE_VERSION') && WORDFENCE_VERSION) {
      echo '<div class="notice notice-error"><p>Please <strong>deactivate Wordfence</strong> before running Security Ninja tests. Some tests are detected as site attacks by Wordfence and hence can\'t be performed properly. Activate Wordfence once you\'re done testing.</p></div>';
    }
  } // wordfence_notice
  
  
  // display promo offer for a short period of time
  static function promo_notice() {
    if (!wf_sn::is_plugin_page()) {
      return;
    }
    
    $promo_delta = 1 * HOUR_IN_SECONDS;
    $options = get_option(WF_SN_OPTIONS_KEY);
    
    if (current_time('timestamp') - $options['first_install'] < $promo_delta) {
      $time = date(get_option('time_format'), $options['first_install'] + $promo_delta);
      echo '<div class="notice notice-error notice-promo"><p>We\'ve prepared a special <b>25% welcoming discount</b> for you on <a href="' . wf_sn::generate_sn_web_link('welcome_link', null, array('coupon' => 'welcome')) . '" target="_blank">Security Ninja PRO</a> available <b>only until ' . $time . ' today</b>.<br>PRO has <b>4 extra modules</b> - Core Scanner, Malware Scanner, Events Logger &amp; Scheduled Scanner! <a class="button button-primary" href="' . wf_sn::generate_sn_web_link('welcome_button', null, array('coupon' => 'welcome')) . '" target="_blank">BUY NOW</a></p></div>';
    }
  } // promo_notice


  // display warning if test were never run
  static function min_version_notice() {
    echo '<div class="notice notice-error"><p>Security Ninja <b>requires WordPress version 4.0</b> or higher to function properly. You\'re using WordPress version ' . get_bloginfo('version') . '. Please <a href="' . admin_url('update-core.php') . '" title="Update WP core">update</a>.</p></div>';
  } // min_version_error


  // add markup for UI overlay
  static function admin_footer() {
    if (wf_sn::is_plugin_page()) {
      echo '<div id="sn_overlay"><div class="sn-overlay-wrapper">';
      echo '<div class="inner">';

      // Title
      echo '<div class="wf-sn-title">
             <h2><img src="' . WF_SN_PLUGIN_URL . 'images/security-ninja-logo.png" alt="Security Ninja" title="Security Ninja"></h2>
           </div>';

      // Outer
      echo '<div class="wf-sn-overlay-outer">';

      // Content
      echo '<div class="wf-sn-overlay-content">';
      echo '<div id="sn-site-scan" style="display: none;">';
      echo '<h3>Security Ninja is analyzing your site.<br/>It will only take a few moments ...</h3>';
      echo '</div>';

      do_action('sn_overlay_content');

      echo '<div class="loader"><img title="Loading ..." src="' . plugins_url('images/ajax-loader.gif', __FILE__) . '" alt="Loading..." /></div>';
      echo '<p><br><br><a id="abort-scan" href="#" class="button button-secondary input-button red">Stop scanning</a></p>';

      do_action('sn_overlay_content_after');

      echo '</div>'; // wf-sn-overlay-content

      echo '</div></div></div></div>';
      
      echo '<div id="test-details-dialog" style="display: none;" title="Test details"><p>Please wait.</p></div>';
      
      echo '<div id="sn_tests_descriptions" style="display: none;">';
      require_once WF_SN_PLUGIN_DIR . 'sn-tests-description.php';
      echo '</div>';
    } // if is_plugin_page
  } // admin_footer


  // ad for add-on
  static function core_ad_page() {
    echo '<div class="submit-test-container"><p><b>Core Scanner</b> is a module in Security Ninja PRO. It compares all your core WordPress files (1100+) with the secure master copy maintained by WordPress.org. With one click you will know if even a byte was changed in any file. If so, you can imediatelly recover the original version. <b>Perfect for restoring hacked sites!</b></p>
<p><a target="_blank" href="' . wf_sn::generate_sn_web_link('tab_core_scanner', '/core-scanner/') . '" class="button-primary input-button">Find out more</a></p></div>';

    echo '<table class="addon-ad" width="100%"><tr><td width="50%" valign="top">';
    echo '<ul class="sn-list">
<li>scan WP core files with <strong>one click</strong></li>
<li>scan takes less than 10 seconds</li>
<li>quickly identify <strong>problematic files</strong></li>
<li><strong>restore modified files</strong> with one click</li>
<li>great for removing <strong>exploits</strong> and fixing accidental file edits/deletes</li>
<li>view files\' <strong>source</strong> to take a closer look</li>
<li><strong>fix</strong> broken WP auto-updates</li>
<li>detailed help and description</li>
<li><strong>color-coded results</strong> separate files into 5 categories:
<ul>
<li>files that are modified and should not have been</li>
<li>files that are missing and should not be</li>
<li>files that are modified and they are supposed to be</li>
<li>files that are missing but they are not vital to WP</li>
<li>files that are intact</li>
</ul></li>
<li>complete integration with Ninja\'s easy-to-use GUI</li>
</ul>';

    echo '<p><a href="#" class="hide_tab" data-tab-id="core" title="Hide this tab"><i>No thank you, I\'m not interested (hide this tab)</i></a></p>';

    echo '</td><td>';
    echo '<a target="_blank" href="' . wf_sn::generate_sn_web_link('tab_core_scanner', '/core-scanner/') . '" title="Core Scanner add-on"><img style="max-width: 100%;" src="' .  plugin_dir_url(__FILE__) . 'images/core-scanner.jpg" title="Core Scanner add-on" alt="Core Scanner add-on" /></a>';
    echo '</td></tr></table>';
  } // core_ad_page

  
  // ad for add-on
  static function schedule_ad_page() {
    echo '<div class="submit-test-container"><p><b>Scheduled Scanner</b> is a module in Security Ninja PRO. It gives you an additional peace of mind by automatically running Security Ninja and Core Scanner tests every day. If any changes occur or your site gets hacked you\'ll immediately get notified via email</p>
    <p><a target="_blank" href="' . wf_sn::generate_sn_web_link('tab_scheduled_scanner', '/scheduled-scanner/') . '" class="button-primary input-button">Find out more</a></p>
    </div>';

    echo '<table class="addon-ad" width="100%"><tr><td width="50%" valign="top">';
    echo '<ul class="sn-list">
<li>give yourself a peace of mind with <strong>automated scans</strong> and email reports</li>
<li><strong>get alerted</strong> when your site is <strong>hacked</strong></li>
<li>compatible with both <strong>Security Ninja & Core Scanner add-on</strong></li>
<li>extremely <strong>easy</strong> to setup - set once and forget</li>
<li>optional <strong>email reports</strong> - get them after every scan or only after changes occur on your site</li>
<li>detailed, color-coded <strong>scan log</strong></li>
<li>complete integration with Ninja\'s easy-to-use GUI</li>
</ul>';

    echo '<p><a class="hide_tab" data-tab-id="schedule" href="#" title="Hide this tab"><i>No thank you, I\'m not interested (hide this tab)</i></a></p>';

    echo '</td><td>';
    echo '<a target="_blank" href="' . wf_sn::generate_sn_web_link('tab_scheduled_scanner', '/scheduled-scanner/') . '" title="Scheduled Scanner add-on"><img style="max-width: 100%;" src="' .  plugin_dir_url(__FILE__) . 'images/scheduled-scanner.jpg" title="Scheduled Scanner add-on" alt="Scheduled Scanner add-on" /></a>';
    echo '</td></tr></table>';
  } // schedule_ad_page


  // ad for add-on
  static function logger_ad_page() {
    echo '<div class="submit-test-container"><p><b>Events Logger</b> is a module in Security Ninja PRO. It monitors, tracks and reports every change on your WordPress site, both in the admin and on the frontend. More than 50 events are instantly tracked with all details!</p><p>
    <a target="_blank" href="' . wf_sn::generate_sn_web_link('tab_events_logger', '/events-logger/') . '" class="button-primary input-button">Find out more</a></p></div>';

    echo '<table class="addon-ad" width="100%"><tr><td width="50%" valign="top">';
    echo '<ul class="sn-list">';
    echo '<li>monitor, track and <b>log more than 50 events</b> on the site in great detail</li>
          <li><b>know what happened</b> on the site at any time, in the admin and on the frontend</li>
          <li>prevent <b>"I didn\'t do it"</b> conversations with clients - Events Logger doesn\'t forget or lie</li>
          <li>easily <b>filter</b> trough the data</li>
          <li>know exactly when and <b>how an action happened</b>, and who did it</li>
          <li>receive <b>email alerts</b> for selected groups of events</li>
          <li>each logged event has the following details:<ul>
             <li>date and time</li>
             <li>event description (ie: "Search widget was added to Primary sidebar" or "Failed login attempt with username asdf.")</li>
             <li>username and role of user who did the action</li>
             <li>IP and user agent of the user</li>
             <li>module</li>
             <li>WordPress action/filter</li></ul></li>
          <li>complete integration with Ninja\'s easy-to-use GUI</li>
          <li>it\'s compatible with all themes and plugins</li>';
    echo '</ul>';
    echo '<p><a class="hide_tab" data-tab-id="logger" href="#" title="Hide this tab"><i>No thank you, I\'m not interested (hide this tab)</i></a></p>';
    echo '</td><td>';
    echo '<a target="_blank" href="' . wf_sn::generate_sn_web_link('tab_events_logger', '/events-logger/') . '" title="Events Logger add-on"><img style="max-width: 100%;" src="' .  plugin_dir_url(__FILE__) . 'images/events-logger.jpg" title="Events Logger add-on" alt="Events Logger add-on" /></a>';
    echo '</td></tr></table>';
  } // logger_ad_page


  // ad for add-on
  static function malware_ad_page() {
    echo '<div class="submit-test-container"><p><b>Malware Scanner</b> is a module in Security Ninja PRO. It scans all of plugin, theme and custom <i>wp-content</i> files as well as the options database table in search for malware and other suspicious code.</p>
    <p><a target="_blank" href="' . wf_sn::generate_sn_web_link('tab_malware_scanner', 'malware-scanner') . '" class="button-primary input-button">Find out more</a></p></div>';

    echo '<table class="addon-ad" width="100%"><tr><td width="50%" valign="top">';
    echo '<ul class="sn-list">';
    echo '<li><b>one click scan</b> - quickly identify problematic files</li>
          <li>scan all (active and disabled) <b>theme files</b></li>
          <li>scan all (active and disabled) <b>plugin files</b></li>
          <li>scan all files uploaded to <i>wp-content</i> folder</li>
          <li>scan <b>options DB table</b></li>
          <li>more than <b>20 tests performed</b> on each file</li>
          <li>see exact parts of the file that malware scanner marked as suspicious</li>
          <li>whitelist files that you have inspected and know are safe</li>
          <li>optimised for large WP installations with numerous files</li>
          <li>complete integration with Security Ninja\'s easy-to-use GUI</li>
          <li>compatible with all themes and plugins</li>';
    echo '</ul>';
    echo '<p><a class="hide_tab" data-tab-id="malware" href="#" title="Hide this tab"><i>No thank you, I\'m not interested (hide this tab)</i></a></p>';
    echo '</td><td>';
    echo '<a target="_blank" href="' . wf_sn::generate_sn_web_link('tab_malware_scanner', '/malware-scanner/') . '" title="Malware Scanner add-on"><img style="max-width: 100%;" src="' .  plugin_dir_url(__FILE__) . 'images/malware-scanner.jpg" title="Malware Scanner add-on" alt="Malware Scanner add-on" /></a>';
    echo '</td></tr></table>';
  } // malware_ad_page


  // whole options page
  static function main_page() {
    // redundant but doesn't hurt
    if (!current_user_can('administrator'))  {
      wp_die('You do not have sufficient permissions to access this page.');
    }
    
    $hidden_tabs = get_transient('wf_sn_hidden_tabs');
    if (!$hidden_tabs) {
      $hidden_tabs = array();
    }
    $tabs = array();
    $tabs[] = array('id' => 'sn_tests', 'class' => '', 'label' => 'Security Tests', 'callback' => array(__CLASS__, 'tests_table'));
    if (!in_array('core', $hidden_tabs)) {
      $tabs[] = array('id' => 'sn_core', 'class' => 'promo_tab', 'label' => 'Core Scanner', 'callback' => array(__CLASS__, 'core_ad_page'));
    }
    if (!in_array('malware', $hidden_tabs)) {
      $tabs[] = array('id' => 'sn_malware', 'class' => 'promo_tab', 'label' => 'Malware Scanner', 'callback' => array(__CLASS__, 'malware_ad_page'));
    }
    if (!in_array('logger', $hidden_tabs)) {
      $tabs[] = array('id' => 'sn_logger', 'class' => 'promo_tab', 'label' => 'Events Logger', 'callback' => array(__CLASS__, 'logger_ad_page'));
    }
    if (!in_array('schedule', $hidden_tabs)) {
      $tabs[] = array('id' => 'sn_schedule', 'class' => 'promo_tab', 'label' => 'Scheduled Scanner', 'callback' => array(__CLASS__, 'schedule_ad_page'));
    }
    $tabs = apply_filters('sn_tabs', $tabs);

    echo '<div class="wrap">';
    echo '<div class="wf-sn-title">
           <h2><img src="' . WF_SN_PLUGIN_URL . 'images/security-ninja-logo.png" alt="Security Ninja" title="Security Ninja"></h2>
         </div>';

    echo '<div id="tabs">';
    echo '<ul>';
    foreach ($tabs as $tab) {
	    if (!empty($tab['label'])){	
        echo '<li><a href="#' . $tab['id'] . '" class="' . $tab['class'] . '">' . $tab['label'] . '</a></li>';
	    }
    }
    echo '</ul>';

    foreach ($tabs as $tab) {
      if(!empty($tab['callback'])) {
	      echo '<div style="display: none;" id="' . $tab['id'] . '">';
        call_user_func($tab['callback']);
		    echo '</div>';
	    }      
    }

    echo '</div>'; // tabs
    echo '</div>'; // wrap
  } // main_page


  // display tests table
  static function tests_table() {
    // get test results from cache
	  $tests = get_option(WF_SN_RESULTS_KEY);

    echo '<div class="submit-test-container">
          <input type="submit" value=" Analyze Site " id="run-tests" class="button-primary" name="Submit" />';

    if (!empty($tests['last_run'])) {
      echo '<span class="sn-notice">Tests were last run on ' . date(get_option('date_format') . ' @ ' . get_option('time_format'), $tests['last_run']) . '.';
      if (!empty($tests['run_time'])) {
        echo ' It took ' . number_format($tests['run_time'], 1) . ' seconds to run them.';
      }
      echo '</span>';
      
      $bad = $warning = $good = $score = $total =  0;
      foreach($tests['test'] as $test_name => $test_details) {
        if (substr($test_name, 0, 3) == 'ad_' && (class_exists('wf_sn_ms') || class_exists('wf_sn_cs'))) {
          continue; 
        }
        $total += $test_details['score'];
        if ($test_details['status'] == 10) {
          $good++;
          $score += $test_details['score'];
        } elseif ($test_details['status'] == 0) {
          $bad++;
        } else {
          $warning++;
        }
      }

      $score = round($score / $total * 100);
      
      echo '<div id="counters">';
      if ($good == 1) {
        echo '<span class="good">' . $good . '<br><i>test passed</i></span>';
      } else {
        echo '<span class="good">' . $good . '<br><i>tests passed</i></span>';
      }
      if ($warning == 1) {
        echo '<span class="warning">' . $warning .'<br><i>test has warnings</i></span>';  
      } else {
        echo '<span class="warning">' . $warning .'<br><i>tests have warnings</i></span>';
      }
      if ($bad == 1) {
        echo '<span class="bad">' . $bad . '<br><i>test failed</i></span>';  
      } else {
        echo '<span class="bad">' . $bad . '<br><i>tests failed</i></span>';
      }
      echo '<span class="score">' . $score . '%<br><i>overall site score</i></span>';
      echo '</div>';
    }
    
    if (empty($tests['last_run'])) {
      echo '<p>Security Ninja <b>tests were never run</b>. Click "Analyze Site" to run them now and analyze your site for security vulnerabilities.</p>';
    }
    echo '<p><strong>Please read!</strong> These tests only serve as suggestions! Although they cover years of best practices in security getting all test <i>green</i> does not guarantee your site will not get hacked. Likewise, having them all <i>red</i> doesn\'t mean you\'ll certainly get hacked. Please read each test\'s detailed information to see if it represents a real security issue for your site. Suggestions and test results apply to public, production sites, not local, development ones.</p>';
    
    echo '</div>';

    $out = '';
    if (!empty($tests['last_run'])) {
	  	
      $out .= '<table class="wp-list-table widefat" cellspacing="0" id="security-ninja">';
      $out .= '<thead><tr>';
      $out .= '<th class="sn-status">Status</th>';
      $out .= '<th>Test description</th>';
      $out .= '<th>Test results</th>';
      $out .= '<th>&nbsp;</th>';
      $out .= '</tr></thead>';
      $out .= '<tbody>';

      if (is_array($tests['test'])) {
        // test results
        foreach($tests['test'] as $test_name => $details) {
          if (substr($test_name, 0, 3) == 'ad_' && (class_exists('wf_sn_ms') || class_exists('wf_sn_cs'))) {
            continue; 
          }
          $out .= '<tr>
                  <td class="sn-status">' . wf_sn::status($details['status']) . '</td>
                  <td>' . $details['title'] . '</td>
                  <td>' . $details['msg'] . '</td>';
				  
            if (substr($test_name, 0, 3) == 'ad_') {
              $out .= '<td class="sn-details"><a target="_blank" href="' . wf_sn::generate_sn_web_link('tests_table_' . $test_name) . '" class="button action skip-button">See what PRO offers</a></td>';
            } else {
              $out .= '<td class="sn-details"><a data-test-name="' . $test_name . '" href="#' . $test_name . '" class="button action">Details, tips &amp; help</a></td>';
            }
            $out .= '</tr>';
        } // foreach ($tests)
      } else { // no test results
        $out .= '<tr>
                <td colspan="4">No test results are available. Click "Analyze Site" to run tests now.</td>
              </tr>';
      } // if tests

      $out .= '</tbody>';
      $out .= '<tfoot><tr>';
      $out .= '<th class="sn-status">Status</th>';
      $out .= '<th>Test description</th>';
      $out .= '<th>Test results</th>';
      $out .= '<th>&nbsp;</th>';
      $out .= '</tr></tfoot>';
      $out .= '</table>';
    } // if $results
    
    $out = apply_filters('sn_tests_table', $out, $tests);
    echo $out;
  } // tests_table
  
  
  // helper function to generate tagged buy links
  static function generate_sn_web_link($placement = '', $page = '/', $params = array()) {
    $base_url = 'https://wpsecurityninja.com';
    if ('/' != $page) {
      $page = '/' . trim($page, '/') . '/';  
    }
    $parts = array_merge(array('utm_source' => 'security_ninja', 'utm_medium' => 'plugin', 'utm_content' => $placement, 'utm_campaign' => 'security_ninja_v' . wf_sn::$version), $params);
    
    $out = $base_url . $page . '?' . http_build_query($parts, '', '&amp;');
    
    return $out;
  } // generate_sn_web_link


  // run all tests; via AJAX
  static function run_tests($return = false) {
    if (defined('DOING_AJAX') && DOING_AJAX) {
      check_ajax_referer('wf_sn_run_tests');
    }
    
    @set_time_limit(WF_SN_MAX_EXEC_SEC);
    $test_count = 0;
    $start_time = microtime(true);
    $test_description['last_run'] = current_time('timestamp');

    foreach(wf_sn_tests::$security_tests as $test_name => $test){
      if ($test_name[0] == '_' || in_array($test_name, self::$skip_tests)) {
        continue;
      }
      $response = wf_sn_tests::$test_name();

      $test_description['test'][$test_name]['title'] = $test['title'];
      $test_description['test'][$test_name]['status'] = $response['status'];
      $test_description['test'][$test_name]['score'] = $test['score'];

      if (!isset($response['msg'])) {
        $response['msg'] = '';
      }

      if ($response['status'] == 10) {
        $test_description['test'][$test_name]['msg'] = sprintf($test['msg_ok'], $response['msg']);
      } elseif ($response['status'] == 0) {
        $test_description['test'][$test_name]['msg'] = sprintf($test['msg_bad'], $response['msg']);
      } else {
        $test_description['test'][$test_name]['msg'] = sprintf($test['msg_warning'], $response['msg']);
      }
      $test_count++;
    } // foreach
    
    $test_description['run_time'] = microtime(true) - $start_time;

    do_action('security_ninja_done_testing', $test_description, microtime(true) - $start_time);
    
    $pointers = get_option(WF_SN_POINTERS_KEY);
    if (!empty($pointers['welcome'])) {
      unset($pointers['welcome']);
      update_option(WF_SN_POINTERS_KEY, $pointers);  
    }
    

    if ($return) {
      return $test_description;
    } else {
      update_option(WF_SN_RESULTS_KEY, $test_description);
      die('1');
    }
  } // run_test


  // convert status integer to button
  static function status($int) {
    if ($int == 0) {
      $string = '<span class="sn-error">Failed</span>';
    } elseif ($int == 10) {
      $string = '<span class="sn-success">Passed</span>';
    } else {
      $string = '<span class="sn-warning">Warning</span>';
    }

    return $string;
  } // status

  
  // reset pointers on activation and save some info
  static function activate() {
    $pointers = array();
    $pointers['welcome'] = array('target' => '#menu-tools', 'edge' => 'left', 'align' => 'right', 'content' => 'Thank you for installing <b>Security Ninja</b>! Open <a href="tools.php?page=wf-sn">Tools - Security Ninja</a> to analyze your site.');
    update_option(WF_SN_POINTERS_KEY, $pointers);
    
    $options = get_option(WF_SN_OPTIONS_KEY, array());
    if (!isset($options['first_version']) || !isset($options['first_install'])) {
      $options['first_version'] = wf_sn::get_plugin_version();
      $options['first_install'] = current_time('timestamp');
      update_option(WF_SN_OPTIONS_KEY, $options);
    }
  } // activate

  
  // clean-up when deactivated
  static function deactivate() {
    delete_option(WF_SN_RESULTS_KEY);
    delete_option(WF_SN_POINTERS_KEY);
    delete_transient('wf_sn_hidden_tabs');
  } // deactivate
  
  
  // clean-up when deleted
  static function uninstall() {
    delete_option(WF_SN_RESULTS_KEY);
    delete_option(WF_SN_OPTIONS_KEY);
    delete_option(WF_SN_POINTERS_KEY);
    delete_transient('wf_sn_hidden_tabs');
  } // delete
} // wf_sn class


// hook everything up
register_activation_hook(__FILE__, array('WF_SN', 'activate'));
register_deactivation_hook(__FILE__, array('WF_SN', 'deactivate'));
register_uninstall_hook(__FILE__, array('GMW', 'uninstall'));
add_action('init', array('WF_SN', 'init'));
add_action('plugins_loaded', array('WF_SN', 'plugins_loaded'));
