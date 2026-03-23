/**
 * Mapa com cluster e popinfo
 * @required 	{DIV} 	#mapa
 * @required 	{JSON} 	#markers
  */
$(function(){

    if ($("#full-map").length) {

        var Map = document.getElementById("full-map");
        var Zoom = 15;
        var minZoom = 10;
        var maxZoom = 18;
        var Attribution = '<a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';
        var markersRef = {};
        var cluster = L.markerClusterGroup({ maxClusterRadius: 30 });

		// Variável para verificar inicialização do mapa
		var isMapInitialized = false;
		// Variável para identificar a primeira execução
		var isFirstLoad = true;

        // Inicializa o mapa no centro do primeiro marcador, se houver
        var inilat = 0, inilng = 0;  // Coordenadas iniciais (0,0)
        var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            minZoom: minZoom,
            maxZoom: maxZoom,
            attribution: Attribution
        });

        window.myMap = L.map(Map, {
            center: [inilat, inilng],
            zoom: Zoom,
            zoomControl: false,
            layers: [tiles],
        });

		function loadMarkers() {

			var bounds = window.myMap.getBounds();
			var ne = bounds.getNorthEast();
			var sw = bounds.getSouthWest();

			// console.log(bounds);

			// Captura os parâmetros da URL
			var params = new URLSearchParams(window.location.search);

			// Define os dados da requisição AJAX
			var data = {
				finalidade: params.get('finalidade') || '',
				cidade: params.get('cidade') || '',
				categoria: params.get('categoria') || '',
				bairro: params.get('bairro') || '',
				valor_de: params.get('valor_de') || '',
				valor_ate: params.get('valor_ate') || '',
				carac: params.get('carac') || '',
				dorm: params.get('dorm') || '',
				suites: params.get('suites') || '',
				vagas: params.get('vagas') || ''
			};

			// Apenas se não for a primeira execução, inclui as coordenadas
			if (!isFirstLoad) {
				var bounds = window.myMap.getBounds();
				var ne = bounds.getNorthEast();
				var sw = bounds.getSouthWest();
				data.neLat = ne.lat;
				data.neLng = ne.lng;
				data.swLat = sw.lat;
				data.swLng = sw.lng;
			}

			$.ajax({
				url: 'inc/markers',
				type: 'GET',
				data: data,
				success: function(response) {

					let isFirstMarker = true;

					$.each(response, function(index, imovel) {

						var codigo = imovel['codigo'];

						// Verifica se o marcador já existe
						if (markersRef[codigo]) {
							return; // Pula a adição se já está no mapa
						}

						var label = imovel['label'].replace('R$', '').replace(',00', '');
						label = parseInt(label) > 0 ? label : 'Consulte';

						var marker = L.marker([imovel['lat'], imovel['lng']], {
							title: codigo,
							icon: new L.DivIcon({
								className: 'my-div-icon',
								html: '<span class="bg-white text-dark p-2 border b-2 brd-1 rounded-pill overflow-visible">' + label + '</span>'
							})
						});

						// Inicializa a propriedade
						marker.bindPopup('');

						// Adicionar marcador ao array
						markersRef[codigo] = marker;

						// Associa o marcador ao cluster
						cluster.addLayer(marker);

						marker.on('click', onMarkerClick);

						// Centraliza o mapa no primeiro marcador apenas no carregamento inicial
						if (isFirstMarker && !isMapInitialized) {
							window.myMap.setView([imovel['lat'], imovel['lng']], Zoom);
							isFirstMarker = false;
							isMapInitialized = true;
						}

					});

					// Adiciona o cluster ao mapa se ainda não estiver
					if (!window.myMap.hasLayer(cluster)) {
						window.myMap.addLayer(cluster);
					}

					isFirstLoad = false;

					console.log("> Marcadores:", getMarkerCount());

				},
				error: function(error) {
					console.error('Erro na consulta:', error);
				}
			});

		}

        // Evento para recarregar marcadores
        window.myMap.on('moveend', loadMarkers);

        // Carrega marcadores na inicialização
        // loadMarkers();

    }

	/**
	 * Função do clique
	 * @param {event} e
	 */
	function onMarkerClick(e) {

		var el = e.target;
		var popup = el.getPopup();
		var id = el._icon.title;

		// console.log( id );

		// $.get( 'marker', function( data ) {
		$.get( 'inc/marker?codigo='+id, function( data ) {

			// console.log(data);

			var Imovel = JSON.parse(data);

			var codigo = Imovel.codigo;
			var foto = Imovel.foto;
			var categoria = Imovel.categoria;
			var valor = Imovel.valor;
			var dorm = Imovel.dormitorios;
			var vagas = Imovel.vagas;
			var area = Imovel.area;
			var img = Imovel.foto;
			var url = Imovel.url;

			var fotos = '';
			var info_dorm = '';
			var info_vagas = '';
			var info_area = '';

			if(dorm>0 && dorm!='hidden'){
				info_dorm =  '<span class="'+dorm+'"><i class="fa fa-bed"></i> '+dorm+'</span>';
			}
			if(vagas>0 && vagas!='hidden'){
				info_vagas = '<span class="'+vagas+'"><i class="fa fa-car"></i> '+vagas+'</span>';
			}
			if(area!="" && area!='hidden'){
				info_area = '<span class="'+area+'"><i class="fa fa-crop"></i> '+area+'</span>';
			}

			// $.each( Imovel.fotos, function( key, foto ){
			//   	// console.log( key + ": " + foto );
			// 	fotos = fotos + '<a href="'+url+'" \
			// 					   target="_blank" \
			// 					   class="imagem" \
			// 					   style="background-image:url('+ foto +')"> \
			// 					</a>';
			// });

			// console.log(fotos);

			var info = '<div class="imovel info-window rounded overflow-hidden"> \
							<div class="foto"> \
								<a href="'+url+'" \
									target="_blank" \
									class="imagem" \
									style="background-image:url('+ foto +')"> \
								</a> \
							</div> \
							<div class="sobre"> \
								<div class="titulo">'+categoria+'</div> \
								<div class="info">'+info_dorm+info_vagas+info_area+'</div> \
								<div class="preco">'+valor+'</div> \
							</div> \
						</div>';

			popup.setContent( info );

			// reload favoritos
			$('body').append('<a class="hidden fav-fake fav"></a>');
			$(".fav-fake").trigger("click");

		});

	}

	/**
	 * Função para criar o marcador
	 * @param {*} img
	 * @param {*} id
	 * @returns
	 */
	function getIcon(img, id){

		imgIco = (img!==undefined) ? img : 'img/marker/marker.png';
		className = (id!==undefined) ? 'marker marker-'+id : '';

		var Icon = new L.icon({
			className: className,
			iconUrl: imgIco,
			iconSize: [45, 60],
			iconAnchor: [22, 60]
		});

		return Icon;
	}

	function getMarkerCount() {
		return Object.keys(markersRef).length;
	}

});
