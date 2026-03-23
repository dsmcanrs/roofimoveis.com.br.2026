$(function(){

	/*
    Exemplo HTML
    <a href="#" class="fav fav-{BOX_CODIGO} fav-on tt" data-id="{BOX_CODIGO}" title="Remover">
        <i class="fa fa-heart"></i>
    </a>
    <a href="#" class="fav fav-{BOX_CODIGO} fav-off tt" data-id="{BOX_CODIGO}" title="Adicionar">
        <i class="fa fa-heart-o"></i>
    </a>
	 */

	// Recupera o cookie
	var favoritos = ($.cookie('favoritos')) ? $.cookie('favoritos') : '';

	// console.log( 'favoritos: ' + favoritos );

	fav_count();

	fav_show();

	/**
	 * Clique no .fav
	 * Se tiver .fav-on remove
	 * Se tover .fav-off adiciona
	 */
	$(document).on('click', '.fav', function(event) {

		var el = $(this);
		var id = el.data("id");
		var strid = id+",";

		// console.log(id);

		if (el.hasClass("fav-on")) {
			// console.log('removendo '+id);
			toastr.warning('Imóvel removido!');
			favoritos = favoritos.replace(strid,"");
		}

		if (el.hasClass("fav-off")) {
			// console.log('adicionando '+id);
			toastr.info('Imóvel salvo!');
			favoritos = favoritos.replace(strid,"");
			favoritos = favoritos + strid;
		}

		// Evitar rolar a pagina no link #
		event.preventDefault();

		$.cookie('favoritos', favoritos, { path: '/' });

		fav_count();

		fav_show();

	});

	/**
	 * Contador
	 * Retorna o valor em .fav-num
	 */
	function fav_count(){

		$(".fav-num").text('');
		$(".fav-num").hide();

		if(favoritos){

			var array = favoritos.split(",");
			var total = array.length-1;

			$(".fav-num").each(function(){
				if(total>=1){
					$(this).text(total);
				}
			});

			$(".fav-num").show();

		}

	}

	/**
	 * Marca imóveis já favoritados
	 * Retorna o visibility para .fav-on ou fav-off
	 */
	function fav_show(){

		$('.fav-on').css('display','none');
		$('.fav-off').css('display','inline-block');

		if(favoritos){

			var array = favoritos.split(",");

			for (i=0;i<(array.length)-1;i++) {
				// console.log('.fav-'+array[i]);
				$('.fav-'+array[i]+'.fav-on').css('display','inline-block');
				$('.fav-'+array[i]+'.fav-off').css('display','none');
			}

		}

	}

	/**
	 * Limpar lista de favoritos
	 */
	$(".limpar-favoritos").click(function(){
		console.log('limpar');
		$.cookie('favoritos', '', { path: '/' });
	    // $.removeCookie("favoritos", null, { path: '/' });
	    window.location.href = "favoritos";
	});

});
