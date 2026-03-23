$(function(){

	// wrapper que vai centralizar a janbela
	$(".dialog").each(function(){
		$(this).wrap('<div class="dialog-wrapper"></div>');
	});

	// Abre o dialog
	$(".dialog-open").on('click', function() {
		var target = $(this).data('src');
		$('body').addClass('dialog-opened');
		$(target).addClass('show');
		$(target).parent().addClass('show');
		window.location.hash = "dialog-open";
	});

	// fechar clique no wrapper
	$(document).on('click', '.dialog-wrapper', function(e) {
		// console.log('click dialog-wrapper');
		if ($(e.target).hasClass("dialog-wrapper")) {
			closeOpen();
		}
	});

	// fecha clique botão
	$(".dialog-close").on('click', function(e) {
		// console.log('click dialog-close');
		e.preventDefault();
		closeOpen();
	});

	// fecha no historty back
	$(window).on('hashchange', function() {
		var currentHash = window.location.hash;
		 if (currentHash!='#dialog-open') {
			closeOpen();
		 }
	});

	function closeOpen(){
		$('body').removeClass('dialog-opened');
		$(".dialog-wrapper").removeClass('show');
		$(".dialog").removeClass('show');
	}

});