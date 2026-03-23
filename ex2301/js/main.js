$(function(){

	if( /Android|webOS|iPhone|iPad/i.test(navigator.userAgent) ){
		$('[data-aos]').each(function() {
			$(this).removeAttr('data-aos');
		});
	}else{
		AOS.init({ duration: 1000, once: true });
	}

	// var domain = $('meta[property="domain"]').attr('content');
	// if(domain===undefined) console.error('Definir META domain');

	// replace das fotos quebradas dentro do box dos imoveis
	$(".imovel .foto img, .imovel .mais img").on('error', function() {
		$(this).unbind("error").attr("src", "img/sem_foto.png");
	});

	// Páginas: fix para fotos e titulos
	$(".pg-pagina main, .pg-blog main").each(function () {
		$(this).find("img").addClass('img-fluid rounded-4');
		$(this).find("h1").addClass('h1');
		$(this).find("h2").addClass('h2');
		$(this).find("h3").addClass('h3');
	});
	
	// nao abrir link
	$("a.prevent").click(function(e){
		e.preventDefault();
	});
	$("a.stop").click(function(e){
		e.stopPropagation();
	});

	// Clique aos links dentro do card
	// $('.card-imovel').on('click', 'a', function(event) {
	// 	event.preventDefault();
	// 	var url = $(this).attr('href');
	// 	window.open(url, '_blank');
	// });

	// lazy load
	$(".lazy").Lazy({
		effect: "fadeIn",
        effectTime: 500,
        threshold: 0,
        beforeLoad: function(element) {
            // console.log('Before: ' + element.data('src'));
        },
 		afterLoad: function(element) {
			// console.log('After: ' + element.data('src'));
		},
        onError: function(element) {
            // console.log('Error: ' + element.data('src'));
        },
        onFinishedAll: function() {
            // console.log('Finished: ' + element.data('src'));
        }
	});

	// loading
	// $("body").addClass("loaded");

	$('[data-toggle="tooltip"]').tooltip();
	$('.tt').tooltip();

	// $('.slim-scroll').slimScroll({});

	// busca aberta na home
	// if ($(".pg-home").length>0) {
	// 	$("body").toggleClass("busca-aberta");
	// }

	$(".btn-collapse-busca").click(function(){
		$(window).scrollTop(0);
	});

	// Modo mobile > Select padrão do OS
	if (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)) {
		// ativa o modo nativo do mobile
		// $.fn.selectpicker.Constructor.DEFAULTS.mobile = true;
		// Desativa o liveserch no mobile = melhor usabilidade
		// $.fn.selectpicker.Constructor.DEFAULTS.liveSearch = false;
		// $(".selectpicker").selectpicker('mobile');
	}

	if ( $(".select-checkbox").length>0 ) {
		$(".select-checkbox").selectCheckbox();
	}

	// textarea autosize
    autosize(document.querySelectorAll('textarea'));

    // Submit Loading - fix IOs
	$(".btn.loading").closest('form').on('submit', function() {
		var $btn = $(this).find('.btn.loading');
		var loadingText = '<i class="fa fa-circle-notch fa-spin"></i>';
		$btn.html(loadingText);
		$btn.off("click");
	});

	if ($(".pg-imoveis").length>0) {
		$(window).scroll(function (){
			if ($(this).scrollTop() > 400) {
				//console.log('700');
				$('.scrollto').fadeIn();
			} else {
				$('.scrollto').fadeOut();
			}
		});
	}

	$(".scrollto").click(function(){
		var id = $(this).attr("href");
		if ($(id).length>0) {
			var t = $(id).offset().top - $("#header").outerHeight();
			$("html, body").animate({ scrollTop: t },1000);
			return false;
		} else return true;
	});

	// $("body").click(function(){
	// 	$("body").removeClass("menu-aberto")
	// });

	// Menu Mobile
	$(".open-menu-mobile").click(function(){
		$(".navbar").toggleClass("opened");
		$(".navbar").trigger('classChange');
		return false;
	});

	// Fecha Menu
	$("body").click(function(e){
		$target = $(e.target).attr('class');
		// console.log($target);
		if( $target!='dropdown-toggle' ){
			// console.log('vai fechar');
			$(".navbar").removeClass("opened");
			$(".navbar").trigger('classChange');
		}
	});

	// Oculta float buttons
	$(".navbar").on('classChange', function() {
		if( $(this).hasClass('opened') ){
			$(".float-buttons").addClass('invisible');
		}else{
			$(".float-buttons").removeClass('invisible');
		}
	});

	// Pop-up trigger
	if ($(".btn-pop-up").length>0) {
		console.log( 'pop-up' );
		$('.btn-pop-up').click();
	}

	// Pop-up trigger
	if ($("#pop").length>0) {
		// console.log( 'pop-up' );
		$('.abre-pop-up').trigger("click");
	}	

	// copy html
	$(".copy-html").each(function(index, element) {
        var $destino = $(this);
		var $origem = $($destino.data("copy"));
		var $html = $origem.clone();
		console.log($html);
		$html.attr("id","").appendTo($destino);
    });

	$(".copy-link").click(function(){
		var currentUrl = window.location.href;
		$("body").append('<input id="copyURL" type="text" value="" />');
        $("#copyURL").val(currentUrl).select();
        document.execCommand("copy");
        $("#copyURL").remove();
		toastr.info('URL Copiada!');
	});

	// banner video
	if ( $(".banner-video").length>0 ) {
		$(".banner-video").each(function(){
			if( $(this).data('youtube')!='' ){
				$(this).youtube_background({mobile:true});
			}
		});
	}

	Fancybox.bind( $('.fancy'), "[data-fancybox]", {
		autoSize: true
	});

	// Torna o banner sem texto clicável
	// Adiciona o cursor de clique
	if ( $(".full-banner").length>0 ) {
		$(".full-banner .swiper-slide").each(function(){
			var href = $(this).find('a').attr('href');
			if(href!='#') {
				$(this).css('cursor','pointer');
				$(this).on('click',function(){
					// console.log(href);
					window.location = href
				});
			}
		});
	}

	$(".swiper-container").each(function(index, el){

		// Adiciona identificador único
		var swiperEl = 'x-swiper-'+index;

		$(el).addClass(swiperEl);

		// console.log(el);

		var count = $(el).find('.swiper-slide').length;

		// Obtendo valores dos atributos data-sm e data-lg
		var smData = $(el).data('sm') ? $(el).data('sm').split(',') : null;
		var mdData = $(el).data('md') ? $(el).data('md').split(',') : null;
		var lgData = $(el).data('lg') ? $(el).data('lg').split(',') : null;

		// Configuração dos breakpoints
		var breakpoints = {};

		// Configuração dos breakpoints
		if (count>1) {
			if (smData) {
				breakpoints[0] = {
					slidesPerView: parseInt(smData[0]),
					slidesPerGroup: parseInt(smData[0]),
					spaceBetween: parseInt(smData[1])
				};
			}
			if (mdData) {
				breakpoints[768] = {
					slidesPerView: parseInt(mdData[0]),
					slidesPerGroup: parseInt(mdData[0]),
					spaceBetween: parseInt(mdData[1])
				};
			}
			if (lgData) {
				breakpoints[1024] = {
					slidesPerView: parseInt(lgData[0]),
					slidesPerGroup: parseInt(lgData[0]),
					spaceBetween: parseInt(lgData[1])
				};
			}
		}

		var options = {
			observer: true,
			lazy: true,
			loop: $(el).data('loop') ? $(el).data('loop') : false,
			autoHeight: $(el).data('autoheight') ? $(el).data('autoheight') : false,
			draggable: $(el).data('draggable') ? $(el).data('draggable') : false,
			preloadImages: $(el).data('preload') ? $(el).data('preload') : false,
			autoplay: ($(el).data('autoplay') && count>1) ? { delay: $(el).data('autoplay') } : false,
			slidesPerView: $(el).data('slides') ? $(el).data('slides') : 'auto',
			speed: 1000,
			breakpoints: breakpoints,
			// spaceBetween: 20,
		};

		// Pginação
		if ($(el).find('.pagination').length) {
			var paginationEl = $(el).find('.pagination')[0];
			// console.log(paginationEl);
			options.pagination = {
				el: paginationEl,
				clickable: true
			};
		}

		// Setas
		if ($(el).find('> .navigation').length) {
			// var next = $(el).find('.next')[0];
			// var prev = $(el).find('.prev')[0];
			var next = $(el).find('> .navigation .next')[0];
        	var prev = $(el).find('> .navigation .prev')[0];
			// console.log(prev);
			// console.log(next);
			options.navigation = {
				nextEl: next,
				prevEl: prev,
			};
		}

		// console.log(options);

		new Swiper(el, options);

	});

	// Lista ou grade de imoveis
	if ($(".visualizar").length>0) {

		var $box = $(".grid-list");
		var view = localStorage.getItem('view');

		if(view=='mapa'){
			showMapa();
		}else if(view=='list'){
			showList();
		}else{
			showGrid();
		}

		$(".btn-mapa").click(function(){
			showMapa();
		});

		$("a.btn-list").click(function(){
			showList();
			hideMapa();
			// swiperUpdate();
		});

		$("a.btn-grid").click(function(){
			showGrid();
			hideMapa();
			// swiperUpdate();
		});

		function showList(){
			// console.log('showList');
			$(".visualizar a").each(function(){ $(this).removeClass("active"); })
			$(".visualizar a.btn-list").addClass("active");
			$('section.imoveis').removeClass('d-none');
			$box.addClass('col-md-12');
			localStorage.setItem('view', 'list');
		}

		function showGrid(){
			// console.log('showGrid');
			$(".visualizar a").each(function(){ $(this).removeClass("active"); })
			$(".visualizar a.btn-grid").addClass("active");
			$('section.imoveis').removeClass('d-none');
			$box.removeClass('col-md-12');
			localStorage.setItem('view', 'grid');
		}

		function showMapa(){
			// console.log('showMapa');
			$(".visualizar a").each(function(){ $(this).removeClass("active"); })
			$(".visualizar a.btn-mapa").addClass("active");
			$('section.mapa').removeClass('d-none');
			$('section.imoveis').addClass('d-none');
			window.myMap.invalidateSize()
			localStorage.setItem('view', 'mapa');
		}

		function hideMapa(){
			// console.log('hideMapa');
			$('section.mapa').addClass('d-none');
		}

		// Ajustar a largura da imagem no swiper
		// function swiperUpdate(){
		// 	$(swiperFotos).each(function(index, elem){
		// 		// console.log(elem);
		// 		swiperFotos[index].update();
		// 	});
		// }

	}

	$("#select-orderby").change(function(){
		if ($(this).val()!="") {
			var page = $(this).data('orderby-page');
			var querystring = $(this).data('orderby-query');
			var value = $(this).val();
			window.location = `${page}?${querystring}&ordem=${value}`;
		}
	});

	// Reload Mapa em Tab
	$('a[href="#pill-mapa"],a[data-bs-target="#pill-mapa"]').on('click', function() {
        setTimeout(function() {
			// console.log('pill-mapa click');
          	window.myMap.invalidateSize();
        }, 300);
	});

	// Reload iframes em abas
	$('a[href="#pill-tour"]').on('click', function() {
        setTimeout(function() {
			// console.log('tour');
          	$(window).scroll();
        }, 100);
	});

	if ($(".pg-detalhes").length>0) {

		navCount = $('.nav-pills .nav-item').length;
		$btnVideo = $('a[href="#pill-video"]');
		$btnMapa = $('a[href="#pill-mapa"]');
		$tabFotos = $('#pill-fotos');
		$divContato = $('div.contato');

		// Show/Hide botões
		$('.nav-pills .nav-item').each(function(){
			if( $(this).hasClass('d-none') || $(this).hasClass('hidden') ) navCount--;
		});
		// console.log(navCount);
		if( navCount<2 ) $('.nav-pills').addClass('d-none');

		// Ativar a aba de vídeo
		if( $btnVideo.length>0 ){
			$btnVideo.trigger('click');
		}

		// Ativar aba Mapa se não tiver fotos
		if( !$tabFotos.length>0 ){
			// console.log('abre mapa');
			$btnMapa.trigger('click');
		}

		if( !$tabFotos.length>0 ){
			// console.log('nao tem fotos');
			$divContato.css('top',0);
		}

	}

	if ($(".pg-home").length>0) {
		$buscaMenu = $('.busca-menu');
		menuTabs = $buscaMenu.find('a').length;
		if( menuTabs<2 ) $buscaMenu.addClass('hidden');
	}

	if ( $(".go-back").length>0 ) {

		const referrer = document.referrer;
		const buscaURL = '/imoveis';
		const currentDomain = window.location.origin;

		// console.log('go-back');
		// console.log('referrer: ' + referrer);
		// console.log('currentDomain: ' + currentDomain);

		if (referrer && referrer.startsWith(currentDomain)) {
			$(".go-back").on("click", function(e) {
				e.preventDefault();
				history.go(-1);
			});
		} else {
			$(".go-back").hide();
		}
	}

	$(".share a").click(function(e){

		e.preventDefault();

		var link 	= '';
		var rede 	= $(this).data("rede");
		var url 	= $(this).data("url");

		if (rede=='facebook') 	link = 'https://www.facebook.com/sharer/sharer.php?u='+url;
		if (rede=='twitter') 	link = 'https://twitter.com/intent/tweet?url='+url;
		if (rede=='google') 	link = 'https://plus.google.com/share?text='+url;
		if (rede=='linkedin') 	link = 'https://www.linkedin.com/shareArticle?mini=true&url='+url;

		if (rede) {
			window.open(link,rede+'_dialog','width=630,height=440');
		}else{
			// window.location = this.href;
			window.open(this.href,'_blank');
		}
	});

	// Depoimentos Nota
	$('input[name="nota"]').change(function () {
		var rating = parseInt($(this).val());
		// console.log(rating);
		$('input[name="nota"]').prop('checked', false);
		$('input[name="nota"]').each(function(index, element) {
			// console.log(index);
			if( index<rating ){
				$(element).prop('checked', true);
			}
		});
	});

	// tratamento de máscaras
	if ($("input[class*=mask-]").length>0) {
		// $.mask.definitions['~']='[0-9 ]';
		// $("form").find(".mask-moeda").maskMoney({thousands:'.', decimal:','});
		$("form").find(".mask-moeda").maskMoney({thousands:'.', precision:0});
		$("form").find(".mask-cep").mask("99999-999");
		$("form").find(".mask-data").mask("99/99/9999");
		$("form").find(".mask-cpf").mask("999.999.999-99");
		$("form").find(".mask-rg").mask("9999999999");
        // $("form").find(".mask-fone").mask("(99) 9999-99999").on("focusout", function () {
        //     var numbers = $(this).val().replace(/\D/g, '');
        //     $(this).unmask();
        //     if (numbers.length > 10) {
        //         $(this).mask("(99) 99999-9999");
        //     } else {
        //         $(this).mask("(99) 9999-9999");
        //     }
        // });
        $("form").find(".mask-fone, .mask-whatsapp").on('input', function() {
            var cursorPosition = this.selectionStart;
            var value = $(this).val();
            var numbers = value.replace(/\D/g, '');
            var formatted = '';
            var oldLength = value.length;

            if (numbers.length === 0) {
                formatted = '';
            } else if (numbers.length <= 2) {
                formatted = '(' + numbers;
            } else if (numbers.length <= 6) {
                formatted = '(' + numbers.substring(0, 2) + ') ' + numbers.substring(2);
            } else if (numbers.length <= 10) {
                formatted = '(' + numbers.substring(0, 2) + ') ' + numbers.substring(2, 6) + '-' + numbers.substring(6);
            } else if (numbers.length === 11) {
                formatted = '(' + numbers.substring(0, 2) + ') ' + numbers.substring(2, 7) + '-' + numbers.substring(7);
            } else if (numbers.length === 12 && numbers.substring(0, 2) === '55') {
                formatted = '+55 (' + numbers.substring(2, 4) + ') ' + numbers.substring(4, 8) + '-' + numbers.substring(8);
            } else if (numbers.length >= 13 && numbers.substring(0, 2) === '55') {
                formatted = '+55 (' + numbers.substring(2, 4) + ') ' + numbers.substring(4, 9) + '-' + numbers.substring(9, 13);
            } else {
                // Para números com mais de 11 dígitos que não começam com 55
                formatted = '(' + numbers.substring(0, 2) + ') ' + numbers.substring(2, 7) + '-' + numbers.substring(7, 11);
            }

            $(this).val(formatted);

            // Ajustar posição do cursor para melhor experiência
            var newLength = formatted.length;
            var lengthDiff = newLength - oldLength;
            var newPosition = cursorPosition + lengthDiff;

            // Evitar que o cursor fique após caracteres especiais
            if (newPosition > 0 && formatted.charAt(newPosition - 1).match(/[\(\)\s\-\+]/)) {
                newPosition++;
            }

            this.setSelectionRange(newPosition, newPosition);
        });
	}


});

$(window).on("load scroll",function(){
	var st = $(window).scrollTop();
	if (st>0) $("body").addClass("scrolled"); else $("body").removeClass("scrolled");
});

function errorImage(el) {
	var imgElement = el;
	imgElement.src = 'https://siteexpresso.com.br/assets/no-image.jpg';
	imgElement.alt = 'Imagem não encontrada';
}

function removeSpecialChars(str) {
	var diacritics = [
		{char: '-', base: /\'/g},
		{char: '-', base: / /g},
		{char: 'A', base: /[\300-\306]/g},
		{char: 'a', base: /[\340-\346]/g},
		{char: 'E', base: /[\310-\313]/g},
		{char: 'e', base: /[\350-\353]/g},
		{char: 'I', base: /[\314-\317]/g},
		{char: 'i', base: /[\354-\357]/g},
		{char: 'O', base: /[\322-\330]/g},
		{char: 'o', base: /[\362-\370]/g},
		{char: 'U', base: /[\331-\334]/g},
		{char: 'u', base: /[\371-\374]/g},
		{char: 'N', base: /[\321]/g},
		{char: 'n', base: /[\361]/g},
		{char: 'C', base: /[\307]/g},
		{char: 'c', base: /[\347]/g}
	]
	diacritics.forEach(function(letter){
		str = str.replace(letter.base, letter.char);
	});
	str = str.toLowerCase();
	return str;
}