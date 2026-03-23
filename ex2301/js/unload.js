$(document).ready(function() {

	function createLoadingIndicator() {
		var loadingIndicator = $('<div>', {
			id: 'loadingIndicator',
			css: {
				display: 'none',
				position: 'fixed',
				top: '0',
				left: '0',
				width: '100%',
				height: '100%',
				backgroundColor: 'rgba(0, 0, 0, 0.3)',
				zIndex: '9999'
			}
		});

		loadingIndicator.append($('<img>', {
			src: 'https://siteexpresso.com.br/assets/loading-circle.svg',
			alt: 'Carregando...',
			css: {
				position: 'absolute',
				top: '50%',
				left: '50%',
				transform: 'translate(-50%, -50%)'
			}
		}));

		$('body').append(loadingIndicator);
	}

	// Chame a função para criar o elemento de loading
	createLoadingIndicator();

	let loadingTimeout;
	let isNavigatingBack = false;

	// Detecta quando o usuário está voltando uma página
	window.addEventListener('popstate', function() {
		isNavigatingBack = true;
	});

	// Quando o evento beforeunload é acionado
	$(window).on('beforeunload', function() {
		if (!isNavigatingBack) {
			loadingTimeout = setTimeout(function() {
				$('#loadingIndicator').show();
			}, 2000);
		}
	});

	// Quando a página é carregada ou restaurada
	$(window).on('pageshow', function() {
		clearTimeout(loadingTimeout);
		$('#loadingIndicator').hide();
		isNavigatingBack = false; // Reseta o estado
	});

});
