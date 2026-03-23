/**
 * [description]
 * @required {HTML ELement} #map
 * @required {JSON content} #markers
 * [
 * 	 {
 * 		lat,
 * 		lng,
 * 		ico, 	padrao vazio
 * 		info, 	padrao vazio
 * 		type	padrao vazio [marker, circle]
 * 	  }
 * 	]
 */
$(function(){

	if ( $("#map").length ) {

		var Map = document.getElementById("map");
		var Zoom = 15;
		var MaxZoom = 17;
		var Attribution = '<a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';

		var MapMarkers = $('#markers').html();
		var markers = JSON.parse(MapMarkers);

		// console.log(MapMarkers);

		// window.myMap = L.map(Map).setView(
		// 	[markers[0]['lat'],markers[0]['lng']],
		// 	Zoom
		// );

		window.myMap = L.map(Map, {
			center: [markers[0]['lat'], markers[0]['lng']],
			zoom: Zoom,
			zoomControl: false
		});

		// Init
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: MaxZoom,
			attribution: Attribution
		}).addTo(window.myMap);

		var myGroup = L.layerGroup().addTo(window.myMap);

		$.each( markers, function(index, marker){

			if(marker.type=='marker'){
				var marker = L.marker(
					[marker.lat, marker.lng],
					{ icon: getIcon(marker.ico) }
				)
				// .bindTooltip("595.000", {
				// 	permanent: true,
				// 	direction: 'right'
				// })
				.addTo(myGroup)
			}else{
				L.circle(
					[marker.lat, marker.lng],
					{ color: 'black', fillColor: '#000', fillOpacity: 0.5, radius: 500 }
				).addTo(myGroup);
			}

		})

		var i=0;
		myGroup.eachLayer(function (layer) {
			if(markers[i].info!==undefined){
				layer.bindPopup(markers[i].info);
			}
			i++;
		});

		// --

		function getIcon(img){

            imgIco = (img!=undefined) ? img : 'img/marker/marker.png';

		    var Icon = new L.icon({
                iconUrl: imgIco,
                iconSize: [45, 60],
                iconAnchor: [22, 60],
                popupAnchor:  [0, -50]
            });

		    return Icon;
		}

	}

});
