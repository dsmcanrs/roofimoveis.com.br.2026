$(function(){

	var domain = $("base").prop("href");
	var $busca = $(".form-busca");

	$(".btn-buscar-float").click(function(){
		var $ativa = $(this).closest('.form-busca').find('.busca form');
		$($ativa).submit();
	});

	/**
	 * Busca Menu
	 */

	$(".busca-menu .btn").click(function(){
		var busca = $(this).data('busca');
		var $target = $("select[name=status]");
		// ativa o botão
		$(".busca-menu .btn").removeClass("active");
		$(this).addClass("active");
		// muda o select finalidade
		$($target).val(busca).change();
		$($target).selectpicker('destroy');
		$($target).selectpicker();

	});

	/**
	 * Filtro de valores
	 */

	var allOptionsDe = $('#valor_de_list option').clone();
	var allOptionsAte = $('#valor_ate_list option').clone();

	// console.log(allOptionsDe);

	$("select[name=status]").on('change', function() {
		const status = $(this).val();
		$('#valor_de_list').empty();
		$('#valor_ate_list').empty()
		allOptionsDe.filter('.' + status).appendTo('#valor_de_list');
    	allOptionsAte.filter('.' + status).appendTo('#valor_ate_list');
	});

	// ---

	// Associa o evento change aos campos a serem recarregados
    $("select[name=status]").change(function() {
        handleSelectChange.call(this, ['categoria', 'cidade', 'bairro']);
    });
    $("select[name=categoria]").change(function() {
        handleSelectChange.call(this, ['cidade', 'bairro']);
    });
    $("select[name=cidade]").change(function() {
        handleSelectChange.call(this, ['bairro']);
    });

    function handleSelectChange(fieldsToUpdate) {

        // Obtém o elemento de busca ativo
        var $ativa = $(this).closest('form');
        var filtro = {};

		// Desmarca todos os selecteds
		fieldsToUpdate.forEach(function(field) {
			var $select = $ativa.find(`select[name=${field}]`);
			if ($select.length) {
				$select.find("option:selected").prop("selected", false);
			}
		});

        // Coleta os valores dos campos ocultos
        $ativa.find("input[type=hidden]").each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            filtro[name] = value;
        });

        // Coleta dinamicamente os valores de todos os selects no formulário
        $ativa.find("select").each(function() {
            var $select = $(this);
            var name = $select.attr('name');
            if (name) {
                if ($select.prop('multiple')) {
                    // Para selects múltiplos, coleta todos os valores selecionados
                    filtro[name] = $select.find(":selected").map(function(i, el) {
                        return $(el).val();
                    }).get();
                } else {
                    // Para selects simples, coleta o valor selecionado
                    filtro[name] = $select.find(":selected").val();
                }
            }
        });

		fieldsToUpdate.forEach(function(field) {
			// Verifica se o campo existe no formulário antes de atualizar
			var $select = $ativa.find(`select[name=${field}]`);
			if ($select.length) {
				buscaLoadData($select, field, filtro);
			}
		});

    }

	function buscaLoadData($target, campo, filtro) {

		// console.log($target);
		// console.log(campo);
		// console.log(filtro);

		var url = $("base").prop("href");
		var filtro = JSON.stringify(filtro);

		url += "/task/busca-filtro?1";
		url += "&campo=" + campo;
		url += "&filtro=" + filtro;

		$target.prop("disabled",true);
		$target.empty();

		$.get(url, function(data) {

			data = JSON.parse(data);

			if (data.length === 0) {
				$target.append("<option value=\"\">Nenhum resultado</option>");
			} else {
				$target.append("<option value=\"\"></option>");
				$.each(data, function(key, value) {
					var id = removeSpecialChars(value);
					$target.append("<option value=" + id + ">" + value + "</option>");
				});
			}

			$target.prop("disabled", false);

			if( $($target).hasClass('selectpicker') ){
				$target.selectpicker();
			}

			if( $($target).hasClass('select-checkbox') ){
				$target.selectCheckbox();
			}

		});

	}

	$(".form-busca form").submit(function(e){

		e.preventDefault();

		var $form = $(this);
		var params = [];

		$form.find('input:not([type=checkbox])').each(function(){
			let $input = $(this);
			// console.log($input);
			if( $input.attr("name") !== undefined && $input.val()!=='' ){
				let name = $input.attr("name");
				let val = encodeURIComponent($input.val());
				params.push(name + '=' + val);
			}
		});

		$form.find('input[type=checkbox]').each(function() {
			let $input = $(this);
			if ($input.attr("name") !== undefined) {
				let name = $input.attr("name").replace("[]", "");
				if ($input.is(":checked")) {
					// Verificar se já adicionamos um parâmetro com este nome
					let existing = params.find(param => param.startsWith(name + '='));
					let value = encodeURIComponent($input.attr("value"));
					if (existing) {
						// Adicionar o valor à lista existente, separando por vírgula
						let currentValues = existing.split('=')[1];
						params = params.filter(param => param !== existing);
						params.push(name + '=' + currentValues + ',' + value);
					} else {
						// Adicionar novo parâmetro
						params.push(name + '=' + value);
					}
				}
			}
		});

		$form.find('select').each(function() {
			var $select = $(this);
			var name = $select.attr("name").replace("[]", "");
			var selectedValues = [];
			$select.find("option:selected").each(function() {
				var $option = $(this);
				var val = encodeURIComponent($option.attr("value"));
				selectedValues.push(val);
			});
			// console.log(selectedValues);
			if (selectedValues.length > 0) {
				// Verificar se já adicionamos um parâmetro com este nome
				let existing = params.find(param => param.startsWith(name + '='));
				if (existing) {
					// Atualizar o parâmetro existente
					params = params.filter(param => param !== existing);
					params.push(name + '=' + selectedValues.join(','));
				} else {
					// Adicionar novo parâmetro
					params.push(name + '=' + selectedValues.join(','));
				}
			}
		});

		var action = $form.attr("action");
		var queryString = params.join('&');
		var url = action+'?'+queryString;
		window.location = decodeURIComponent(url);
		// console.log(decodeURIComponent(url));
		return false;

	});

});
