/*
 * Security Ninja
 * Main backend JS
 * (c) Web factory Ltd, 2012 - 2016
 */

  
function sn_block_ui(content_el) {
  jQuery('html.wp-toolbar').addClass('sn-overlay-active');
  jQuery('#wpadminbar').addClass('sn-overlay-active');
  jQuery('#sn_overlay .wf-sn-overlay-outer').css('height', (jQuery(window).height() - 200) + 'px');
  jQuery('#sn_overlay').show();

  if (content_el) {
    jQuery(content_el, '#sn_overlay').show();
  }
} // sn_block_ui


function sn_unblock_ui(content_el) {
  jQuery('html.wp-toolbar').removeClass('sn-overlay-active');
  jQuery('#wpadminbar').removeClass('sn-overlay-active');
  jQuery('#sn_overlay').hide();

  if (content_el) {
    jQuery(content_el, '#sn_overlay').hide();
  }
} // sn_block_ui

function sn_fix_dialog_close(event, ui) {
  jQuery('.ui-widget-overlay').bind('click', function(){ jQuery('#' + event.target.id).dialog('close'); });
} // fixDialogClose


jQuery(document).ready(function($) {
  $('#test-details-dialog').dialog({'dialogClass': 'wp-dialog sn-dialog',
                               'modal': true,
                               'resizable': false,
                               'zIndex': 9999,
                               'width': 750,
                               'height': 'auto',
                               'hide': 'fade',
                               'open': function(event, ui) { sn_fix_dialog_close(event, ui); },
                               'close': function(event, ui) { jQuery('#test-details-dialog').html('<p>Please wait.</p>') },
                               'show': 'fade',
                               'autoOpen': false,
                               'closeOnEscape': true
                              });
                              
  // init tabs
  $('#tabs').tabs({
    activate: function( event, ui ) {
        $.cookie('sn_tabs_selected', $('#tabs').tabs('option', 'active'));
    },
    active: $('#tabs').tabs({ active: $.cookie('sn_tabs_selected') })
  });

  // run tests, via ajax
  $('#run-tests').click(function(){
    var data = {action: 'sn_run_tests', '_ajax_nonce': wf_sn.nonce_run_tests};

    sn_block_ui('#sn-site-scan');

    $.post(ajaxurl, data, function(response) {
      if (response != '1') {
        alert('Undocumented error. Page will automatically reload.');
        window.location.reload();
      } else {
        window.location.reload();
      }
    });
  }); // run tests

  // show test details/help popup
  $('.sn-details a.button').live('click', function(e) {
    if ($(this).hasClass('skip-button')) {
      return true;
    }
    
    e.preventDefault();
    
    if ($('#wf-ss-dialog').length){
      $('#wf-ss-dialog').dialog('close');
    }
    
    test_id = $(this).data('test-name');
    name = $('#' + test_id + ' .test_name').text();
    if (name == '') {
      name = 'Unknown test ID';
      content = 'Help is not available for this test. Make sure you have the latest version of Security Ninja installed.';
    } else {
      content = '<span class="ui-helper-hidden-accessible"><input type="text"/></span>' + $('#' + test_id + ' .test_description').html();  
      content += '<hr><h3>Auto Fixer</h3><div id="auto-fixer-content"><p>Auto Fixer is a module in <a href="https://wpsecurityninja.com/?utm_source=security_ninja&utm_medium=plugin&utm_content=test_info_fixer&utm_campaign=security_ninja_v2.20" target="_blank"><b>Security Ninja PRO</b></a>. If you don\'t like creating backups, editing files, messing with code and getting your hands dirty - it will do all of that for you! It fixes over 30 security issues with one click. Perfect if you\'re a <b>beginner</b> or in a <b>time crunch</b> and don\'t want to fix stuff for hours. <a href="https://wpsecurityninja.com/?utm_source=security_ninja&utm_medium=plugin&utm_content=test_info_fixer_get&utm_campaign=security_ninja_v2.20" class="button button-primary" target="_blank"><b>GET the PRO version</b></a></p></div>';
    }
    
    $('#test-details-dialog').html(content);
    $('#test-details-dialog').dialog('option', { title: name, test_id: test_id } ).dialog('open');
    
    return false;
  }); // show test details

  // hide add-on tab
  $('.hide_tab').on('click', function(e){
    e.preventDefault();
    data = {action: 'sn_hide_tab', 'tab': $(this).data('tab-id'), '_ajax_nonce': wf_sn.nonce_hide_tab};

    $.post(ajaxurl, data, function(response) {
      if (!response.success) {
        alert('Undocumented error. Page will automatically reload.');
        window.location.reload();
      } else {
        window.location.reload();
      }
    });
  }); // hide add-on tab

  // abort scan by refreshing
  $('#abort-scan').on('click', function(e){
    e.preventDefault();
    if (confirm('Are you sure you want to stop scanning?')) {
      window.location.reload();
    }
  }); // abort scan
}); // on ready
