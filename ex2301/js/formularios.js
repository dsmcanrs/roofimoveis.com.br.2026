$(function(){

	$(".validate-form").submit(function(){

		// console.log('validate-form');

		var erMail = /(^[A-Za-z0-9_.-]+@([A-Za-z0-9_.-]+\.)+[A-Za-z]{2,4}$)/;
		var loadingText = '<i class="fa fa-circle-notch fa-spin"></i>';
		var $btn = $(this).find('button[type="submit"]');
		var retorno = true;

		if(!$btn.length){
			retorno = false;
			console.log( 'Definir button type=submit' );
		}

		// Remove indicadores de erro
		$btn.removeClass('animate__animated animate__shakeX');
		$('.form-group').removeClass('has-error');
		$('span[class*=form-error]').remove();

		$(this).find(".valida").each(function(){

			var $campo = $(this);
			var type = $campo.attr('type');
			var tagName = $campo.prop("tagName").toLowerCase();
			var label = $campo.parents(".form-group").find("label:first").text().replace('*','');

			// console.log(tagName);

			// remove erro no focus
			$campo.focus(function(){
				$error_message = $(this).next();
				$error_message.remove();
			});

			if (tagName == 'input' && type=="checkbox" && !$campo.is(':checked')) {
				retorno = false;
				$campo.parent().after('<span class="form-error text-danger">Obrigatório</span>');
			}

			if (tagName == 'input' && $campo.val()=="") {
				retorno = false;
				$campo.parents(".form-group").addClass("has-error");
				$campo.after('<span class="form-error text-danger">Obrigatório<span>');
			}

			if (tagName == "select" && $campo.val() == "") {
				retorno = false;
				$campo.parents(".form-group").addClass("has-error");
				$campo.after('<span class="form-error text-danger">Selecione uma opção</span>');
			}

			if ($campo.val()!="" && $campo.hasClass("valida-email")) {
				if(!erMail.test($campo.val())) {
					retorno = false;
					$campo.parents(".form-group").addClass("has-error");
					$campo.after('<span class="form-error text-danger">'+ label +' inválido');
				}
			}

		});

		if (!retorno) {
			$btn.addClass('animate__animated animate__shakeX');
		} else {
			if ($(this).find('input[name=captcha_array]').length>0) {
				var cap = $(this).find('input[name=captcha]').val(); cap = cap.toLowerCase();
				var n = $(this).find('input[name=captcha_num]').val(); n = parseInt(n)+3;
				var v = $(this).find('input[name=captcha_array]').val();
				v = v.split(",");
				if (v[n]!=cap) {
					retorno = false;
					alert('Validação anti-spam inválida.');
				}
			}
		}

		if(retorno){
			if ($btn.html() !== loadingText) {
				$btn.data('original-text', $btn.html());
				$btn.html(loadingText);
			}
			setTimeout(function() {
				$btn.html($btn.data('original-text'));
			}, 15000);
		}

		return retorno;
		return false;

	});

	$("form .form-group input, form .form-group select, form .form-group textarea").focus(function(){
		$(this).parents(".form-group").removeClass("has-error");
	});

});

// Armazena arquivos selecionados por input
const fileStore = {};

// Input File: Pega arquivos via drap n drop
function fileInputDrop(e){
	// console.log('fileInputDrop');
	e.preventDefault();
	var $input = e.target;
	$input.files = e.dataTransfer.files;
	// console.log($input.files);
	fileInputChange(e);
}

function fileInputChange(e){
    var $input = e.target;
    var inputName = $input.name;
    var files = Array.from($input.files);

    // Inicializa o array de arquivos se necessário
    if (!fileStore[inputName]) fileStore[inputName] = [];

    // Adiciona novos arquivos ao array, evitando duplicados pelo nome
    files.forEach(file => {
        if (!fileStore[inputName].some(f => f.name === file.name && f.size === file.size)) {
            fileStore[inputName].push(file);
        }
    });

    // Atualiza o campo visualmente
    var $closest = $input.closest('.file-drop-area');
    var $textContainer = $closest.querySelector('.msg');
    if(fileStore[inputName].length === 1){
        $textContainer.innerHTML = fileStore[inputName][0].name;
    }else{
        $textContainer.innerHTML = fileStore[inputName].length + ' arquivos selecionados';
    }

    // Adiciona a lista dos arquivos fora (abaixo) da div .file-drop-area
    var $fileList = $closest.nextElementSibling;
    if (!$fileList || !$fileList.classList.contains('file-list')) {
        $fileList = document.createElement('div');
        $fileList.className = 'file-list';
        $closest.parentNode.insertBefore($fileList, $closest.nextSibling);
    }

    // Limpa a lista antes de adicionar os arquivos
    $fileList.innerHTML = '';
    fileStore[inputName].forEach((file, idx) => {
        var fileDiv = document.createElement('div');
        fileDiv.style.display = 'flex';
        fileDiv.style.alignItems = 'center';

        var fileNameSpan = document.createElement('span');
        fileNameSpan.textContent = ' 🔗 ' + file.name;

        var removeLink = document.createElement('a');
        removeLink.href = '#';
        removeLink.textContent = ' remover';
        removeLink.style.marginLeft = '8px';
        removeLink.style.color = '#d00';
        removeLink.onclick = function(ev) {
            ev.preventDefault();
            // Remove do array
            fileStore[inputName].splice(idx, 1);
            // Atualiza input e lista
            const dataTransfer = new DataTransfer();
            fileStore[inputName].forEach(f => dataTransfer.items.add(f));
            $input.files = dataTransfer.files;
            fileInputChange({ target: $input });
        };

        fileDiv.appendChild(fileNameSpan);
        fileDiv.appendChild(removeLink);
        $fileList.appendChild(fileDiv);
    });

    // Cria um novo DataTransfer para atualizar o input
    const dataTransfer = new DataTransfer();
    fileStore[inputName].forEach(file => dataTransfer.items.add(file));
    $input.files = dataTransfer.files;
}

function fileInputChange1(e){
	// console.log('fileInputChange');
	var $input = e.target;
	var filesCount = $input.files.length;
	var $closest = $input.closest('.file-drop-area');
	var $textContainer = $closest.querySelector('.msg');
	// console.log($textContainer);
	if( filesCount===1 ){
		$textContainer.innerHTML = $input.files[0].name;;
	}else{
		$textContainer.innerHTML = filesCount + ' arquivos selecionados';
	}
}
