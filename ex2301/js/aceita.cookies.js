$(function(){

  var aceitou = window.localStorage.getItem('aceitar-cookies');

  if( !aceitou ){
    $('#aceitar-cookies').addClass('d-block');
  }

  $(".btn-aceitar-cookies").click(function(){
    window.localStorage.setItem('aceitar-cookies', true);
    $('#aceitar-cookies').removeClass('d-block');
  });

});