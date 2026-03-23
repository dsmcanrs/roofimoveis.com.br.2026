$(function(){

	// google translate
	$(".translate a").click(function(){
		muda_lingua($(this));
		return false;
	});

	$('.lang-selected').text( localStorage.getItem('language') );

});

function googleTranslateElementInit(){
	new google.translate.TranslateElement({
		pageLanguage: 'pt',
		// autoDisplay: false,
		includedLanguages: 'de,es,fr,en,it',
		layout: google.translate.TranslateElement.InlineLayout.SIMPLE
	},'google_translate_element');
}

function muda_lingua(elem) {

	var lang = $(elem).data("lang");
	var label = '';
	var googleMenu = '';

	// console.log(lang);

	localStorage.setItem('language', lang.toUpperCase());

	$('.lang-selected').text( localStorage.getItem('language') );

	if (lang=='pt') {
		// googleMenu = $(".goog-te-banner-frame:eq(0)").contents().find("button[id*='restore']")
		googleMenu = $("iframe[id*='container']").contents().find("button[id*='restore']");
		// console.log('Restore:');
		// console.log(googleMenu);
	}

	else {
		switch (lang) {
			case 'de': label = "Alemão"; break;
			case 'es': label = "Espanhol"; break;
			case 'fr': label = "Francês"; break;
			case 'en': label = "Inglês"; break;
			case 'it': label = "Italiano"; break
		}
		// googleMenu = $(".goog-te-menu-frame:eq(0)").contents().find("span:contains('" + label + "')");
		$("iframe.skiptranslate").each(function( index ) {
			var iframe = $( this ).contents().find("span:contains('" + label + "')");
			if( iframe.length>0 ){
				// console.log( `skiptranslate-${index}` );
				// console.log( $( this ).contents() );
				googleMenu = iframe;
				return false;
			}
		});
	}

	if( googleMenu.length>0 ){
		// console.log('trigger');
		googleMenu.trigger('click');
	}

}