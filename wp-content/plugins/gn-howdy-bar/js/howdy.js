//use a noconflict wrapper so that $ is defined as jQuery
jQuery.noConflict();
jQuery( document ).ready(function( $ ){
  //normal jquery code with $ shortcut goes in the wrapper
  $('.howdy-dismiss').click(function(){
    $('.howdy-bar').slideUp('slow');
    //animate the .header bar margin so it slides up
    $('.header-bar').animate({
      marginTop: '0'
    }, 'slow');
  });
});
